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
                    'page' => $page
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

            // Find existing customer by email or create new
            $cliente = new Cliente();
            $where = [new \FacturaScripts\Core\Base\DataBase\DataBaseWhere('email', $email)];
            
            if (!$cliente->loadFromCode('', $where)) {
                // New customer - generate code
                $cliente->codcliente = $this->generateCustomerCode($email);
                $this->log("Creating new customer: {$email}", 'INFO', 'customer', (string)$wooId);
            } else {
                $this->log("Updating existing customer: {$email}", 'INFO', 'customer', (string)$wooId);
            }

            // Map WooCommerce fields to FacturaScripts
            $firstName = $wooCustomer['first_name'] ?? '';
            $lastName = $wooCustomer['last_name'] ?? '';
            $cliente->nombre = trim($firstName . ' ' . $lastName);
            
            if (empty($cliente->nombre)) {
                $cliente->nombre = $email;
            }
            
            $cliente->email = $email;
            
            // Billing address
            $billing = $wooCustomer['billing'] ?? [];
            if (!empty($billing)) {
                $cliente->direccion = $billing['address_1'] ?? '';
                if (!empty($billing['address_2'])) {
                    $cliente->direccion .= ' ' . $billing['address_2'];
                }
                $cliente->ciudad = $billing['city'] ?? '';
                $cliente->provincia = $billing['state'] ?? '';
                $cliente->codpostal = $billing['postcode'] ?? '';
                $cliente->telefono1 = $billing['phone'] ?? '';
                
                // Country
                if (!empty($billing['country'])) {
                    $cliente->codpais = $billing['country'];
                }
                
                // Company
                if (!empty($billing['company'])) {
                    $cliente->razonsocial = $billing['company'];
                }
            }

            // Save the customer
            if ($cliente->save()) {
                $this->syncedCount++;
                $this->log("Successfully synced customer: {$email}", 'INFO', 'customer', (string)$wooId);
                
                // Create/update contact
                $this->syncContact($cliente, $wooCustomer);
            } else {
                $this->errorCount++;
                $this->log("Failed to save customer: {$email}", 'ERROR', 'customer', (string)$wooId);
            }

        } catch (\Exception $e) {
            $this->errorCount++;
            $this->log(
                'Error syncing customer: ' . $e->getMessage(), 
                'ERROR', 
                'customer', 
                (string)($wooCustomer['id'] ?? '')
            );
        }
    }

    /**
     * Sync or create contact for customer
     */
    private function syncContact(Cliente $cliente, array $wooCustomer): void
    {
        try {
            $contacto = new Contacto();
            $where = [
                new \FacturaScripts\Core\Base\DataBase\DataBaseWhere('email', $cliente->email),
                new \FacturaScripts\Core\Base\DataBase\DataBaseWhere('codcliente', $cliente->codcliente)
            ];
            
            if (!$contacto->loadFromCode('', $where)) {
                // Create new contact
                $contacto->codcliente = $cliente->codcliente;
                $contacto->email = $cliente->email;
            }
            
            $contacto->nombre = $wooCustomer['first_name'] ?? '';
            $contacto->apellidos = $wooCustomer['last_name'] ?? '';
            $contacto->telefono1 = $wooCustomer['billing']['phone'] ?? '';
            
            $contacto->save();
            
        } catch (\Exception $e) {
            $this->log('Error syncing contact: ' . $e->getMessage(), 'WARNING', 'customer');
        }
    }

    /**
     * Generate customer code from email
     */
    private function generateCustomerCode(string $email): string
    {
        // Use part of email as code
        $parts = explode('@', $email);
        $code = strtoupper(substr($parts[0], 0, 6));
        
        // Add random suffix to ensure uniqueness
        $code .= mt_rand(100, 999);
        
        return $code;
    }
}
