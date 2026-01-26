<?php
namespace FacturaScripts\Plugins\WooSync\Controller;

use FacturaScripts\Core\Lib\ExtendedController\PanelController;
use FacturaScripts\Core\Lib\ExtendedController\HtmlView;
use FacturaScripts\Core\App\AppSettings;
use FacturaScripts\Core\Tools;
use FacturaScripts\Dinamic\Model\Producto;
use FacturaScripts\Dinamic\Model\Cliente;
use FacturaScripts\Dinamic\Model\PedidoCliente;
use FacturaScripts\Dinamic\Model\LineaPedidoCliente;

class WooSyncConfig extends PanelController
{
    public $wc_url;
    public $wc_key;
    public $wc_secret;

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
        $viewName = 'WooSyncConfig';
        $title = 'WooSync Configuration';
        $icon = 'fas fa-cogs';

        $view = new HtmlView($viewName, $title, '', 'WooSyncConfig', $icon);
        $this->addCustomView($viewName, $view);
    }

    protected function loadData($viewName, $view)
    {
        if ($viewName !== 'WooSyncConfig') {
            return;
        }

        $appSettings = new AppSettings();
        $appSettings->reload(); // Ensure settings are loaded from DB

        $this->wc_url = $appSettings->get('woosync', 'wc_url', '');
        $this->wc_key = $appSettings->get('woosync', 'wc_key', '');
        $this->wc_secret = $appSettings->get('woosync', 'wc_secret', '');
    }

    public function privateCore(&$response, $user, $permissions)
    {
        parent::privateCore($response, $user, $permissions);

        $action = $this->request->request->get('action');
        if ($action === 'save-config') {
            $this->saveConfig();
            // Reload settings after save
            $this->loadData('WooSyncConfig', $this->views['WooSyncConfig']);
        } elseif ($action === 'sync-now') {
            $this->syncData();
        }
    }

    private function saveConfig()
    {
        $appSettings = new AppSettings();
        $appSettings->set('woosync', 'wc_url', $this->request->request->get('wc_url'));
        $appSettings->set('woosync', 'wc_key', $this->request->request->get('wc_key'));
        $appSettings->set('woosync', 'wc_secret', $this->request->request->get('wc_secret'));
        $appSettings->save();
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

    private function makeApiRequest(string $endpoint): array
    {
        $appSettings = new AppSettings();
        $appSettings->reload();

        $url = rtrim($appSettings->get('woosync', 'wc_url', ''), '/') . '/wp-json/wc/v3/' . $endpoint;
        $key = $appSettings->get('woosync', 'wc_key', '');
        $secret = $appSettings->get('woosync', 'wc_secret', '');

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
                    return false;
                }
            }
        }
        return true;
    }
}
