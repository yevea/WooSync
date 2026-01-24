<?php
namespace FacturaScripts\Plugins\WooSync\Controller;

use FacturaScripts\Core\Base\Controller;
use FacturaScripts\Core\Base\AppSettings;  // ← This is the correct import!
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
        // Use the correct AppSettings class
        AppSettings::set('woosync_url', $this->request->request->get('woosync_url'));
        AppSettings::set('woosync_key', $this->request->request->get('woosync_key'));
        AppSettings::set('woosync_secret', $this->request->request->get('woosync_secret'));
        AppSettings::save();  // This persists to database
    }

    // The rest of the class (doSync, syncProducts, syncCustomers, syncOrders) remains the same as before
    // ... (no changes needed there unless you see new errors)
}
