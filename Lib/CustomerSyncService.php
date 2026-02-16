<?php
/**
 * Customer Sync Service
 * Syncs customers from WooCommerce to FacturaScripts
 */
namespace FacturaScripts\Plugins\WooSync\Lib;

use FacturaScripts\Dinamic\Model\Cliente;
use FacturaScripts\Dinamic\Model\Contacto;

class CustomerSyncService extends SyncService
{
    /**
     * Sync customers from WooCommerce
     */
    public function sync(array $options = []): array
    {
        $perPage = $options['per_page'] ?? 50;
        $page = 1;
        $hasMore = true;

        $this->log('Starting customer synchronization', 'INFO', 'customer-sync');

        while ($hasMore) {
            try {
                $customers = $this->wooApi->getCustomers([
                    'per_page' => $perPage,
                    'page' => $page,
                    'role' => 'customer'
                ]);

                if (empty($customers)) {
                    $hasMore = false;
                    break;
                }

                foreach ($customers as $wooCustomer) {
                    $this->syncCustomer($wooCustomer);
                }

                $page++;
                
                if (count($customers) < $perPage) {
                    $hasMore = false;
                }
                
            } catch (\Exception $e) {
                $this->log('Error fetching customers page ' . $page . ': ' . $e->getMessage(), 'ERROR', 'customer-sync');
                $hasMore = false;
            }
        }

        $results = $this->getResults();
        $this->log(
            sprintf('Customer sync completed: %d synced, %d errors, %d skipped', 
                $results['synced'], $results['errors'], $results['skipped']),
            'INFO', 
            'customer-sync'
        );

        return $results;
    }

    /**
     * Sync a single customer
     */
    private function syncCustomer(array $wooCustomer): void
    {
        try {
            $email = $wooCustomer['email'] ?? '';
            $wooId = $wooCustomer['id'] ?? 0;

            // Skip customers without email
            if (empty($email)) {
                $this->log("Skipping customer ID {$wooId} - no email", 'WARNING', 'customer', (string)$wooId);
                $this->skippedCount++;
                return;
            }

            // Skip non-customer roles (administrators, shop_manager, etc.)
            $role = $wooCustomer['role'] ?? '';
            if (!empty($role) && $role !== 'customer' && $role !== 'subscriber') {
                $this->log("Skipping user ID {$wooId} - role '{$role}' is not a customer", 'WARNING', 'customer', (string)$wooId);
                $this->skippedCount++;
                return;
            }

            // Find existing customer by email or create new
            $cliente = new Cliente();
            $where = [new \FacturaScripts\Core\Base\DataBase\DataBaseWhere('email', $email)];
            
            $isNew = !$cliente->loadFromCode('', $where);
            if ($isNew) {
                // Let FacturaScripts auto-generate codcliente via newCode() in saveInsert()
                $this->log("Creating new customer: {$email}", 'INFO', 'customer', (string)$wooId);
            } else {
                $this->log("Updating existing customer: {$email}", 'INFO', 'customer', (string)$wooId);
            }

            // Map WooCommerce fields to FacturaScripts Cliente model
            // Only set properties that exist on the clientes table
            $firstName = $wooCustomer['first_name'] ?? '';
            $lastName = $wooCustomer['last_name'] ?? '';
            $cliente->nombre = trim($firstName . ' ' . $lastName);
            
            if (empty($cliente->nombre)) {
                $cliente->nombre = $email;
            }
            
            $cliente->nombre = substr($cliente->nombre, 0, 100);
            $cliente->email = $email;
            
            // Billing data - only set fields that exist on Cliente
            $billing = $wooCustomer['billing'] ?? [];
            $cliente->telefono1 = substr($billing['phone'] ?? '', 0, 30);
            
            // razonsocial (FS sets to nombre if empty, but we can set from company)
            if (!empty($billing['company'])) {
                $cliente->razonsocial = substr($billing['company'], 0, 100);
            } else if (empty($cliente->razonsocial)) {
                $cliente->razonsocial = $cliente->nombre;
            }

            // cifnif is NOT NULL in FS schema; ensure it has a value for new customers
            if ($cliente->cifnif === null) {
                $cliente->cifnif = '';
            }

            // Save the customer
            if ($cliente->save()) {
                $this->syncedCount++;
                $this->log("Successfully synced customer: {$email}", 'INFO', 'customer', (string)$wooId);
                
                // Update the auto-created contact with address data
                $this->syncContact($cliente, $wooCustomer);
            } else {
                $this->errorCount++;
                $this->log("Failed to save customer {$email} (code: {$cliente->codcliente})", 'ERROR', 'customer', (string)$wooId);
            }

        } catch (\Exception $e) {
            $this->errorCount++;
            $this->log(
                'Error syncing customer: ' . $e->getMessage() . ' | ' . $e->getFile() . ':' . $e->getLine(), 
                'ERROR', 
                'customer', 
                (string)($wooCustomer['id'] ?? '')
            );
        }
    }

    /**
     * Update contact for customer with address data from WooCommerce.
     * FacturaScripts auto-creates a Contacto when saving a new Cliente,
     * so we find and update that contact with address fields.
     */
    private function syncContact(Cliente $cliente, array $wooCustomer): void
    {
        try {
            $contacto = new Contacto();
            $where = [
                new \FacturaScripts\Core\Base\DataBase\DataBaseWhere('codcliente', $cliente->codcliente),
                new \FacturaScripts\Core\Base\DataBase\DataBaseWhere('email', $cliente->email)
            ];
            
            if (!$contacto->loadFromCode('', $where)) {
                // No auto-created contact found; create one
                $contacto->codcliente = $cliente->codcliente;
                $contacto->email = $cliente->email;
            }
            
            // Name fields
            $contacto->nombre = $wooCustomer['first_name'] ?? '';
            $contacto->apellidos = $wooCustomer['last_name'] ?? '';
            
            if (empty($contacto->nombre) && empty($contacto->apellidos)) {
                $contacto->nombre = $cliente->nombre;
            }
            
            // Address fields (these belong on Contacto, not Cliente)
            $billing = $wooCustomer['billing'] ?? [];
            if (!empty($billing)) {
                $contacto->direccion = substr(trim(($billing['address_1'] ?? '') . ' ' . ($billing['address_2'] ?? '')), 0, 100);
                $contacto->ciudad = substr($billing['city'] ?? '', 0, 100);
                $contacto->provincia = substr($billing['state'] ?? '', 0, 100);
                $contacto->codpostal = substr($billing['postcode'] ?? '', 0, 10);
                $contacto->telefono1 = substr($billing['phone'] ?? '', 0, 30);
                
                if (!empty($billing['company'])) {
                    $contacto->empresa = substr($billing['company'], 0, 100);
                }
            }
            
            $contacto->email = $cliente->email;
            
            if (!$contacto->save()) {
                $this->log("Failed to save contact for customer {$cliente->email}", 'WARNING', 'customer');
            }
            
        } catch (\Exception $e) {
            $this->log('Error syncing contact: ' . $e->getMessage(), 'WARNING', 'customer');
        }
    }
}
