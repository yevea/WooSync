<?php
/**
 * Order Sync Service
 * Syncs orders from WooCommerce to FacturaScripts
 */
namespace FacturaScripts\Plugins\WooSync\Lib;

use FacturaScripts\Dinamic\Model\Cliente;
use FacturaScripts\Dinamic\Model\Pedido;
use FacturaScripts\Dinamic\Model\LineaPedido;
use FacturaScripts\Dinamic\Model\Producto;

class OrderSyncService extends SyncService
{
    private $customerSyncService;

    public function __construct(WooCommerceAPI $wooApi)
    {
        parent::__construct($wooApi);
        $this->customerSyncService = new CustomerSyncService($wooApi);
    }

    /**
     * Sync orders from WooCommerce
     */
    public function sync(array $options = []): array
    {
        $perPage = $options['per_page'] ?? 50;
        $page = 1;
        $hasMore = true;

        $this->log('Starting order synchronization', 'INFO', 'order-sync');

        while ($hasMore) {
            try {
                $orders = $this->wooApi->getOrders([
                    'per_page' => $perPage,
                    'page' => $page
                ]);

                if (empty($orders)) {
                    $hasMore = false;
                    break;
                }

                foreach ($orders as $wooOrder) {
                    $this->syncOrder($wooOrder);
                }

                $page++;
                
                if (count($orders) < $perPage) {
                    $hasMore = false;
                }
                
            } catch (\Exception $e) {
                $this->log('Error fetching orders page ' . $page . ': ' . $e->getMessage(), 'ERROR', 'order-sync');
                $hasMore = false;
            }
        }

        $results = $this->getResults();
        $this->log(
            sprintf('Order sync completed: %d synced, %d errors, %d skipped', 
                $results['synced'], $results['errors'], $results['skipped']),
            'INFO', 
            'order-sync'
        );

        return $results;
    }

    /**
     * Sync a single order
     */
    private function syncOrder(array $wooOrder): void
    {
        try {
            $wooOrderId = $wooOrder['id'] ?? 0;
            $orderNumber = $wooOrder['number'] ?? $wooOrderId;

            // Check if order already exists by checking observaciones field for WooCommerce ID
            $pedido = new Pedido();
            $where = [new \FacturaScripts\Core\Base\DataBase\DataBaseWhere('observaciones', '%WooCommerce ID: ' . $wooOrderId . '%', 'LIKE')];
            
            if ($pedido->loadFromCode('', $where)) {
                // Order already synced, skip
                $this->log("Skipping order #{$orderNumber} - already synced", 'DEBUG', 'order', (string)$wooOrderId);
                $this->skippedCount++;
                return;
            }

            // Get or create customer
            $customer = $this->getOrCreateCustomer($wooOrder);
            if (!$customer) {
                $this->log("Cannot sync order #{$orderNumber} - failed to get/create customer", 'ERROR', 'order', (string)$wooOrderId);
                $this->errorCount++;
                return;
            }

            // Create new order
            $pedido->codcliente = $customer->codcliente;
            $pedido->nombrecliente = $customer->nombre;
            
            // Date
            if (!empty($wooOrder['date_created'])) {
                $date = date('d-m-Y', strtotime($wooOrder['date_created']));
                $pedido->fecha = $date;
            }

            // Status mapping
            $pedido->editable = $this->isOrderEditable($wooOrder['status'] ?? 'pending');
            
            // Totals
            $pedido->neto = (float)($wooOrder['total'] ?? 0);
            $pedido->total = (float)($wooOrder['total'] ?? 0);
            
            // Store WooCommerce order ID in observations
            $pedido->observaciones = "WooCommerce Order #" . $orderNumber . "\n";
            $pedido->observaciones .= "WooCommerce ID: " . $wooOrderId . "\n";
            $pedido->observaciones .= "Status: " . ($wooOrder['status'] ?? 'unknown') . "\n";
            
            if (!empty($wooOrder['customer_note'])) {
                $pedido->observaciones .= "\nCustomer Note: " . $wooOrder['customer_note'];
            }

            // Save order header
            if (!$pedido->save()) {
                $this->log("Failed to save order #{$orderNumber}", 'ERROR', 'order', (string)$wooOrderId);
                $this->errorCount++;
                return;
            }

            // Sync order lines
            $linesSynced = $this->syncOrderLines($pedido, $wooOrder);
            
            if ($linesSynced) {
                $this->syncedCount++;
                $this->log("Successfully synced order #{$orderNumber} with {$linesSynced} lines", 'INFO', 'order', (string)$wooOrderId);
            } else {
                $this->log("Order #{$orderNumber} synced but no lines added", 'WARNING', 'order', (string)$wooOrderId);
                $this->syncedCount++;
            }

        } catch (\Exception $e) {
            $this->errorCount++;
            $this->log(
                'Error syncing order: ' . $e->getMessage(), 
                'ERROR', 
                'order', 
                (string)($wooOrder['id'] ?? '')
            );
        }
    }

