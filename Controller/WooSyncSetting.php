<?php
namespace FacturaScripts\Plugins\WooSync\Controller;

use FacturaScripts\Core\Base\Controller;
use FacturaScripts\Core\Base\ControllerPermissions;
use FacturaScripts\Plugins\WooSync\Lib\WooCommerceAPI;
use Symfony\Component\HttpFoundation\Response;

class WooSyncSetting extends Controller
{
    public function getPageData(): array  // ADDED: array
    {
        $pageData = parent::getPageData();
        $pageData['title'] = 'WooSync Settings';
        $pageData['menu'] = 'admin';
        $pageData['icon'] = 'fas fa-sync-alt';
        return $pageData;
    }

    public function privateCore(&$response, $user, $permissions): void  // ADDED: void
    {
        parent::privateCore($response, $user, $permissions);
        
        $action = $this->request->get('action', '');
        
        switch ($action) {
            case 'test-connection':
                $this->testConnection();
                break;
                
            case 'save-settings':
                $this->saveSettings();
                break;
                
            case 'sync-now':
                $this->manualSync();
                break;
        }
    }

    private function testConnection(): void  // ADDED: void
    {
        $wooApi = new WooCommerceAPI();
        $result = $wooApi->testConnection();
        
        if ($result === true) {
            $this->toolBox()->i18nLog()->info('Connection successful');
        } else {
            $this->toolBox()->i18nLog()->error('Connection failed: ' . $result);
        }
    }

    private function saveSettings(): void  // ADDED: void
    {
        $settings = [
            'woocommerce_url' => $this->request->get('woocommerce_url'),
            'woocommerce_key' => $this->request->get('woocommerce_key'),
            'woocommerce_secret' => $this->request->get('woocommerce_secret'),
            'enable_auto_sync' => $this->request->get('enable_auto_sync', false),
            'sync_products' => $this->request->get('sync_products', false),
            'sync_stock' => $this->request->get('sync_stock', false),
            'create_customers' => $this->request->get('create_customers', false),
        ];
        
        foreach ($settings as $key => $value) {
            \FacturaScripts\Core\Tools::settingsSet('WooSync', $key, $value);
        }
        
        $this->toolBox()->i18nLog()->info('Settings saved');
    }

    private function manualSync(): void  // ADDED: void
    {
        // Manual synchronization logic
        $this->toolBox()->i18nLog()->info('Manual sync started');
    }
}
