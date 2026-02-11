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
        // Install/Update settings table
        $configModel = new WooSyncConfig();
        $this->updateSettingsTable($configModel);
        
        // Install/Update logs table
        $logModel = new WooSyncLog();
        if (!empty($logModel->install())) {
            $this->dataBase->exec($logModel->install());
        }
    }
    
    /**
     * Update settings table structure
     * Handles migration from old structure to new structure
     */
    private function updateSettingsTable($configModel)
    {
        $tableName = $configModel->tableName();
        
        // Check if table exists
        $tableExists = $this->dataBase->tableExists($tableName);
        
        if (!$tableExists) {
            // Table doesn't exist, create it
            if (!empty($configModel->install())) {
                $this->dataBase->exec($configModel->install());
            }
            return;
        }
        
        // Table exists, check if it has the correct structure
        $columns = $this->dataBase->getColumns($tableName);
        $columnNames = array_column($columns, 'name');
        
        // Check if 'setting_key' column exists
        if (!in_array('setting_key', $columnNames)) {
            // Old structure detected - need to recreate table
            // Drop old table and recreate with new structure
            $this->dataBase->exec("DROP TABLE IF EXISTS {$tableName}");
            
            // Create new table
            if (!empty($configModel->install())) {
                $this->dataBase->exec($configModel->install());
            }
        }
    }
}
