<?php
namespace FacturaScripts\Plugins\WooSync\Controller;

use FacturaScripts\Core\Base\DataBase\DataBaseWhere;
use FacturaScripts\Core\Lib\ExtendedController\BaseController;
use FacturaScripts\Core\Lib\ExtendedController\EditView;
use FacturaScripts\Core\Tools;
use FacturaScripts\Dinamic\Model\WooSyncConfig as ModelWooSyncConfig;
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
        $viewName = 'EditWooSyncConfig';
        $this->addEditView($viewName, 'WooSyncConfig', 'WooSync Configuration', 'fas fa-cogs');
    }

    protected function loadData($viewName, $view)
    {
        if ($viewName !== 'EditWooSyncConfig') {
            return;
        }

        $model = new ModelWooSyncConfig();
        $where = [new DataBaseWhere('id', 1)];
        $view->loadData(false, $where);
        if (!$model->exists()) {
            // Create default if not exists
            $model->id = 1;
            $model->wc_url = '';
            $model->wc_key = '';
            $model->wc_secret = '';
            $model->save();
        }
        $view->model = $model;
    }

    public function privateCore(&$response, $user, $permissions)
    {
        parent::privateCore($response, $user, $permissions);

        $action = $this->request->request->get('action');
        if ($action === 'edit' || $action === 'save-config') { // Handle save
            $this->saveConfig();
        } elseif ($action === 'sync-now') {
            $this->syncData();
        }
    }

    private function saveConfig()
    {
        $viewName = 'EditWooSyncConfig';
        $view = $this->views[$viewName];
        $model = $view->model;

        $model->wc_url = $this->request->request->get('wc_url');
        $model->wc_key = $this->request->request->get('wc_key');
        $model->wc_secret = $this->request->request->get('wc_secret');

        if ($model->save()) {
            Tools::log()->notice('Config saved successfully.');
        } else {
            Tools::log()->error('Failed to save config.');
        }
    }

    private function syncData()
    {
        $viewName = 'EditWooSyncConfig';
        $model = $this->views[$viewName]->model;

        $result = $this->syncProducts($model) && $this->syncCustomers($model) && $this->syncOrders($model);
        if ($result) {
            Tools::log()->notice('Sync completed successfully.');
        } else {
            Tools::log()->error('Sync failed. Check logs.');
        }
    }

    private function makeApiRequest(string $endpoint, ModelWooSyncConfig $model): array
    {
        $url = rtrim($model->wc_url, '/') . '/wp-json/wc/v3/' . $endpoint;
        $key = $model->wc_key;
        $secret = $model->wc_secret;

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

    private function syncProducts(ModelWooSyncConfig $model): bool
    {
        $products = $this->makeApiRequest('products?per_page=100', $model);
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

    private function syncCustomers(ModelWooSyncConfig $model): bool
    {
        $customers = $this->makeApiRequest('customers?per_page=100', $model);
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

    private function syncOrders(ModelWooSyncConfig $model): bool
    {
        $orders = $this->makeApiRequest('orders?per_page=100&status=completed', $model);
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
