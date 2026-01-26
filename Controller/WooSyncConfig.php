<?php
namespace FacturaScripts\Plugins\WooSync\Controller;

use FacturaScripts\Core\Lib\ExtendedController\BaseController;
use FacturaScripts\Core\Lib\ExtendedController\HtmlView;
use FacturaScripts\Core\Model\AppSettings;
use FacturaScripts\Core\Tools;
use FacturaScripts\Dinamic\Model\Producto;
use FacturaScripts\Dinamic\Model\Cliente;
use FacturaScripts\Dinamic\Model\PedidoCliente;
use FacturaScripts\Dinamic\Model\LineaPedidoCliente;

class WooSyncConfig extends BaseController
{
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
        $this->addHtmlView($viewName, 'WooSyncConfig', '', 'WooSync Configuration');
    }

    protected function loadData($viewName, $view)
    {
        if ($viewName !== 'WooSyncConfig') {
            return;
        }

        // Load settings and pass to template via setTemplateVar
        $appSettings = new AppSettings();
        $this->setTemplateVar('wc_url', $appSettings->get('woosync', 'wc_url', ''));
        $this->setTemplateVar('wc_key', $appSettings->get('woosync', 'wc_key', ''));
        $this->setTemplateVar('wc_secret', $appSettings->get('woosync', 'wc_secret', ''));
    }

    public function privateCore(&$response, $user, $permissions)
    {
        parent::privateCore($response, $user, $permissions);

        $action = $this->request->request->get('action');
        if ($action === 'save-config') {
            $this->saveConfig();
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
        // Implement sync logic here
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
        $products = $this->makeApiRequest('products?per_page=100'); // Paginate if >100
        foreach ($products as $wcProduct) {
            $fsProduct = new Producto();
            if ($fsProduct->loadFromCode($wcProduct['sku'])) {
                // Update existing
                $fsProduct->stockfis = $wcProduct['stock_quantity'];
            } else {
                // Create new
                $fsProduct->referencia = $wcProduct['sku'];
                $fsProduct->descripcion = $wcProduct['name'];
                $fsProduct->precio = $wcProduct['price'];
                $fsProduct->stockfis = $wcProduct['stock_quantity'];
            }
            if (!$fsProduct->save()) {
                Tools::log()->error('Failed to save product: ' . $wcProduct['name']);
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
            $code = strval($wcCustomer['id']);
            if ($fsCustomer->loadFromCode($code)) {
                // Update
                $fsCustomer->nombre = $wcCustomer['first_name'] . ' ' . $wcCustomer['last_name'];
                $fsCustomer->email = $wcCustomer['email'];
            } else {
                // Create
                $fsCustomer->codcliente = $code;
                $fsCustomer->nombre = $wcCustomer['first_name'] . ' ' . $wcCustomer['last_name'];
                $fsCustomer->email = $wcCustomer['email'];
            }
            if (!$fsCustomer->save()) {
                return false;
            }
        }
        return true;
    }

    private function syncOrders(): bool
    {
        $orders = $this->makeApiRequest('orders?per_page=100&status=completed'); // Only completed orders
        foreach ($orders as $wcOrder) {
            $fsOrder = new PedidoCliente();
            $code = strval($wcOrder['id']);
            if ($fsOrder->loadFromCode($code)) {
                continue; // Skip if already exists (adjust as needed)
            }
            $fsOrder->numero = $code;
            $fsOrder->codcliente = strval($wcOrder['customer_id']);
            $fsOrder->fecha = date('Y-m-d', strtotime($wcOrder['date_created']));
            $fsOrder->total = $wcOrder['total'];
            if (!$fsOrder->save()) {
                return false;
            }
            // Add lines (items) after saving order
            foreach ($wcOrder['line_items'] as $item) {
                $line = new LineaPedidoCliente();
                $line->idpedido = $fsOrder->idpedido;
                $line->referencia = $item['sku'];
                $line->cantidad = $item['quantity'];
                $line->pvpunitario = $item['price'];
                if (!$line->save()) {
                    return false;
                }
            }
        }
        return true;
    }
}
