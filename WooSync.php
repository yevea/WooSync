<?php
/**
 * WooSync - WooCommerce Synchronization Plugin for FacturaScripts
 */

namespace FacturaScripts\Plugins\WooSync;

use FacturaScripts\Core\Base\CronClass;
use FacturaScripts\Core\Base\PluginDeploy;
use FacturaScripts\Core\Tools;
use FacturaScripts\Plugins\WooSync\Model\WooSyncLog;

class WooSync extends CronClass
{
    public function __construct()
    {
        // Set cron execution time (every 5 minutes)
        $this->setPeriod(300);
    }

    public function deploy(): void
    {
        // Install database tables
        $installer = new PluginDeploy();
        $installer->deploy($this->getDirectory() . '/DataBase');
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
            // Your sync logic here
            WooSyncLog::logMessage('Orders synchronization started', 'INFO', 'orders');
            
            // Log completion
            WooSyncLog::logMessage('Orders synchronized successfully', 'INFO', 'orders');
            
        } catch (\Exception $e) {
            WooSyncLog::logMessage('Error syncing orders: ' . $e->getMessage(), 'ERROR', 'orders');
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
}
