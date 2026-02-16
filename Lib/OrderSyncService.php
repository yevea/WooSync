<?php
/**
 * Order Sync Service
 * Syncs orders from WooCommerce to FacturaScripts
 */
namespace FacturaScripts\Plugins\WooSync\Lib;

use FacturaScripts\Dinamic\Model\Cliente;
use FacturaScripts\Dinamic\Model\PedidoCliente;
use FacturaScripts\Dinamic\Model\LineaPedidoCliente;
use FacturaScripts\Dinamic\Model\Producto;

class OrderSyncService extends SyncService
{
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
            $pedido = new PedidoCliente();
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
                $date = date('Y-m-d', strtotime($wooOrder['date_created']));
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
    private function syncOrderLines(PedidoCliente $pedido, array $wooOrder): int
    {
        $lineCount = 0;
        $lineItems = $wooOrder['line_items'] ?? [];

        foreach ($lineItems as $item) {
            try {
                $linea = new LineaPedidoCliente();
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

        // Try to find existing customer by email
        $cliente = new Cliente();
        $where = [new \FacturaScripts\Core\Base\DataBase\DataBaseWhere('email', $email)];
        
        if ($cliente->loadFromCode('', $where)) {
            return $cliente;
        }

        // Customer not found - create from order billing data
        try {
            $firstName = $billing['first_name'] ?? '';
            $lastName = $billing['last_name'] ?? '';
            $nombre = trim($firstName . ' ' . $lastName);
            if (empty($nombre)) {
                $nombre = $email;
            }

            // Only set properties that exist on the clientes table
            // Let FacturaScripts auto-generate codcliente via newCode() in saveInsert()
            $cliente->nombre = substr($nombre, 0, 100);
            $cliente->email = $email;
            $cliente->telefono1 = substr($billing['phone'] ?? '', 0, 30);

            if (!empty($billing['company'])) {
                $cliente->razonsocial = substr($billing['company'], 0, 100);
            } else {
                $cliente->razonsocial = $cliente->nombre;
            }

            // cifnif is NOT NULL in FS schema; ensure it has a value for new customers
            if ($cliente->cifnif === null) {
                $cliente->cifnif = '';
            }

            if ($cliente->save()) {
                $this->log("Created customer from order billing: {$email}", 'INFO', 'order');

                // Update auto-created contact with address data
                $this->updateContactAddress($cliente, $billing);

                return $cliente;
            } else {
                $this->log("Failed to create customer from order billing: {$email}", 'ERROR', 'order');
            }
        } catch (\Exception $e) {
            $this->log('Error creating customer for order: ' . $e->getMessage(), 'ERROR', 'order');
        }

        return null;
    }

    /**
     * Update the auto-created contact with address data from billing info
     */
    private function updateContactAddress(Cliente $cliente, array $billing): void
    {
        try {
            $contacto = new \FacturaScripts\Dinamic\Model\Contacto();
            $where = [new \FacturaScripts\Core\Base\DataBase\DataBaseWhere('codcliente', $cliente->codcliente)];

            if ($contacto->loadFromCode('', $where)) {
                $contacto->direccion = substr(trim(($billing['address_1'] ?? '') . ' ' . ($billing['address_2'] ?? '')), 0, 100);
                $contacto->ciudad = substr($billing['city'] ?? '', 0, 100);
                $contacto->provincia = substr($billing['state'] ?? '', 0, 100);
                $contacto->codpostal = substr($billing['postcode'] ?? '', 0, 10);

                if (!empty($billing['company'])) {
                    $contacto->empresa = substr($billing['company'], 0, 100);
                }

                $contacto->save();
            }
        } catch (\Exception $e) {
            $this->log('Error updating contact address: ' . $e->getMessage(), 'WARNING', 'order');
        }
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
