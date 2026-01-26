<?php
namespace FacturaScripts\Plugins\WooSync\Controller;

use FacturaScripts\Core\Lib\ExtendedController\BaseController;
use FacturaScripts\Core\Model\AppSettings;
use FacturaScripts\Core\Tools;

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

    public function privateCore(&$response, $user, $permissions)
    {
        parent::privateCore($response, $user, $permissions);

        $action = $this->request->request->get('action');
        if ($action === 'save-config') {
            $this->saveConfig();
        } elseif ($action === 'sync-now') {
            $this->syncData();
        }

        // Load saved settings for display
        $appSettings = new AppSettings();
        $this->views['WooSyncConfig']->model->wc_url = $appSettings->get('woosync', 'wc_url', '');
        $this->views['WooSyncConfig']->model->wc_key = $appSettings->get('woosync', 'wc_key', '');
        $this->views['WooSyncConfig']->model->wc_secret = $appSettings->get('woosync', 'wc_secret', '');
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
        // Implement sync logic here (see Step 3)
        $result = $this->syncProducts() && $this->syncCustomers() && $this->syncOrders();
        if ($result) {
            Tools::log()->notice('Sync completed successfully.');
        } else {
            Tools::log()->error('Sync failed. Check logs.');
        }
    }

    // Add sync methods below (see Step 3)
}
