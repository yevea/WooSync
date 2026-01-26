<?php
namespace FacturaScripts\Plugins\WooSync\Controller;

use FacturaScripts\Core\Lib\ExtendedController\BaseController;
use FacturaScripts\Core\Lib\ExtendedController\HtmlView;
use FacturaScripts\Core\Tools;
use FacturaScripts\Core\Model\Settings;
use FacturaScripts\Dinamic\Model\Producto;
use FacturaScripts\Dinamic\Model\Cliente;
use FacturaScripts\Dinamic\Model\PedidoCliente;
use FacturaScripts\Dinamic\Model\LineaPedidoCliente;

class WooSyncConfig extends BaseController
{
    public $wc_url = '';
    public $wc_key = '';
    public $wc_secret = '';

    public function getPageData(): array
    {
        $data = parent::getPageData();
        $data['menu'] = 'admin';
        $data['title'] = 'WooSync Configuration';
        $data['icon'] = 'fas fa-cogs';
        return $data;
    }

    protected function createViews()
    {
        $this->views['WooSyncConfig'] = new HtmlView('WooSyncConfig', 'WooSync Configuration', '', 'WooSyncConfig', 'fas fa-cogs');
    }

    protected function loadData($viewName, $view)
    {
        if ($viewName !== 'WooSyncConfig') {
            return;
        }

        $group = $this->getPluginSettings();

        $this->wc_url = $group['wc_url'] ?? '';
        $this->wc_key = $group['wc_key'] ?? '';
        $this->wc_secret = $group['wc_secret'] ?? '';
    }

    public function privateCore(&$response, $user, $permissions)
    {
        parent::privateCore($response, $user, $permissions);

        $action = $this->request->request->get('action');
        if ($action === 'save-config') {
            $this->saveConfig();
            $this->loadData('WooSyncConfig', $this->views['WooSyncConfig']);
        } elseif ($action === 'sync-now') {
            $this->syncData();
        }
    }

    private function saveConfig()
    {
        $wc_url = $this->request->request->get('wc_url');
        $wc_key = $this->request->request->get('wc_key');
        $wc_secret = $this->request->request->get('wc_secret');

        $group = [
            'wc_url' => $wc_url,
            'wc_key' => $wc_key,
            'wc_secret' => $wc_secret
        ];

        $this->setPluginSettings($group);

        Tools::log()->notice('Config saved successfully.');
    }

    private function syncData()
    {
        $result = $this->syncProducts() && $this->syncCustomers() && $this->syncOrders();
        if ($result) {
            Tools::log()->notice('Sync completed successfully.');
        } else {
            Tools::log()->error('Sync failed. Check logs.');
        }
    }

    private function getPluginSettings(): array
    {
        $settings = new Settings();
        if ($settings->loadFromCode('woosync')) {
            return json_decode($settings->value, true) ?? [];
        }
        return [];
    }

    private function setPluginSettings(array $group): bool
    {
        $settings = new Settings();
        $settings->loadFromCode('woosync');
        $settings->name = 'woosync';
        $settings->value = json_encode($group);
        return $settings->save();
    }

    private function makeApiRequest(string $endpoint): array
    {
        $group = $this->getPluginSettings();

        $url = rtrim($group['wc_url'] ?? '', '/') . '/wp-json/wc/v3/' . $endpoint;
        $key = $group['wc_key'] ?? '';
        $secret = $group['wc_secret'] ?? '';

        if (empty($url) || empty($key) || empty($secret)) {
            Tools::log()->error('Missing WooCommerce credentials.');
            return [];
        }

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, $key . ':' . $secret);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            Tools::log()->error('API error: ' . $httpCode);
            return [];
        }

        return json_decode($response, true) ?? [];
    }

    private function syncProducts(): bool
    {
        $products = $this->makeApiRequest('products?per_page=100');
        foreach ($products as $wcProduct) {
            $fsProduct = new Producto();
            if ($fsProduct->loadFromCode($wcProduct['sku'] ?? '')) {
                $fsProduct->stockfis = $wcProduct['stock_quantity'] ?? 0;
            } else {
                $fsProduct->referencia = $wcProduct['sku'] ?? '';
                $fsProduct->descripcion = $wcProduct['name'] ?? '';
                $fsProduct->precio = $wcProduct['price'] ?? 0;
                $fsProduct->stockfis = $wcProduct['stock_quantity'] ?? 0;
            }
            if (!$fsProduct->save()) {
                Tools::log()->error('Failed to save product: ' . ($wcProduct['name'] ?? 'unknown'));
                return false;
            }
        }
        return true;
    }

    private function syncCustomers(): bool
    {
        $customers = $this->makeApiRequest('customers?per_page=100');
        foreach ($customers as $wcCustomer) {
            $fsCustomer = new Cliente();
            $code = strval($wcCustomer['id'] ?? 0);
            if ($fsCustomer->loadFromCode($code)) {
                $fsCustomer->nombre = ($wcCustomer['first_name'] ?? '') . ' ' . ($wcCustomer['last_name'] ?? '');
                $fsCustomer->email = $wcCustomer['email'] ?? '';
            } else {
                $fsCustomer->codcliente = $code;
                $fsCustomer->nombre = ($wcCustomer['first_name'] ?? '') . ' ' . ($wcCustomer['last_name'] ?? '');
                $fsCustomer->email = $wcCustomer['email'] ?? '';
            }
            if (!$fsCustomer->save()) {
                Tools::log()->error('Failed to save customer: ' . $code);
                return false;
            }
        }
        return true;
    }

    private function syncOrders(): bool
    {
        $orders = $this->makeApiRequest('orders?per_page=100&status=completed');
        foreach ($orders as $wcOrder) {
            $fsOrder = new PedidoCliente();
            $code = strval($wcOrder['id'] ?? 0);
            if ($fsOrder->loadFromCode($code)) {
                continue;
            }
            $fsOrder->numero = $code;
            $fsOrder->codcliente = strval($wcOrder['customer_id'] ?? 0);
            $fsOrder->fecha = date('Y-m-d', strtotime($wcOrder['date_created'] ?? 'now'));
            $fsOrder->total = $wcOrder['total'] ?? 0;
            if (!$fsOrder->save()) {
                Tools::log()->error('Failed to save order: ' . $code);
                return false;
            }
            foreach ($wcOrder['line_items'] ?? [] as $item) {
                $line = new LineaPedidoCliente();
                $line->idpedido = $fsOrder->idpedido;
                $line->referencia = $item['sku'] ?? '';
                $line->cantidad = $item['quantity'] ?? 1;
                $line->pvpunitario = $item['price'] ?? 0;
                if (!$line->save()) {
                    Tools::log()->error('Failed to save order line for order: ' . $code);
                    return false;
                }
            }
        }
        return true;
    }
}
