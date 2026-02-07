<?php
/**
 * WooSync - WooCommerce Synchronization Plugin for FacturaScripts
 * 
 * Main plugin class for WooCommerce to FacturaScripts synchronization.
 * Designed for shared hosting environments without CLI access.
 * 
 * Note: Auto-sync via cron is not available on shared hosting.
 * Use the admin UI (WooSyncConfig) to manually trigger synchronization.
 */

namespace FacturaScripts\Plugins\WooSync;

use FacturaScripts\Core\Base\CronClass;
use FacturaScripts\Plugins\WooSync\Model\WooSyncConfig;

class WooSync extends CronClass
{
    public function __construct()
    {
        // Cron is disabled by default for shared hosting compatibility
        // If you have cron access, you can enable this by setting a shorter period
        $this->setPeriod(0); // 0 = disabled
    }

    public function run(): void
    {
        // This method would be called by FacturaScripts cron if enabled
        // However, on shared hosting without CLI access, this won't run
        // Use the admin UI for manual synchronization instead
        
        $autoSyncEnabled = WooSyncConfig::getSetting('enable_auto_sync', 'false');
        
        if ($autoSyncEnabled === 'true') {
            // If auto-sync is somehow enabled, log it
            // But don't actually sync to avoid timeout issues
            \FacturaScripts\Plugins\WooSync\Model\WooSyncLog::logMessage(
                'Auto-sync triggered via cron (not recommended for shared hosting)',
                'INFO',
                'cron'
            );
        }
    }
}
