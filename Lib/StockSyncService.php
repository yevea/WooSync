<?php
/**
 * Stock Sync Service
 * Syncs stock levels from WooCommerce to FacturaScripts
 */
namespace FacturaScripts\Plugins\WooSync\Lib;

use FacturaScripts\Dinamic\Model\Producto;
use FacturaScripts\Dinamic\Model\Stock;

class StockSyncService extends SyncService
{
    /**
     * Sync stock from WooCommerce
     */
    public function sync(array $options = []): array
    {
        $perPage = $options['per_page'] ?? 50;
        $page = 1;
        $hasMore = true;

        $this->log('Starting stock synchronization', 'INFO', 'stock-sync');

        while ($hasMore) {
            try {
                $products = $this->wooApi->getProducts([
                    'per_page' => $perPage,
                    'page' => $page,
                    'stock_status' => 'instock,outofstock,onbackorder'
                ]);

                if (empty($products)) {
                    $hasMore = false;
                    break;
                }

                foreach ($products as $wooProduct) {
                    $this->syncProductStock($wooProduct);
                }

                $page++;
                
                if (count($products) < $perPage) {
                    $hasMore = false;
                }
                
            } catch (\Exception $e) {
                $this->log('Error fetching products for stock sync page ' . $page . ': ' . $e->getMessage(), 'ERROR', 'stock-sync');
                $hasMore = false;
            }
        }

        $results = $this->getResults();
        $this->log(
            sprintf('Stock sync completed: %d synced, %d errors, %d skipped', 
                $results['synced'], $results['errors'], $results['skipped']),
            'INFO', 
            'stock-sync'
        );

        return $results;
    }

    /**
     * Sync stock for a single product
     */
    private function syncProductStock(array $wooProduct): void
    {
        try {
            $sku = $wooProduct['sku'] ?? '';
            $wooId = $wooProduct['id'] ?? 0;

            // Skip products without SKU
            if (empty($sku)) {
                $this->log("Skipping product ID {$wooId} - no SKU", 'DEBUG', 'stock', (string)$wooId);
                $this->skippedCount++;
                return;
            }

            // Find product by SKU
            $producto = new Producto();
            $where = [new \FacturaScripts\Core\Base\DataBase\DataBaseWhere('referencia', $sku)];
            
            if (!$producto->loadFromCode('', $where)) {
                $this->log("Product not found for SKU: {$sku}", 'WARNING', 'stock', (string)$wooId);
                $this->skippedCount++;
                return;
            }

            // Update stock quantity
            $manage_stock = $wooProduct['manage_stock'] ?? false;
            
            if ($manage_stock && isset($wooProduct['stock_quantity'])) {
                $stockQty = (float)$wooProduct['stock_quantity'];
                $producto->stockfis = $stockQty;
                
                // Update stock status
                $stockStatus = $wooProduct['stock_status'] ?? 'instock';
                $producto->bloqueado = ($stockStatus !== 'instock');
                
                if ($producto->save()) {
                    $this->syncedCount++;
                    $this->log("Updated stock for {$sku}: {$stockQty}", 'INFO', 'stock', (string)$wooId);
                } else {
                    $this->errorCount++;
                    $this->log("Failed to update stock for {$sku}", 'ERROR', 'stock', (string)$wooId);
                }
            } else {
                // Stock not managed in WooCommerce
                $this->log("Stock not managed for SKU: {$sku}", 'DEBUG', 'stock', (string)$wooId);
                $this->skippedCount++;
            }

        } catch (\Exception $e) {
            $this->errorCount++;
            $this->log(
                'Error syncing stock: ' . $e->getMessage(), 
                'ERROR', 
                'stock', 
                (string)($wooProduct['id'] ?? '')
            );
        }
    }
}
