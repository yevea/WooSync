<?php
/**
 * WooSync Configuration Controller
 * Admin UI for configuring and managing WooCommerce synchronization
 */
namespace FacturaScripts\Plugins\WooSync\Controller;

use FacturaScripts\Core\Base\Controller;
use FacturaScripts\Plugins\WooSync\Model\WooSyncConfig as WooSyncConfigModel;
use FacturaScripts\Plugins\WooSync\Lib\WooCommerceAPI;
use FacturaScripts\Plugins\WooSync\Lib\ProductSyncService;
use FacturaScripts\Plugins\WooSync\Lib\CustomerSyncService;
use FacturaScripts\Plugins\WooSync\Lib\OrderSyncService;
use FacturaScripts\Plugins\WooSync\Lib\StockSyncService;
use FacturaScripts\Plugins\WooSync\Lib\TaxSyncService;

class WooSyncConfig extends Controller
{
    public $woocommerce_url = '';
    public $woocommerce_key = '';
    public $woocommerce_secret = '';
    public $last_error = '';
    public $last_success = '';

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

        // Load settings
        $this->loadSettings();

        // Handle POST requests (save settings)
        if ($this->request->getMethod() === 'POST') {
            $action = $this->request->request->get('action', '');
            if ($action === 'save') {
                if ($this->saveSettings()) {
                    $this->redirect($this->url() . '?saved=1');
                    return;
                }
            }
        }

        // Handle GET actions (test, sync buttons)
        if ($this->request->query->has('saved')) {
            $this->last_success = 'Settings saved successfully!';
            $this->loadSettings(); // Reload to show saved values
        }

        if ($this->request->query->has('error')) {
            $this->last_error = $this->request->query->get('error', '');
        }

        if ($this->request->query->has('success')) {
            $this->last_success = $this->request->query->get('success', '');
        }

