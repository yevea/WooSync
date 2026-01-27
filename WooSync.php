<?php
/**
 * WooSync - WooCommerce Synchronization Plugin for FacturaScripts
 */

namespace FacturaScripts\Plugins\WooSync;

use FacturaScripts\Core\Base\CronClass;
use FacturaScripts\Core\Tools;

class WooSync extends CronClass
{
    public function __construct()
    {
        // Set cron execution time (every 15 minutes)
        $this->setPeriod(900);
    }

    public function run(): void
    {
        // Execute synchronization if enabled
        if (Tools::settings('WooSync', 'enable_auto_sync', false)) {
            Tools::log()->info('WooSync: Auto-sync running');
            // Auto-sync logic would go here
        }
    }
}
