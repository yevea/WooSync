<?php
namespace FacturaScripts\Plugins\WooSync\Lib;

use FacturaScripts\Core\Tools;
use FacturaScripts\Core\Base\DataBase\DataBaseWhere;
use FacturaScripts\Dinamic\Model\Pedido;
use FacturaScripts\Dinamic\Model\Cliente;

class OrderSyncService
{
    private $wooApi;
    
    // Constants
    private const MAX_LOG_MESSAGE_LENGTH = 2000;
    private const CUSTOMER_CODE_MIN = 1000;
    private const CUSTOMER_CODE_MAX = 9999;
    private const MAX_CODE_GENERATION_ATTEMPTS = 20;
    
    public function __construct()
    {
        $this->wooApi = new WooCommerceAPI();
    }
    
    /**
     * Synchronize orders from WooCommerce to FacturaScripts
     */
    public function sync(): void
    {
        try {
            Tools::log()->info('OrderSyncService: Starting order synchronization');
            
            // Get orders from WooCommerce
            $wooOrders = $this->wooApi->getOrders(['per_page' => 100]);
            
            if (!is_array($wooOrders)) {
                Tools::log()->warning('OrderSyncService: No orders returned from WooCommerce');
                return;
            }
            
            $syncCount = 0;
            $skipCount = 0;
            $errorCount = 0;
            
            foreach ($wooOrders as $wooOrder) {
                try {
                    $result = $this->syncOrder($wooOrder);
                    if ($result === 'synced') {
                        $syncCount++;
                    } elseif ($result === 'skipped') {
                        $skipCount++;
                    }
                } catch (\Exception $e) {
                    $errorCount++;
                    $this->log('Error syncing order: ' . $e->getMessage(), 'ERROR', 'order', (string)($wooOrder['id'] ?? ''));
                }
            }
            
            Tools::log()->info("OrderSyncService: Completed - Synced: {$syncCount}, Skipped: {$skipCount}, Errors: {$errorCount}");
            
        } catch (\Exception $e) {
            Tools::log()->error('OrderSyncService: Sync failed: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Sync a single order from WooCommerce
     */
    private function syncOrder(array $wooOrder): string
    {
        try {
            $wooOrderId = $wooOrder['id'] ?? 0;
            $orderNumber = $wooOrder['number'] ?? $wooOrderId;

            // Check if order already exists by checking observaciones field for WooCommerce ID
            $pedido = new Pedido();
            $where = [new DataBaseWhere('observaciones', '%WooCommerce ID: ' . $wooOrderId . '%', 'LIKE')];
            
            if ($pedido->loadFromCode('', $where)) {
                // Order already synced, skip
                $this->log("Skipping order #{$orderNumber} - already synced", 'DEBUG', 'order', (string)$wooOrderId);
                return 'skipped';
            }
            
            // Get or create customer
            $customer = $this->getOrCreateCustomer($wooOrder['billing'] ?? []);
            if (!$customer) {
                $this->log("Failed to get/create customer for order #{$orderNumber}", 'ERROR', 'order', (string)$wooOrderId);
                return 'error';
            }
            
            // Create new order
            $pedido = new Pedido();
            $pedido->codcliente = $customer->codcliente;
            $pedido->nombre = $customer->nombre;
            $pedido->cifnif = $customer->cifnif;
            
            // Set billing address
            $billing = $wooOrder['billing'] ?? [];
            $pedido->direccion = $billing['address_1'] ?? '';
            $pedido->codpostal = $billing['postcode'] ?? '';
            $pedido->ciudad = $billing['city'] ?? '';
            $pedido->provincia = $billing['state'] ?? '';
            $pedido->apartado = $billing['address_2'] ?? '';
            
            // Add WooCommerce ID to observations
            $pedido->observaciones = "WooCommerce ID: {$wooOrderId}\n";
            $pedido->observaciones .= "WooCommerce Order #: {$orderNumber}\n";
            $pedido->observaciones .= "Status: " . ($wooOrder['status'] ?? 'unknown') . "\n";
            
            // Save the order
            if ($pedido->save()) {
                $this->log("Order #{$orderNumber} synced successfully", 'INFO', 'order', (string)$wooOrderId);
                return 'synced';
            } else {
                $this->log("Failed to save order #{$orderNumber}", 'ERROR', 'order', (string)$wooOrderId);
                return 'error';
            }
            
        } catch (\Exception $e) {
            $this->log("Exception syncing order: " . $e->getMessage(), 'ERROR', 'order', (string)($wooOrder['id'] ?? ''));
            throw $e;
        }
    }
    
    /**
     * Get existing customer or create a new one
     */
    private function getOrCreateCustomer(array $billing): ?Cliente
    {
        $email = $billing['email'] ?? '';
        
        if (empty($email)) {
            Tools::log()->warning('OrderSyncService: Cannot create customer without email');
            return null;
        }
        
        // Try to find existing customer by email
        $cliente = new Cliente();
        $where = [new DataBaseWhere('email', $email)];
        
        if ($cliente->loadFromCode('', $where)) {
            return $cliente;
        }
        
        // Create new customer
        $cliente = new Cliente();
        $cliente->email = $email;
        
        // Build customer name with fallback
        $firstName = trim($billing['first_name'] ?? '');
        $lastName = trim($billing['last_name'] ?? '');
        $fullName = trim($firstName . ' ' . $lastName);
        $cliente->nombre = !empty($fullName) ? $fullName : 'WooCommerce Customer';
        
        $cliente->telefono1 = $billing['phone'] ?? '';
        
        // Generate unique customer code with retry logic
        $baseCode = strtoupper(substr(str_replace([' ', '.', '@'], '', $email), 0, 6));
        $attempt = 0;
        
        do {
            $code = $baseCode . random_int(self::CUSTOMER_CODE_MIN, self::CUSTOMER_CODE_MAX);
            $testCliente = new Cliente();
            $codeExists = $testCliente->loadFromCode($code);
            $attempt++;
        } while ($codeExists && $attempt < self::MAX_CODE_GENERATION_ATTEMPTS);
        
        if ($codeExists) {
            Tools::log()->error("OrderSyncService: Failed to generate unique customer code after " . self::MAX_CODE_GENERATION_ATTEMPTS . " attempts");
            return null;
        }
        
        $cliente->codcliente = $code;
        
        if ($cliente->save()) {
            Tools::log()->info("OrderSyncService: Created new customer: {$code}");
            return $cliente;
        }
        
        Tools::log()->error("OrderSyncService: Failed to create customer for email: {$email}");
        return null;
    }
    
    /**
     * Log a message to the WooSync log
     */
    private function log(string $message, string $level = 'INFO', string $type = '', string $reference = ''): void
    {
        try {
            $log = new \FacturaScripts\Plugins\WooSync\Model\WooSyncLog();
            $log->message = substr($message, 0, self::MAX_LOG_MESSAGE_LENGTH);
            $log->level = $level;
            $log->type = $type;
            $log->reference = $reference;
            $log->save();
        } catch (\Exception $e) {
            Tools::log()->error('OrderSyncService: Failed to write log: ' . $e->getMessage());
        }
    }
}
