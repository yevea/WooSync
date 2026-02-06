<?php
/**
 * WooSync Initialization
 * Handles plugin installation and updates
 */
namespace FacturaScripts\Plugins\WooSync;

use FacturaScripts\Core\Base\InitClass;
use FacturaScripts\Plugins\WooSync\Model\WooSyncConfig;
use FacturaScripts\Plugins\WooSync\Model\WooSyncLog;

class Init extends InitClass
{
    public function init()
    {
        // Placeholder for loading JS/CSS or registering hooks
    }

    public function update()
    {
        // Install settings table
        $configModel = new WooSyncConfig();
        if (!empty($configModel->install())) {
            $this->dataBase->exec($configModel->install());
        }
        
        // Install logs table
        $logModel = new WooSyncLog();
        if (!empty($logModel->install())) {
            $this->dataBase->exec($logModel->install());
        }
    }
}
