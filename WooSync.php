<?php
/**
 * WooSync - WooCommerce Synchronization Plugin for FacturaScripts
 */

namespace FacturaScripts\Plugins\WooSync;

use FacturaScripts\Core\Base\CronClass;
use FacturaScripts\Core\Base\DataBase\DataBaseWhere;
use FacturaScripts\Core\Tools;
use FacturaScripts\Plugins\WooSync\Model\WooSyncLog;

class WooSync extends CronClass
{
    public function __construct()
    {
        // Set cron execution time (every 5 minutes)
        $this->setPeriod(300);
    }

    public function run(): void
    {
        // Execute synchronization if enabled
        if (Tools::settings('WooSync', 'enable_auto_sync', false)) {
            $this->syncOrders();
            $this->syncProducts();
            $this->syncStock();
        }
    }

    private function syncOrders(): void
    {
        try {
            $wooApi = new Lib\WooCommerceAPI();
            $since = Tools::settings('WooSync', 'last_sync', '2023-01-01T00:00:00');
            
            $orders = $wooApi->getOrders(['after' => $since, 'status' => 'processing']);
            
            foreach ($orders as $orderData) {
                $this->importOrder($orderData);
            }
            
            Tools::settingsSet('WooSync', 'last_sync', date('c'));
            $this->log('Orders synchronized successfully');
            
        } catch (\Exception $e) {
            $this->log('Error syncing orders: ' . $e->getMessage(), 'ERROR');
        }
    }

    private function syncProducts(): void
    {
        // Product synchronization logic
    }

    private function syncStock(): void
    {
        // Stock synchronization logic
    }

    private function importOrder(array $orderData): void
    {
        // Import order to FacturaScripts
    }

    private function log(string $message, string $level = 'INFO'): void
    {
        $log = new WooSyncLog();
        $log->message = $message;
        $log->level = $level;
        $log->date = date('Y-m-d H:i:s');
        $log->save();
    }
}
