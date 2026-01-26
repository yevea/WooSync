<?php
namespace FacturaScripts\Plugins\WooSync;

use FacturaScripts\Core\Base\InitClass;
use FacturaScripts\Plugins\WooSync\Model\WooSyncConfig;

class Init extends InitClass
{
    public function init()
    {
        // Placeholder
    }

    public function update()
    {
        $model = new WooSyncConfig();
        $model->install();
    }
}
