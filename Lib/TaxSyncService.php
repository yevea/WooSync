<?php
/**
 * Tax Sync Service
 * Syncs tax rates from WooCommerce to FacturaScripts
 */
namespace FacturaScripts\Plugins\WooSync\Lib;

use FacturaScripts\Dinamic\Model\Impuesto;

class TaxSyncService extends SyncService
{
    /**
     * Sync taxes from WooCommerce
     */
    public function sync(array $options = []): array
    {
        $this->log('Starting tax synchronization', 'INFO', 'tax-sync');

        try {
            $taxes = $this->wooApi->getTaxRates(['per_page' => 100]);

            if (empty($taxes)) {
                $this->log('No tax rates found in WooCommerce', 'INFO', 'tax-sync');
                return $this->getResults();
            }

            foreach ($taxes as $wooTax) {
                $this->syncTaxRate($wooTax);
            }

        } catch (\Exception $e) {
            $this->log('Error fetching tax rates: ' . $e->getMessage(), 'ERROR', 'tax-sync');
        }

        $results = $this->getResults();
        $this->log(
            sprintf('Tax sync completed: %d synced, %d errors, %d skipped', 
                $results['synced'], $results['errors'], $results['skipped']),
            'INFO', 
            'tax-sync'
        );

        return $results;
    }

    /**
     * Sync a single tax rate
     */
    private function syncTaxRate(array $wooTax): void
    {
        try {
            $taxId = $wooTax['id'] ?? 0;
            $taxRate = $wooTax['rate'] ?? '0';
            $taxClass = $wooTax['class'] ?? 'standard';
            $taxName = $wooTax['name'] ?? '';

            // Generate tax code
            $taxCode = $this->generateTaxCode($taxClass, $taxRate);

            // Check if tax already exists
            $impuesto = new Impuesto();
            
            if ($impuesto->loadFromCode($taxCode)) {
                // Update existing
                $this->log("Updating existing tax: {$taxCode}", 'DEBUG', 'tax', (string)$taxId);
            } else {
                // Create new
                $impuesto->codimpuesto = $taxCode;
                $this->log("Creating new tax: {$taxCode}", 'INFO', 'tax', (string)$taxId);
            }

            // Set tax details
            $impuesto->descripcion = !empty($taxName) ? $taxName : "WooCommerce {$taxClass} tax";
            $impuesto->iva = (float)$taxRate;
            $impuesto->recargo = 0; // WooCommerce doesn't have equivalence surcharge

            if ($impuesto->save()) {
                $this->syncedCount++;
                $this->log("Successfully synced tax: {$taxCode} ({$taxRate}%)", 'INFO', 'tax', (string)$taxId);
            } else {
                $this->errorCount++;
                $this->log("Failed to save tax: {$taxCode}", 'ERROR', 'tax', (string)$taxId);
            }

        } catch (\Exception $e) {
            $this->errorCount++;
            $this->log(
                'Error syncing tax rate: ' . $e->getMessage(), 
                'ERROR', 
                'tax', 
                (string)($wooTax['id'] ?? '')
            );
        }
    }

    /**
     * Generate tax code based on class and rate
     */
    private function generateTaxCode(string $taxClass, string $rate): string
    {
        // Map common classes to Spanish tax codes
        $classMap = [
            'standard' => 'IVA',
            'reduced-rate' => 'IVARED',
            'zero-rate' => 'IVA0'
        ];

        $prefix = $classMap[$taxClass] ?? 'IVA';
        $rateInt = (int)round((float)$rate);

        return $prefix . $rateInt;
    }
}
