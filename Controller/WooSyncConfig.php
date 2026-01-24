<?php
namespace FacturaScripts\Plugins\WooSync\Controller;

use FacturaScripts\Core\Base\Controller;
use FacturaScripts\Core\App\AppSettings;  // ← Correct namespace for 2025.x versions!

// Keep these other uses if they are already there
use FacturaScripts\Dinamic\Model\Cliente;
use FacturaScripts\Dinamic\Model\LineaPedidoCliente;
use FacturaScripts\Dinamic\Model\PedidoCliente;
use FacturaScripts\Dinamic\Model\Producto;

/**
 * Controlador para la configuración y sincronización de WooSync.
 */
class WooSyncConfig extends Controller
{
    public function privateCore(&$response, $user, $permissions)
    {
        parent::privateCore($response, $user, $permissions);

        $action = $this->request->request->get('action');
        if ($action === 'save') {
            $this->saveSettings();
            $this->toolBox()->i18nLog()->notice('Configuración guardada correctamente.');
        } elseif ($action === 'sync') {
            $this->doSync();
        }

        $this->setTemplate('WooSyncConfig');
    }

    private function saveSettings()
    {
        // Correct usage for 2025.71: static methods on AppSettings
        AppSettings::set('woosync_url', $this->request->request->get('woosync_url'));
        AppSettings::set('woosync_key', $this->request->request->get('woosync_key'));
        AppSettings::set('woosync_secret', $this->request->request->get('woosync_secret'));
        AppSettings::save();  // Persists changes to the database
    }

    // ... The rest of the file (doSync, syncProducts, syncCustomers, syncOrders) stays exactly the same
}
