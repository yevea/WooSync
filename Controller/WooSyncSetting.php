<?php
namespace FacturaScripts\Plugins\WooSync\Controller;

use FacturaScripts\Core\Base\Controller;
use FacturaScripts\Core\Base\ControllerPermissions;
use FacturaScripts\Plugins\WooSync\Lib\WooCommerceAPI;
use FacturaScripts\Core\Tools;
use Symfony\Component\HttpFoundation\Response;

class WooSyncSettings extends Controller
{
    public function getPageData(): array
    {
        $pageData = parent::getPageData();
        $pageData['title'] = 'WooSync Settings';
        $pageData['menu'] = 'admin';
        $pageData['icon'] = 'fas fa-sync-alt';
        return $pageData;
    }

    public function privateCore(&$response, $user, $permissions): void
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

    private function testConnection(): void
    {
        try {
            $wooApi = new WooCommerceAPI();
            $result = $wooApi->testConnection();
            
            if ($result === true) {
                Tools::log()->info('Connection to WooCommerce successful');
            } else {
                Tools::log()->error('Connection failed: ' . $result);
            }
        } catch (\Exception $e) {
            Tools::log()->error('Connection test error: ' . $e->getMessage());
        }
    }

    private function saveSettings(): void
    {
        $settings = [
            'woocommerce_url' => $this->request->get('woocommerce_url', ''),
            'woocommerce_key' => $this->request->get('woocommerce_key', ''),
            'woocommerce_secret' => $this->request->get('woocommerce_secret', ''),
            'enable_auto_sync' => $this->request->get('enable_auto_sync', false),
            'sync_products' => $this->request->get('sync_products', false),
            'sync_stock' => $this->request->get('sync_stock', false),
            'create_customers' => $this->request->get('create_customers', false),
        ];
        
        foreach ($settings as $key => $value) {
            Tools::settingsSet('WooSync', $key, $value);
        }
        
        Tools::log()->info('WooSync settings saved');
        $this->toolBox()->i18nLog()->info('settings-saved');
    }

    private function manualSync(): void
    {
        // Manual synchronization logic
        Tools::log()->info('Manual synchronization started');
        
        // You would call your sync methods here
        // $wooApi = new WooCommerceAPI();
        // $wooApi->syncOrders();
        // etc.
        
        $this->toolBox()->i18nLog()->info('Manual synchronization completed');
    }

    protected function createViews(): void
    {
        // Create settings view
        $this->addHtmlView('WooSyncSettings', 'WooSyncSettings.html.twig', 'WooSyncSettings');
    }

    protected function execAfterAction(string $action): void
    {
        switch ($action) {
            case 'test-connection':
            case 'save-settings':
            case 'sync-now':
                $this->redirect($this->url() . '?code=' . $this->request->get('code'));
                break;
        }
    }
}
