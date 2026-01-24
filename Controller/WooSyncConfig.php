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
    $url = $this->request->request->get('woosync_url');
    $key = $this->request->request->get('woosync_key');
    $secret = $this->request->request->get('woosync_secret');

    // Save each setting as a separate row in the 'settings' table
    $this->saveSetting('woosync_url', $url);
    $this->saveSetting('woosync_key', $key);
    $this->saveSetting('woosync_secret', $secret);

    $this->toolBox()->i18nLog()->notice('Configuración guardada correctamente.');
}

/**
 * Helper to save or update a single setting.
 */
private function saveSetting($name, $value)
{
    $setting = new \FacturaScripts\Core\Model\Settings();
    
    // Try to load existing
    if (!$setting->loadWhere(['name' => $name])) {
        // New setting
        $setting->name = $name;
    }
    
    $setting->value = $value;  // Can be string, json_encode if array needed later
    $setting->save();
}
    // ... The rest of the file (doSync, syncProducts, syncCustomers, syncOrders) stays exactly the same
}
