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
            
            // Ensure nombre is not too long (FS usually limits to 100 chars)
            $cliente->nombre = substr($cliente->nombre, 0, 100);
            
            $cliente->email = $email;
            
            // Billing address
            $billing = $wooCustomer['billing'] ?? [];
            if (!empty($billing)) {
                $cliente->direccion = substr(trim(($billing['address_1'] ?? '') . ' ' . ($billing['address_2'] ?? '')), 0, 100);
                $cliente->ciudad = substr($billing['city'] ?? '', 0, 100);
                $cliente->provincia = substr($billing['state'] ?? '', 0, 100);
                $cliente->codpostal = substr($billing['postcode'] ?? '', 0, 10);
                $cliente->telefono1 = substr($billing['phone'] ?? '', 0, 30);
                
                // Country - validate it exists in FacturaScripts
                if (!empty($billing['country'])) {
                    $countryCode = strtoupper($billing['country']);
                    if ($this->validateCountryCode($countryCode)) {
                        $cliente->codpais = $countryCode;
                    } else {
                        // Try common country code if validation fails
                        $cliente->codpais = $this->getDefaultCountryCode();
                        $this->log("Invalid country code '{$countryCode}' for customer {$email}, using default", 'WARNING', 'customer', (string)$wooId);
                    }
                } else if ($isNew) {
                    // Set default country for new customers without country
                    $cliente->codpais = $this->getDefaultCountryCode();
                }
                
                // Company
                if (!empty($billing['company'])) {
                    $cliente->razonsocial = substr($billing['company'], 0, 100);
                }
            } else if ($isNew) {
                // New customer with no billing info - set default country
                $cliente->codpais = $this->getDefaultCountryCode();
            }

            // Log customer state before save attempt
            $this->log("Attempting to save customer {$email}. Code: {$cliente->codcliente}, Country: {$cliente->codpais}, Name: {$cliente->nombre}", 'DEBUG', 'customer', (string)$wooId);
            
            // Save the customer with detailed error logging
            if ($cliente->save()) {
                $this->syncedCount++;
                $this->log("Successfully synced customer: {$email}", 'INFO', 'customer', (string)$wooId);
                
                // Create/update contact
                $this->syncContact($cliente, $wooCustomer);
            } else {
                $this->errorCount++;
                // Get validation errors if available
                $errors = method_exists($cliente, 'getErrors') ? implode(', ', $cliente->getErrors()) : 'Unknown error';
                
                // Enhanced error logging with all customer details
                $errorDetails = "Failed to save customer {$email}:\n";
                $errorDetails .= "- Errors: {$errors}\n";
                $errorDetails .= "- Code: {$cliente->codcliente}\n";
                $errorDetails .= "- Name: {$cliente->nombre}\n";
                $errorDetails .= "- Email: {$cliente->email}\n";
                $errorDetails .= "- Country: {$cliente->codpais}\n";
                $errorDetails .= "- Address: {$cliente->direccion}\n";
                $errorDetails .= "- City: {$cliente->ciudad}\n";
                
                $this->log($errorDetails, 'ERROR', 'customer', (string)$wooId);
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
     * Generate unique customer code from email
     */
    private function generateCustomerCode(string $email): string
    {
        // Use part of email as base code
        $parts = explode('@', $email);
        $baseCode = strtoupper(substr($parts[0], 0, 6));
        
        // Try up to 100 times to find a unique code
        for ($i = 0; $i < 100; $i++) {
            $suffix = mt_rand(100, 999);
            $code = $baseCode . $suffix;
            
            // Check if this code already exists
            $cliente = new Cliente();
            if (!$cliente->loadFromCode($code)) {
                // Code is unique, return it
                return $code;
            }
        }
        
        // Fallback: use timestamp-based code if all random attempts fail
        return $baseCode . substr(time(), -6);
    }

    /**
     * Validate if a country code exists in FacturaScripts
     */
    private function validateCountryCode(string $code): bool
    {
        if (empty($code)) {
            return false;
        }
        
        try {
            $sql = "SELECT codpais FROM paises WHERE codpais = " . $this->dataBase->var2str($code) . " LIMIT 1";
            $result = $this->dataBase->select($sql);
            return !empty($result);
        } catch (\Exception $e) {
            $this->log("Error validating country code '{$code}': " . $e->getMessage(), 'WARNING', 'customer');
            return false;
        }
    }

    /**
     * Get default country code (Spain/ES as fallback, or first available country)
     */
    private function getDefaultCountryCode(): string
    {
        try {
            // Try Spain first (common default for Spanish FacturaScripts installations)
            if ($this->validateCountryCode('ESP')) {
                $this->log("Using default country: ESP", 'DEBUG', 'customer');
                return 'ESP';
            }
            if ($this->validateCountryCode('ES')) {
                $this->log("Using default country: ES", 'DEBUG', 'customer');
                return 'ES';
            }
            
            // Get any available country from the database
            $sql = "SELECT codpais FROM paises LIMIT 1";
            $result = $this->dataBase->select($sql);
            if (!empty($result) && isset($result[0]['codpais'])) {
                $defaultCode = $result[0]['codpais'];
                $this->log("Using first available country: {$defaultCode}", 'DEBUG', 'customer');
                return $defaultCode;
            }
            
            // Log if no countries found
            $this->log("WARNING: No countries found in paises table!", 'ERROR', 'customer');
        } catch (\Exception $e) {
            $this->log("Error getting default country: " . $e->getMessage(), 'ERROR', 'customer');
        }
        
        // Ultimate fallback
        return 'ESP';
    }
}
