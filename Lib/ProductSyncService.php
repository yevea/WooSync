<?php
/**
 * Product Sync Service
 * Syncs products from WooCommerce to FacturaScripts
 */
namespace FacturaScripts\Plugins\WooSync\Lib;

use FacturaScripts\Dinamic\Model\Producto;
use FacturaScripts\Dinamic\Model\Impuesto;
use FacturaScripts\Dinamic\Model\Familia;

class ProductSyncService extends SyncService
{
    /**
     * Sync products from WooCommerce
     */
    public function sync(array $options = []): array
    {
        $perPage = $options['per_page'] ?? 50;
        $page = 1;
        $hasMore = true;

        $this->log('Starting product synchronization', 'INFO', 'product-sync');

        while ($hasMore) {
            try {
                $products = $this->wooApi->getProducts([
                    'per_page' => $perPage,
                    'page' => $page
                ]);

                if (empty($products)) {
                    $hasMore = false;
                    break;
                }

                foreach ($products as $wooProduct) {
                    $this->syncProduct($wooProduct);
                }

                $page++;
                
                // If we got less than perPage, we're on the last page
                if (count($products) < $perPage) {
                    $hasMore = false;
                }
                
            } catch (\Exception $e) {
                $this->log('Error fetching products page ' . $page . ': ' . $e->getMessage(), 'ERROR', 'product-sync');
                $hasMore = false;
            }
        }

        $results = $this->getResults();
        $this->log(
            sprintf('Product sync completed: %d synced, %d errors, %d skipped', 
                $results['synced'], $results['errors'], $results['skipped']),
            'INFO', 
            'product-sync'
        );

        return $results;
    }

    /**
     * Sync a single product
     */
    private function syncProduct(array $wooProduct): void
    {
        try {
            $sku = $wooProduct['sku'] ?? '';
            $wooId = $wooProduct['id'] ?? 0;

            // Skip products without SKU
            if (empty($sku)) {
                $this->log("Skipping product ID {$wooId} - no SKU", 'WARNING', 'product', (string)$wooId);
                $this->skippedCount++;
                return;
            }

            // Find existing product by SKU or create new
            $product = new Producto();
            $where = [new \FacturaScripts\Core\Base\DataBase\DataBaseWhere('referencia', $sku)];
            
            if (!$product->loadFromCode('', $where)) {
                // New product
                $product->referencia = $sku;
                $this->log("Creating new product: {$sku}", 'INFO', 'product', (string)$wooId);
            } else {
                $this->log("Updating existing product: {$sku}", 'INFO', 'product', (string)$wooId);
            }

            // Map WooCommerce fields to FacturaScripts
            $product->descripcion = $wooProduct['name'] ?? '';
            
            // Price handling
            $price = $wooProduct['regular_price'] ?? $wooProduct['price'] ?? '0';
            $product->precio = (float)$price;
            
            // Stock
            if (isset($wooProduct['stock_quantity'])) {
                $product->stockfis = (float)$wooProduct['stock_quantity'];
            }
            
            // Stock status
            $product->bloqueado = ($wooProduct['stock_status'] ?? 'instock') !== 'instock';
            
            // Type (simple, variable, etc.)
            $product->nostock = ($wooProduct['type'] ?? 'simple') === 'virtual';
            
            // Description - use short or full description
            if (!empty($wooProduct['short_description'])) {
                $product->observaciones = strip_tags($wooProduct['short_description']);
            }

            // Tax handling - try to map tax class
            if (!empty($wooProduct['tax_class'])) {
                $taxCode = $this->mapTaxClass($wooProduct['tax_class']);
                if ($taxCode) {
                    $product->codimpuesto = $taxCode;
                }
            }

            // Save the product
            if ($product->save()) {
                $this->syncedCount++;
                $this->log("Successfully synced product: {$sku}", 'INFO', 'product', (string)$wooId);
            } else {
                $this->errorCount++;
                $this->log("Failed to save product: {$sku}", 'ERROR', 'product', (string)$wooId);
            }

        } catch (\Exception $e) {
            $this->errorCount++;
            $this->log(
                'Error syncing product: ' . $e->getMessage(), 
                'ERROR', 
                'product', 
                (string)($wooProduct['id'] ?? '')
            );
        }
    }

    /**
     * Map WooCommerce tax class to FacturaScripts tax code
     */
    private function mapTaxClass(string $taxClass): ?string
    {
        // Map common tax classes
        $taxMap = [
            'standard' => 'IVA21',
            'reduced-rate' => 'IVA10',
            'zero-rate' => 'IVA0',
        ];

        return $taxMap[$taxClass] ?? null;
    }
}
