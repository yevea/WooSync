<?php
namespace FacturaScripts\Plugins\WooSync\Controller;

use FacturaScripts\Core\App\AppSettings;
use FacturaScripts\Core\Base\Controller;
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
        $settings = AppSettings::getDefault();
        $settings->set('woosync_url', $this->request->request->get('woosync_url'));
        $settings->set('woosync_key', $this->request->request->get('woosync_key'));
        $settings->set('woosync_secret', $this->request->request->get('woosync_secret'));
        $settings->save();
    }

    private function doSync()
    {
        $settings = AppSettings::getDefault();
        $url = rtrim($settings->get('woosync_url'), '/');
        $key = $settings->get('woosync_key');
        $secret = $settings->get('woosync_secret');

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
        $apiUrl = $url . '/wp-json/wc/v3/products?consumer_key=' . $key . '&consumer_secret=' . $secret . '&per_page=100';
        $response = @file_get_contents($apiUrl);
        if ($response === false) {
            $this->toolBox()->i18nLog()->error('Error al conectar con la API de productos.');
            return;
        }

        $products = json_decode($response, true);
        foreach ($products as $prod) {
            $producto = new Producto();
            if ($producto->loadFromCode($prod['slug'])) {
                // Actualizar existente
                $producto->descripcion = $prod['name'];
                $producto->pvp = (float)$prod['price'];
                if ($prod['manage_stock']) {
                    $producto->stockalm = (int)$prod['stock_quantity'];
                }
            } else {
                // Crear nuevo
                $producto->referencia = $prod['slug'];
                $producto->descripcion = $prod['name'];
                $producto->pvp = (float)$prod['price'];
                if ($prod['manage_stock']) {
                    $producto->stockalm = (int)$prod['stock_quantity'];
                }
            }
            $producto->save();
        }
    }

    private function syncCustomers($url, $key, $secret)
    {
        $apiUrl = $url . '/wp-json/wc/v3/customers?consumer_key=' . $key . '&consumer_secret=' . $secret . '&per_page=100';
        $response = @file_get_contents($apiUrl);
        if ($response === false) {
            $this->toolBox()->i18nLog()->error('Error al conectar con la API de clientes.');
            return;
        }

        $customers = json_decode($response, true);
        foreach ($customers as $cust) {
            $cliente = new Cliente();
            if ($cliente->loadFromCode($cust['email'])) {
                // Actualizar existente (usando email como código único)
                $cliente->nombre = $cust['first_name'] . ' ' . $cust['last_name'];
            } else {
                // Crear nuevo
                $cliente->codcliente = 'WOO-' . $cust['id']; // Temporal
                $cliente->nombre = $cust['first_name'] . ' ' . $cust['last_name'];
                $cliente->email = $cust['email'];
                $cliente->cifnif = 'WOO-' . $cust['id']; // Usar ID como CIF temporal
            }
            $cliente->save();
        }
    }

    private function syncOrders($url, $key, $secret)
    {
        $apiUrl = $url . '/wp-json/wc/v3/orders?consumer_key=' . $key . '&consumer_secret=' . $secret . '&per_page=100';
        $response = @file_get_contents($apiUrl);
        if ($response === false) {
            $this->toolBox()->i18nLog()->error('Error al conectar con la API de pedidos.');
            return;
        }

        $orders = json_decode($response, true);
        foreach ($orders as $ord) {
            $pedido = new PedidoCliente();
            if ($pedido->loadFromCode('WOO-' . $ord['id'])) {
                continue; // Evitar duplicados
            }

            // Encontrar cliente
            $cliente = new Cliente();
            $cliente->loadFromCode($ord['billing']['email']);

            $pedido->codcliente = $cliente->codcliente;
            $pedido->nombrecliente = $ord['billing']['first_name'] . ' ' . $ord['billing']['last_name'];
            $pedido->cifnif = $cliente->cifnif;
            $pedido->total = (float)$ord['total'];
            $pedido->idestado = 1; // Nuevo
            $pedido->codigo = 'WOO-' . $ord['id'];
            if (!$pedido->save()) {
                continue;
            }

            // Líneas
            foreach ($ord['line_items'] as $item) {
                $linea = new LineaPedidoCliente();
                $linea->idpedido = $pedido->idpedido;
                $linea->referencia = $item['sku'] ?: $item['product_id'];
                $linea->descripcion = $item['name'];
                $linea->cantidad = $item['quantity'];
                $linea->pvpunitario = $item['price'];
                $linea->save();
            }
        }
    }
}
