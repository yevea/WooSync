<?php
namespace FacturaScripts\Plugins\WooSync;

use FacturaScripts\Core\Base\InitClass;

class Init extends InitClass
{
    public function init()
    {
        // Load any JS/CSS if needed, or register cron jobs (but skip for now since no CLI)
    }

    public function update()
    {
        // Run migrations if needed (e.g., add custom tables)
    }
}
