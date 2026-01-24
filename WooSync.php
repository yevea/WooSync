<?php
namespace FacturaScripts\Plugins\WooSync;

use FacturaScripts\Core\Base\Menu\MenuItem;
use FacturaScripts\Core\Base\PluginManager;
use FacturaScripts\Core\PluginAbstract;

/**
 * Clase principal del plugin WooSync.
 */
class Plugin extends PluginAbstract
{
    public function init()
    {
        // Añade entrada al menú "Herramientas"
        $menuManager = PluginManager::getMenuManager();
        $menuManager->addItem('tools', new MenuItem('WooSyncConfig', 'WooSync', 'fas fa-sync-alt'));
    }

    public function update()
    {
        // Código para actualizaciones futuras, como crear tablas si es necesario.
    }
}