        $action = $this->request->get('action', '');
        if (!empty($action)) {
            $this->processAction($action);
        }
    }

    private function loadSettings(): void
    {
        $settings = WooSyncConfigModel::getWooCommerceSettings();
        $this->woocommerce_url = $settings['url'];
        $this->woocommerce_key = $settings['consumer_key'];
        $this->woocommerce_secret = $settings['consumer_secret'];
    }

    private function saveSettings(): bool
    {
        $url = trim($this->request->request->get('woocommerce_url', ''));
        $key = trim($this->request->request->get('woocommerce_key', ''));
        $secret = trim($this->request->request->get('woocommerce_secret', ''));

        // Validate inputs
        if (empty($url) || empty($key) || empty($secret)) {
            $this->last_error = 'Please fill in all required fields (URL, Key, Secret).';
            return false;
        }

        // Validate URL format
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            $this->last_error = 'Invalid URL format. Use https://yourstore.com';
            return false;
        }

        // Save settings
        try {
            WooSyncConfigModel::setSetting('woocommerce_url', $url);
            WooSyncConfigModel::setSetting('woocommerce_key', $key);
            WooSyncConfigModel::setSetting('woocommerce_secret', $secret);
            
            $this->last_success = 'Settings saved successfully!';
            return true;
            
        } catch (\Exception $e) {
            $this->last_error = 'Error saving settings: ' . $e->getMessage();
            return false;
        }
    }

    private function processAction(string $action): void
    {
        // Check if settings are configured
        if (empty($this->woocommerce_url) || empty($this->woocommerce_key) || empty($this->woocommerce_secret)) {
            $this->redirect($this->url() . '?error=' . urlencode('Please configure WooCommerce settings first.'));
            return;
        }

        try {
            switch ($action) {
                case 'test':
                    $this->testConnection();
                    break;
                case 'sync':
                    $this->syncAll();
                    break;
                case 'sync-products':
                    $this->syncProducts();
                    break;
                case 'sync-customers':
                    $this->syncCustomers();
                    break;
                case 'sync-orders':
                    $this->syncOrders();
                    break;
                case 'sync-stock':
                    $this->syncStock();
                    break;
                case 'sync-taxes':
                    $this->syncTaxes();
                    break;
                default:
                    $this->redirect($this->url() . '?error=' . urlencode('Unknown action: ' . $action));
                    break;
            }
        } catch (\Exception $e) {
            $this->redirect($this->url() . '?error=' . urlencode('Error: ' . $e->getMessage()));
        }
    }

    private function testConnection(): void
    {
        try {
            $wooApi = new WooCommerceAPI();
            
            if ($wooApi->testConnection()) {
                $this->redirect($this->url() . '?success=' . urlencode('✅ Connection to WooCommerce successful!'));
            } else {
                $this->redirect($this->url() . '?error=' . urlencode('❌ Connection failed. Check your credentials.'));
            }
        } catch (\Exception $e) {
            $this->redirect($this->url() . '?error=' . urlencode('Connection error: ' . $e->getMessage()));
        }
    }

    private function syncAll(): void
    {
        try {
            // Increase PHP execution time limit (works on some hosting)
            @set_time_limit(300); // 5 minutes
            @ini_set('max_execution_time', '300');
            
            $wooApi = new WooCommerceAPI();
            
            // Sync in order: taxes -> products -> customers -> orders -> stock
            // Using smaller batch sizes to reduce timeout risk
            $taxService = new TaxSyncService($wooApi);
            $taxResults = $taxService->sync(['per_page' => 20]);
            
            $productService = new ProductSyncService($wooApi);
            $productResults = $productService->sync(['per_page' => 20]);
            
            $customerService = new CustomerSyncService($wooApi);
            $customerResults = $customerService->sync(['per_page' => 20]);
            
            $orderService = new OrderSyncService($wooApi);
            $orderResults = $orderService->sync(['per_page' => 10]);
            
            $stockService = new StockSyncService($wooApi);
            $stockResults = $stockService->sync(['per_page' => 20]);
            
            $totalErrors = $taxResults['errors'] + $productResults['errors'] + 
                $customerResults['errors'] + $orderResults['errors'] + $stockResults['errors'];
            $totalSynced = $taxResults['synced'] + $productResults['synced'] + 
                $customerResults['synced'] + $orderResults['synced'] + $stockResults['synced'];
            
            $message = sprintf(
                'Full sync completed! Taxes: %d, Products: %d, Customers: %d, Orders: %d, Stock: %d',
                $taxResults['synced'],
                $productResults['synced'],
                $customerResults['synced'],
                $orderResults['synced'],
                $stockResults['synced']
            );
            
            if ($totalErrors > 0) {
                $message .= sprintf(' (Total errors: %d)', $totalErrors);
            }
            
            if ($totalErrors > 0 && $totalSynced === 0) {
                $this->redirect($this->url() . '?error=' . urlencode($message));
            } else {
                $this->redirect($this->url() . '?success=' . urlencode($message));
            }
            
        } catch (\Exception $e) {
            $this->redirect($this->url() . '?error=' . urlencode('Sync error: ' . $e->getMessage()));
        }
    }

    private function syncProducts(): void
    {
        try {
            // Increase PHP execution time limit
            @set_time_limit(180); // 3 minutes
            @ini_set('max_execution_time', '180');
            
            $wooApi = new WooCommerceAPI();
            $service = new ProductSyncService($wooApi);
            $results = $service->sync(['per_page' => 20]);
            
            $message = sprintf(
                'Product sync completed: %d synced, %d errors, %d skipped',
                $results['synced'], $results['errors'], $results['skipped']
            );
            
            if ($results['errors'] > 0 && $results['synced'] === 0) {
                $this->redirect($this->url() . '?error=' . urlencode($message));
            } else {
                $this->redirect($this->url() . '?success=' . urlencode($message));
            }
            
        } catch (\Exception $e) {
            $this->redirect($this->url() . '?error=' . urlencode('Product sync error: ' . $e->getMessage()));
        }
    }

    private function syncCustomers(): void
    {
        try {
            // Increase PHP execution time limit
            @set_time_limit(180); // 3 minutes
            @ini_set('max_execution_time', '180');
            
            $wooApi = new WooCommerceAPI();
            $service = new CustomerSyncService($wooApi);
            $results = $service->sync(['per_page' => 20]);
            
            $message = sprintf(
                'Customer sync completed: %d synced, %d errors, %d skipped',
                $results['synced'], $results['errors'], $results['skipped']
            );
            
            if ($results['errors'] > 0 && $results['synced'] === 0) {
                $this->redirect($this->url() . '?error=' . urlencode($message));
            } else {
                $this->redirect($this->url() . '?success=' . urlencode($message));
            }
            
        } catch (\Exception $e) {
            $this->redirect($this->url() . '?error=' . urlencode('Customer sync error: ' . $e->getMessage()));
        }
    }

    private function syncOrders(): void
    {
        try {
            // Increase PHP execution time limit
            @set_time_limit(180); // 3 minutes
            @ini_set('max_execution_time', '180');
            
            $wooApi = new WooCommerceAPI();
            $service = new OrderSyncService($wooApi);
            $results = $service->sync(['per_page' => 10]); // Orders are slower, use smaller batch
            
            $message = sprintf(
                'Order sync completed: %d synced, %d errors, %d skipped',
                $results['synced'], $results['errors'], $results['skipped']
            );
            
            if ($results['errors'] > 0 && $results['synced'] === 0) {
                $this->redirect($this->url() . '?error=' . urlencode($message));
            } else {
                $this->redirect($this->url() . '?success=' . urlencode($message));
            }
            
        } catch (\Exception $e) {
            $this->redirect($this->url() . '?error=' . urlencode('Order sync error: ' . $e->getMessage()));
        }
    }

    private function syncStock(): void
    {
        try {
            // Increase PHP execution time limit
            @set_time_limit(180); // 3 minutes
            @ini_set('max_execution_time', '180');
            
            $wooApi = new WooCommerceAPI();
            $service = new StockSyncService($wooApi);
            $results = $service->sync(['per_page' => 20]);
            
            $message = sprintf(
                'Stock sync completed: %d synced, %d errors, %d skipped',
                $results['synced'], $results['errors'], $results['skipped']
            );
            
            if ($results['errors'] > 0 && $results['synced'] === 0) {
                $this->redirect($this->url() . '?error=' . urlencode($message));
            } else {
                $this->redirect($this->url() . '?success=' . urlencode($message));
            }
            
        } catch (\Exception $e) {
            $this->redirect($this->url() . '?error=' . urlencode('Stock sync error: ' . $e->getMessage()));
        }
    }

    private function syncTaxes(): void
    {
        try {
            // Increase PHP execution time limit
            @set_time_limit(180); // 3 minutes
            @ini_set('max_execution_time', '180');
            
            $wooApi = new WooCommerceAPI();
            $service = new TaxSyncService($wooApi);
            $results = $service->sync(['per_page' => 20]);
            
            $message = sprintf(
                'Tax sync completed: %d synced, %d errors, %d skipped',
                $results['synced'], $results['errors'], $results['skipped']
            );
            
            if ($results['errors'] > 0 && $results['synced'] === 0) {
                $this->redirect($this->url() . '?error=' . urlencode($message));
            } else {
                $this->redirect($this->url() . '?success=' . urlencode($message));
            }
            
        } catch (\Exception $e) {
            $this->redirect($this->url() . '?error=' . urlencode('Tax sync error: ' . $e->getMessage()));
        }
    }

    protected function createViews(): void
    {
        // No custom views to create
    }
}