    /**
     * Sync order lines
     */
    private function syncOrderLines(Pedido $pedido, array $wooOrder): int
    {
        $lineCount = 0;
        $lineItems = $wooOrder['line_items'] ?? [];

        foreach ($lineItems as $item) {
            try {
                $linea = new LineaPedido();
                $linea->idpedido = $pedido->idpedido;
                
                // Try to find product by SKU
                $sku = $item['sku'] ?? '';
                if (!empty($sku)) {
                    $producto = new Producto();
                    $where = [new \FacturaScripts\Core\Base\DataBase\DataBaseWhere('referencia', $sku)];
                    
                    if ($producto->loadFromCode('', $where)) {
                        $linea->referencia = $producto->referencia;
                        $linea->descripcion = $producto->descripcion;
                    } else {
                        // Product not found, use item data
                        $linea->descripcion = $item['name'] ?? 'Unknown Product';
                    }
                } else {
                    $linea->descripcion = $item['name'] ?? 'Unknown Product';
                }

                $linea->cantidad = (float)($item['quantity'] ?? 1);
                $linea->pvpunitario = (float)($item['price'] ?? 0);
                $linea->pvptotal = (float)($item['total'] ?? 0);
                
                // Tax
                $linea->iva = (float)($item['tax_class'] ?? 0);

                if ($linea->save()) {
                    $lineCount++;
                }

            } catch (\Exception $e) {
                $this->log('Error syncing order line: ' . $e->getMessage(), 'WARNING', 'order');
            }
        }

        return $lineCount;
    }

    /**
     * Get or create customer for order
     */
    private function getOrCreateCustomer(array $wooOrder): ?Cliente
    {
        $billing = $wooOrder['billing'] ?? [];
        $email = $billing['email'] ?? '';

        if (empty($email)) {
            return null;
        }

        // Try to find existing customer
        $cliente = new Cliente();
        $where = [new \FacturaScripts\Core\Base\DataBase\DataBaseWhere('email', $email)];
        
        if ($cliente->loadFromCode('', $where)) {
            return $cliente;
        }

        // Create customer from order billing data
        $customerData = [
            'id' => 0,
            'email' => $email,
            'first_name' => $billing['first_name'] ?? '',
            'last_name' => $billing['last_name'] ?? '',
            'billing' => $billing
        ];

        // Use customer sync service to create customer
        try {
            $this->customerSyncService->sync(['customers' => [$customerData]]);
            
            // Try to load the newly created customer
            if ($cliente->loadFromCode('', $where)) {
                return $cliente;
            }
        } catch (\Exception $e) {
            $this->log('Error creating customer for order: ' . $e->getMessage(), 'ERROR', 'order');
        }

        return null;
    }

    /**
     * Check if order should be editable based on WooCommerce status
     */
    private function isOrderEditable(string $status): bool
    {
        $nonEditableStatuses = ['completed', 'cancelled', 'refunded', 'failed'];
        return !in_array($status, $nonEditableStatuses);
    }
}
