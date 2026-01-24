<?php
namespace FacturaScripts\Plugins\WooSync\Controller;

use FacturaScripts\Core\Base\Controller;
use FacturaScripts\Core\Base\DataBase\DataBaseWhere;  // ← Correct namespace for 2025.x
use FacturaScripts\Core\Model\Settings;
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
        $url    = $this->request->request->get('woosync_url');
        $key    = $this->request->request->get('woosync_key');
        $secret = $this->request->request->get('woosync_secret');

        $this->saveSetting('woosync_url',    $url);
        $this->saveSetting('woosync_key',    $key);
        $this->saveSetting('woosync_secret', $secret);

        $this->toolBox()->i18nLog()->notice('Configuración guardada correctamente.');
    }

    /**
     * Guarda o actualiza una configuración usando DataBaseWhere correctamente
     */
    private function saveSetting($name, $value)
    {
        $setting = new Settings();

        $where = [new DataBaseWhere('name', $name)];

        if ($setting->loadWhere($where)) {
            // Existe → actualizar
            $setting->value = $value;
        } else {
            // No existe → crear nuevo
            $setting->name  = $name;
            $setting->value = $value;
        }

        if (!$setting->save()) {
            $this->toolBox()->i18nLog()->error('No se pudo guardar la clave: ' . $name);
        }
    }

    private function doSync()
    {
        // Leer configuración con el mismo método seguro
        $setting = new Settings();

        $urlWhere    = [new DataBaseWhere('name', 'woosync_url')];
        $keyWhere    = [new DataBaseWhere('name', 'woosync_key')];
        $secretWhere = [new DataBaseWhere('name', 'woosync_secret')];

        $url    = $setting->loadWhere($urlWhere)    ? $setting->value : '';
        $setting->loadWhere($keyWhere);
        $key    = $setting->value ?? '';
        $setting->loadWhere($secretWhere);
        $secret = $setting->value ?? '';

        if (empty($url) || empty($key) || empty($secret)) {
            $this->toolBox()->i18nLog()->error('Configuración incompleta. Por favor, guarda la URL y claves primero.');
            return;
        }

        // Sincronizar productos
        $this->syncProducts($url, $key, $secret);

        // Sincronizar clientes
        $this->syncCustomers($url, $key, $secret);

        // Sincronizar pedidos
        $this->syncOrders($url, $key, $secret);

        $this->toolBox()->i18nLog()->notice('Sincronización completada.');
    }

    private function syncProducts($url, $key, $secret)
    {
        $apiUrl = $url . '/wp-json/wc/v3/products?consumer_key=' . urlencode($key) . '&consumer_secret=' . urlencode($secret) . '&per_page=100';
        $response = @file_get_contents($apiUrl);
        if ($response === false) {
            $this->toolBox()->i18nLog()->error('Error al conectar con la API de productos. Verifica allow_url_fopen o usa cURL.');
            return;
        }

        $products = json_decode($response, true);
        if (!is_array($products)) {
            $this->toolBox()->i18nLog()->error('Respuesta inválida de la API de productos.');
            return;
        }

        foreach ($products as $prod) {
            $producto = new Producto();
            if ($producto->loadFromCode($prod['slug'])) {
                // Actualizar
                $producto->descripcion = $prod['name'];
                $producto->pvp = (float)$prod['price'];
                if (!empty($prod['manage_stock']) && $prod['manage_stock'] === true) {
                    $producto->stockalm = (int)$prod['stock_quantity'];
                }
            } else {
                // Crear
                $producto->referencia  = $prod['slug'];
                $producto->descripcion = $prod['name'];
                $producto->pvp         = (float)$prod['price'];
                if (!empty($prod['manage_stock']) && $prod['manage_stock'] === true) {
                    $producto->stockalm = (int)$prod['stock_quantity'];
                }
            }
            $producto->save();
        }
    }

    private function syncCustomers($url, $key, $secret)
    {
        $apiUrl = $url . '/wp-json/wc/v3/customers?consumer_key=' . urlencode($key) . '&consumer_secret=' . urlencode($secret) . '&per_page=100';
        $response = @file_get_contents($apiUrl);
        if ($response === false) {
            $this->toolBox()->i18nLog()->error('Error al conectar con la API de clientes.');
            return;
        }

        $customers = json_decode($response, true);
        if (!is_array($customers)) {
            $this->toolBox()->i18nLog()->error('Respuesta inválida de la API de clientes.');
            return;
        }

        foreach ($customers as $cust) {
            $cliente = new Cliente();
            $where = [new DataBaseWhere('email', $cust['email'])];
            if ($cliente->loadWhere($where)) {
                // Actualizar
                $cliente->nombre = trim($cust['first_name'] . ' ' . $cust['last_name']);
            } else {
                // Crear
                $cliente->codcliente = 'WOO-' . $cust['id'];
                $cliente->nombre     = trim($cust['first_name'] . ' ' . $cust['last_name']);
                $cliente->email      = $cust['email'];
                $cliente->cifnif     = 'WOO-' . $cust['id']; // temporal
            }
            $cliente->save();
        }
    }

    private function syncOrders($url, $key, $secret)
    {
        $apiUrl = $url . '/wp-json/wc/v3/orders?consumer_key=' . urlencode($key) . '&consumer_secret=' . urlencode($secret) . '&per_page=100';
        $response = @file_get_contents($apiUrl);
        if ($response === false) {
            $this->toolBox()->i18nLog()->error('Error al conectar con la API de pedidos.');
            return;
        }

        $orders = json_decode($response, true);
        if (!is_array($orders)) {
            $this->toolBox()->i18nLog()->error('Respuesta inválida de la API de pedidos.');
            return;
        }

        foreach ($orders as $ord) {
            $pedido = new PedidoCliente();
            $where = [new DataBaseWhere('codigo', 'WOO-' . $ord['id'])];
            if ($pedido->loadWhere($where)) {
                continue; // evitar duplicados
            }

            // Buscar cliente por email
            $cliente = new Cliente();
            $cliWhere = [new DataBaseWhere('email', $ord['billing']['email'])];
            $cliente->loadWhere($cliWhere);

            $pedido->codcliente     = $cliente->codcliente ?: null;
            $pedido->nombrecliente  = trim($ord['billing']['first_name'] . ' ' . $ord['billing']['last_name']);
            $pedido->cifnif         = $cliente->cifnif;
            $pedido->total          = (float)$ord['total'];
            $pedido->idestado       = 1; // Asumiendo 1 = nuevo
            $pedido->codigo         = 'WOO-' . $ord['id'];

            if (!$pedido->save()) {
                continue;
            }

            foreach ($ord['line_items'] as $item) {
                $linea = new LineaPedidoCliente();
                $linea->idpedido     = $pedido->idpedido;
                $linea->referencia   = $item['sku'] ?: (string)$item['product_id'];
                $linea->descripcion  = $item['name'];
                $linea->cantidad     = (float)$item['quantity'];
                $linea->pvpunitario  = (float)$item['price'];
                $linea->save();
            }
        }
    }
}
