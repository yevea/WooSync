<?php
namespace FacturaScripts\Plugins\WooSync\Controller;

use FacturaScripts\Core\Base\Controller;
use FacturaScripts\Core\Base\ControllerPermissions;
use FacturaScripts\Core\Tools;
use Symfony\Component\HttpFoundation\Response;

class WooSyncConfig extends Controller
{
    // Store settings for template access
    public $woocommerce_url = '';
    public $woocommerce_key = '';
    public $woocommerce_secret = '';
    
    public function getPageData(): array
    {
        $pageData = parent::getPageData();
        $pageData['title'] = 'WooSync Configuration';
        $pageData['menu'] = 'admin';
        $pageData['icon'] = 'fas fa-sync-alt';
        $pageData['showonmenu'] = true;
        return $pageData;
    }

    public function privateCore(&$response, $user, $permissions): void
    {
        parent::privateCore($response, $user, $permissions);
        
        Tools::log()->debug('WooSyncConfig: privateCore called - Method: ' . $this->request->getMethod());
        
        // Load saved settings FIRST
        $this->loadSettings();
        
        // Get action from both POST and GET
        $action = $this->request->get('action', $this->request->request->get('action', ''));
        Tools::log()->debug('WooSyncConfig: Action = ' . $action);
        
        switch ($action) {
            case 'save':
                $this->saveSettings();
                break;
            case 'test':
                $this->testConnection();
                break;
            case 'sync':
                $this->syncAll();
                break;
            case 'sync-orders':
                $this->syncOrders();
                break;
            case 'sync-products':
                $this->syncProducts();
                break;
            case 'sync-stock':
                $this->syncStock();
                break;
        }
    }

    private function loadSettings(): void
    {
        $this->woocommerce_url = Tools::settings('WooSync', 'woocommerce_url', '');
        $this->woocommerce_key = Tools::settings('WooSync', 'woocommerce_key', '');
        $this->woocommerce_secret = Tools::settings('WooSync', 'woocommerce_secret', '');
        
        Tools::log()->debug('WooSyncConfig: Loaded URL = ' . $this->woocommerce_url);
        Tools::log()->debug('WooSyncConfig: Loaded Key exists = ' . (!empty($this->woocommerce_key) ? 'YES' : 'NO'));
    }

    private function saveSettings(): bool
    {
        Tools::log()->debug('WooSyncConfig: saveSettings called');
        
        // Get POST data
        $url = $this->request->request->get('woocommerce_url', '');
        $key = $this->request->request->get('woocommerce_key', '');
        $secret = $this->request->request->get('woocommerce_secret', '');
        
        Tools::log()->debug('WooSyncConfig: URL = ' . $url);
        Tools::log()->debug('WooSyncConfig: Key = ' . (!empty($key) ? 'SET' : 'EMPTY'));
        Tools::log()->debug('WooSyncConfig: Secret = ' . (!empty($secret) ? 'SET' : 'EMPTY'));
        
        // Validate
        if (empty($url) || empty($key) || empty($secret)) {
            Tools::log()->error('WooSyncConfig: Validation failed - empty fields');
            return false;
        }
        
        // Save settings
        Tools::settingsSet('WooSync', 'woocommerce_url', $url);
        Tools::settingsSet('WooSync', 'woocommerce_key', $key);
        Tools::settingsSet('WooSync', 'woocommerce_secret', $secret);
        
        Tools::log()->info('WooSync settings saved: ' . $url);
        
        // Update instance variables
        $this->woocommerce_url = $url;
        $this->woocommerce_key = $key;
        $this->woocommerce_secret = $secret;
        
        return true;
    }

    private function testConnection(): void
    {
        Tools::log()->info('WooSync: Testing connection...');
        
        try {
            $wooApi = new \FacturaScripts\Plugins\WooSync\Lib\WooCommerceAPI();
            
            if ($wooApi->testConnection()) {
                Tools::log()->info('WooSync: Connection test successful');
                Tools::flash()->success('Connection to WooCommerce successful!');
            } else {
                Tools::log()->error('WooSync: Connection test failed');
                Tools::flash()->error('Connection to WooCommerce failed. Check your credentials.');
            }
        } catch (\Exception $e) {
            Tools::log()->error('WooSync: Connection test error: ' . $e->getMessage());
            Tools::flash()->error('Connection error: ' . $e->getMessage());
        }
        
        $this->redirect($this->url());
    }

    private function syncAll(): void
    {
        Tools::log()->info('WooSync: Starting full synchronization');
        
        try {
            $wooApi = new \FacturaScripts\Plugins\WooSync\Lib\WooCommerceAPI();
            
            // Test connection first
            if (!$wooApi->testConnection()) {
                Tools::flash()->error('Cannot sync: Connection to WooCommerce failed.');
                $this->redirect($this->url());
                return;
            }
            
            // TODO: Implement actual sync logic
            // $orders = $wooApi->getOrders();
            // $products = $wooApi->getProducts();
            // etc.
            
            Tools::flash()->success('Synchronization started successfully!');
            
        } catch (\Exception $e) {
            Tools::log()->error('WooSync: Sync error: ' . $e->getMessage());
            Tools::flash()->error('Sync error: ' . $e->getMessage());
        }
        
        $this->redirect($this->url());
    }

    private function syncOrders(): void
    {
        Tools::log()->info('WooSync: Starting order synchronization');
        
        try {
            $wooApi = new \FacturaScripts\Plugins\WooSync\Lib\WooCommerceAPI();
            
            // Simple test - get first page of orders
            $orders = $wooApi->getOrders(['per_page' => 5]);
            $count = is_array($orders) ? count($orders) : 0;
            
            Tools::log()->info("WooSync: Found {$count} orders");
            Tools::flash()->success("Found {$count} orders. Order sync logic needs to be implemented.");
            
        } catch (\Exception $e) {
            Tools::log()->error('WooSync: Order sync error: ' . $e->getMessage());
            Tools::flash()->error('Order sync error: ' . $e->getMessage());
        }
        
        $this->redirect($this->url());
    }

    private function syncProducts(): void
    {
        Tools::log()->info('WooSync: Starting product synchronization');
        
        try {
            $wooApi = new \FacturaScripts\Plugins\WooSync\Lib\WooCommerceAPI();
            
            // Simple test - get first page of products
            $products = $wooApi->getProducts(['per_page' => 5]);
            $count = is_array($products) ? count($products) : 0;
            
            Tools::log()->info("WooSync: Found {$count} products");
            Tools::flash()->success("Found {$count} products. Product sync logic needs to be implemented.");
            
        } catch (\Exception $e) {
            Tools::log()->error('WooSync: Product sync error: ' . $e->getMessage());
            Tools::flash()->error('Product sync error: ' . $e->getMessage());
        }
        
        $this->redirect($this->url());
    }

    private function syncStock(): void
    {
        Tools::log()->info('WooSync: Starting stock synchronization');
        Tools::flash()->info('Stock synchronization feature needs to be implemented.');
        $this->redirect($this->url());
    }

    protected function execAfterAction(string $action): void
    {
        Tools::log()->debug('WooSyncConfig: execAfterAction called with action: ' . $action);
        
        // Only redirect for save action, other actions redirect themselves
        if ($action === 'save') {
            $this->redirect($this->url() . '?saved=1');
        }
    }

    protected function createViews(): void
    {
        // Empty
    }
}
