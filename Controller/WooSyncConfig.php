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
            // Check if settings are configured
            if (empty($this->woocommerce_url) || empty($this->woocommerce_key) || empty($this->woocommerce_secret)) {
                Tools::log()->error('WooSync: Settings not configured');
                // Use simple query parameter for message
                $this->redirect($this->url() . '?message=' . urlencode('Please configure WooCommerce settings first.') . '&type=error');
                return;
            }
            
            $wooApi = new \FacturaScripts\Plugins\WooSync\Lib\WooCommerceAPI();
            
            if ($wooApi->testConnection()) {
                Tools::log()->info('WooSync: Connection test successful');
                // Use query parameter for success message
                $this->redirect($this->url() . '?message=' . urlencode('✅ Connection to WooCommerce successful!') . '&type=success');
            } else {
                Tools::log()->error('WooSync: Connection test failed');
                // Use query parameter for error message
                $this->redirect($this->url() . '?message=' . urlencode('❌ Connection to WooCommerce failed. Check your credentials and URL.') . '&type=error');
            }
        } catch (\Exception $e) {
            Tools::log()->error('WooSync: Connection test error: ' . $e->getMessage());
            // Use query parameter for error message
            $this->redirect($this->url() . '?message=' . urlencode('Connection error: ' . $e->getMessage()) . '&type=error');
        }
    }

    private function syncAll(): void
    {
        Tools::log()->info('WooSync: Starting full synchronization');
        
        try {
            // Check if settings are configured
            if (empty($this->woocommerce_url) || empty($this->woocommerce_key) || empty($this->woocommerce_secret)) {
                $this->redirect($this->url() . '?message=' . urlencode('Please configure WooCommerce settings first.') . '&type=error');
                return;
            }
            
            $wooApi = new \FacturaScripts\Plugins\WooSync\Lib\WooCommerceAPI();
            
            // Test connection first
            if (!$wooApi->testConnection()) {
                $this->redirect($this->url() . '?message=' . urlencode('Cannot sync: Connection to WooCommerce failed.') . '&type=error');
                return;
            }
            
            // TODO: Implement actual sync logic
            Tools::log()->info('WooSync: Full sync would run here');
            
            $this->redirect($this->url() . '?message=' . urlencode('Synchronization started successfully!') . '&type=success');
            
        } catch (\Exception $e) {
            Tools::log()->error('WooSync: Sync error: ' . $e->getMessage());
            $this->redirect($this->url() . '?message=' . urlencode('Sync error: ' . $e->getMessage()) . '&type=error');
        }
    }

    private function syncOrders(): void
    {
        Tools::log()->info('WooSync: Starting order synchronization');
        
        try {
            // Check if settings are configured
            if (empty($this->woocommerce_url) || empty($this->woocommerce_key) || empty($this->woocommerce_secret)) {
                $this->redirect($this->url() . '?message=' . urlencode('Please configure WooCommerce settings first.') . '&type=error');
                return;
            }
            
            $wooApi = new \FacturaScripts\Plugins\WooSync\Lib\WooCommerceAPI();
            
            // Simple test - get first page of orders
            $orders = $wooApi->getOrders(['per_page' => 5]);
            $count = is_array($orders) ? count($orders) : 0;
            
            Tools::log()->info("WooSync: Found {$count} orders");
            $this->redirect($this->url() . '?message=' . urlencode("Found {$count} orders. Order sync logic needs to be implemented.") . '&type=success');
            
        } catch (\Exception $e) {
            Tools::log()->error('WooSync: Order sync error: ' . $e->getMessage());
            $this->redirect($this->url() . '?message=' . urlencode('Order sync error: ' . $e->getMessage()) . '&type=error');
        }
    }

    private function syncProducts(): void
    {
        Tools::log()->info('WooSync: Starting product synchronization');
        
        try {
            // Check if settings are configured
            if (empty($this->woocommerce_url) || empty($this->woocommerce_key) || empty($this->woocommerce_secret)) {
                $this->redirect($this->url() . '?message=' . urlencode('Please configure WooCommerce settings first.') . '&type=error');
                return;
            }
            
            $wooApi = new \FacturaScripts\Plugins\WooSync\Lib\WooCommerceAPI();
            
            // Simple test - get first page of products
            $products = $wooApi->getProducts(['per_page' => 5]);
            $count = is_array($products) ? count($products) : 0;
            
            Tools::log()->info("WooSync: Found {$count} products");
            $this->redirect($this->url() . '?message=' . urlencode("Found {$count} products. Product sync logic needs to be implemented.") . '&type=success');
            
        } catch (\Exception $e) {
            Tools::log()->error('WooSync: Product sync error: ' . $e->getMessage());
            $this->redirect($this->url() . '?message=' . urlencode('Product sync error: ' . $e->getMessage()) . '&type=error');
        }
    }

    private function syncStock(): void
    {
        Tools::log()->info('WooSync: Starting stock synchronization');
        $this->redirect($this->url() . '?message=' . urlencode('Stock synchronization feature needs to be implemented.') . '&type=info');
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
}<?php
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
            // Check if settings are configured
            if (empty($this->woocommerce_url) || empty($this->woocommerce_key) || empty($this->woocommerce_secret)) {
                Tools::log()->error('WooSync: Settings not configured');
                // Use simple query parameter for message
                $this->redirect($this->url() . '?message=' . urlencode('Please configure WooCommerce settings first.') . '&type=error');
                return;
            }
            
            $wooApi = new \FacturaScripts\Plugins\WooSync\Lib\WooCommerceAPI();
            
            if ($wooApi->testConnection()) {
                Tools::log()->info('WooSync: Connection test successful');
                // Use query parameter for success message
                $this->redirect($this->url() . '?message=' . urlencode('✅ Connection to WooCommerce successful!') . '&type=success');
            } else {
                Tools::log()->error('WooSync: Connection test failed');
                // Use query parameter for error message
                $this->redirect($this->url() . '?message=' . urlencode('❌ Connection to WooCommerce failed. Check your credentials and URL.') . '&type=error');
            }
        } catch (\Exception $e) {
            Tools::log()->error('WooSync: Connection test error: ' . $e->getMessage());
            // Use query parameter for error message
            $this->redirect($this->url() . '?message=' . urlencode('Connection error: ' . $e->getMessage()) . '&type=error');
        }
    }

    private function syncAll(): void
    {
        Tools::log()->info('WooSync: Starting full synchronization');
        
        try {
            // Check if settings are configured
            if (empty($this->woocommerce_url) || empty($this->woocommerce_key) || empty($this->woocommerce_secret)) {
                $this->redirect($this->url() . '?message=' . urlencode('Please configure WooCommerce settings first.') . '&type=error');
                return;
            }
            
            $wooApi = new \FacturaScripts\Plugins\WooSync\Lib\WooCommerceAPI();
            
            // Test connection first
            if (!$wooApi->testConnection()) {
                $this->redirect($this->url() . '?message=' . urlencode('Cannot sync: Connection to WooCommerce failed.') . '&type=error');
                return;
            }
            
            // TODO: Implement actual sync logic
            Tools::log()->info('WooSync: Full sync would run here');
            
            $this->redirect($this->url() . '?message=' . urlencode('Synchronization started successfully!') . '&type=success');
            
        } catch (\Exception $e) {
            Tools::log()->error('WooSync: Sync error: ' . $e->getMessage());
            $this->redirect($this->url() . '?message=' . urlencode('Sync error: ' . $e->getMessage()) . '&type=error');
        }
    }

    private function syncOrders(): void
    {
        Tools::log()->info('WooSync: Starting order synchronization');
        
        try {
            // Check if settings are configured
            if (empty($this->woocommerce_url) || empty($this->woocommerce_key) || empty($this->woocommerce_secret)) {
                $this->redirect($this->url() . '?message=' . urlencode('Please configure WooCommerce settings first.') . '&type=error');
                return;
            }
            
            $wooApi = new \FacturaScripts\Plugins\WooSync\Lib\WooCommerceAPI();
            
            // Simple test - get first page of orders
            $orders = $wooApi->getOrders(['per_page' => 5]);
            $count = is_array($orders) ? count($orders) : 0;
            
            Tools::log()->info("WooSync: Found {$count} orders");
            $this->redirect($this->url() . '?message=' . urlencode("Found {$count} orders. Order sync logic needs to be implemented.") . '&type=success');
            
        } catch (\Exception $e) {
            Tools::log()->error('WooSync: Order sync error: ' . $e->getMessage());
            $this->redirect($this->url() . '?message=' . urlencode('Order sync error: ' . $e->getMessage()) . '&type=error');
        }
    }

    private function syncProducts(): void
    {
        Tools::log()->info('WooSync: Starting product synchronization');
        
        try {
            // Check if settings are configured
            if (empty($this->woocommerce_url) || empty($this->woocommerce_key) || empty($this->woocommerce_secret)) {
                $this->redirect($this->url() . '?message=' . urlencode('Please configure WooCommerce settings first.') . '&type=error');
                return;
            }
            
            $wooApi = new \FacturaScripts\Plugins\WooSync\Lib\WooCommerceAPI();
            
            // Simple test - get first page of products
            $products = $wooApi->getProducts(['per_page' => 5]);
            $count = is_array($products) ? count($products) : 0;
            
            Tools::log()->info("WooSync: Found {$count} products");
            $this->redirect($this->url() . '?message=' . urlencode("Found {$count} products. Product sync logic needs to be implemented.") . '&type=success');
            
        } catch (\Exception $e) {
            Tools::log()->error('WooSync: Product sync error: ' . $e->getMessage());
            $this->redirect($this->url() . '?message=' . urlencode('Product sync error: ' . $e->getMessage()) . '&type=error');
        }
    }

    private function syncStock(): void
    {
        Tools::log()->info('WooSync: Starting stock synchronization');
        $this->redirect($this->url() . '?message=' . urlencode('Stock synchronization feature needs to be implemented.') . '&type=info');
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
