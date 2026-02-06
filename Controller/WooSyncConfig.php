<?php
namespace FacturaScripts\Plugins\WooSync\Controller;

use FacturaScripts\Core\Base\Controller;
use FacturaScripts\Core\Base\ControllerPermissions;
use FacturaScripts\Core\Tools;
use Symfony\Component\HttpFoundation\Response;

class WooSyncConfig extends Controller
{
    public $woocommerce_url = '';
    public $woocommerce_key = '';
    public $woocommerce_secret = '';
    public $last_error = '';
    public $last_success = '';
    public $debug_messages = [];

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

        // Always load settings first
        $this->loadSettings();

        // Check for messages in URL (from redirects)
        if ($this->request->query->has('saved')) {
            $this->last_success = 'Settings saved successfully!';
        }
        if ($this->request->query->has('error')) {
            $this->last_error = $this->request->query->get('error', '');
        }
        if ($this->request->query->has('success')) {
            $this->last_success = $this->request->query->get('success', '');
        }

        // Process POST actions (form submission)
        if ($this->request->getMethod() === 'POST') {
            $action = $this->request->request->get('action', '');
            if ($action === 'save') {
                $this->saveSettings();
                $this->redirect($this->url() . '?saved=1');
                return;
            }

            // Other POST actions can be handled here if needed
            switch ($action) {
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

        // Process GET actions (test, sync buttons)
        $action = $this->request->get('action', '');
        if (!empty($action) && $action !== 'save') {
            $this->processAction($action);
        }
    }

    private function loadSettings(): void
    {
        $this->woocommerce_url = Tools::settings('WooSync', 'woocommerce_url', '');
        $this->woocommerce_key = Tools::settings('WooSync', 'woocommerce_key', '');
        $this->woocommerce_secret = Tools::settings('WooSync', 'woocommerce_secret', '');

        Tools::log()->debug('WooSync: Loaded settings - URL: ' . $this->woocommerce_url);
    }

    private function processAction(string $action): void
    {
        switch ($action) {
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
            default:
                // Unknown action
                Tools::log()->warning('WooSync: Unknown action requested: ' . $action);
                break;
        }
    }

    private function saveSettings(): bool
    {
        $url = $this->request->request->get('woocommerce_url', '');
        $key = $this->request->request->get('woocommerce_key', '');
        $secret = $this->request->request->get('woocommerce_secret', '');

        $this->debug_messages[] = "URL received: " . substr($url, 0, 30) . "...";
        $this->debug_messages[] = "Key length: " . strlen($key);
        $this->debug_messages[] = "Secret length: " . strlen($secret);

        if (empty($url) || empty($key) || empty($secret)) {
            Tools::log()->error('WooSync: Validation failed - empty fields');
            $this->debug_messages[] = "ERROR: One or more fields are empty!";
            $this->last_error = 'Please fill in all required fields (URL, Key, Secret).';
            return false;
        }

        // Validate URL format
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            Tools::log()->error('WooSync: Invalid URL format: ' . $url);
            $this->debug_messages[] = "ERROR: Invalid URL format!";
            $this->last_error = 'Invalid URL format. Use https://...';
            return false;
        }

        try {
            Tools::settingsSet('WooSync', 'woocommerce_url', $url);
            Tools::settingsSet('WooSync', 'woocommerce_key', $key);
            Tools::settingsSet('WooSync', 'woocommerce_secret', $secret);

            Tools::log()->info('WooSync settings saved: ' . $url);
            $this->debug_messages[] = "Settings saved to database";

            // Update current values
            $this->woocommerce_url = $url;
            $this->woocommerce_key = $key;
            $this->woocommerce_secret = $secret;

            $this->last_success = 'Settings saved successfully!';
            return true;
        } catch (\Exception $e) {
            Tools::log()->error('WooSync: Error saving settings: ' . $e->getMessage());
            $this->debug_messages[] = "Database error: " . $e->getMessage();
            $this->last_error = 'Error saving settings: ' . $e->getMessage();
            return false;
        }
    }

    private function testConnection(): void
    {
        Tools::log()->info('WooSync: Testing connection...');

        if (empty($this->woocommerce_url) || empty($this->woocommerce_key) || empty($this->woocommerce_secret)) {
            $this->redirect($this->url() . '?error=' . urlencode('Please configure WooCommerce settings first.'));
            return;
        }

        try {
            $wooApi = new \FacturaScripts\Plugins\WooSync\Lib\WooCommerceAPI();

            if ($wooApi->testConnection()) {
                $this->redirect($this->url() . '?success=' . urlencode('✅ Connection to WooCommerce successful!'));
            } else {
                $this->redirect($this->url() . '?error=' . urlencode('❌ Connection to WooCommerce failed. Check your credentials.'));
            }
        } catch (\Exception $e) {
            Tools::log()->error('WooSync: Connection test error: ' . $e->getMessage());
            $this->redirect($this->url() . '?error=' . urlencode('Connection error: ' . $e->getMessage()));
        }
    }

    private function syncAll(): void
    {
        Tools::log()->info('WooSync: Starting full synchronization');

        if (empty($this->woocommerce_url) || empty($this->woocommerce_key) || empty($this->woocommerce_secret)) {
            $this->redirect($this->url() . '?error=' . urlencode('Please configure WooCommerce settings first.'));
            return;
        }

        try {
            $wooApi = new \FacturaScripts\Plugins\WooSync\Lib\WooCommerceAPI();

            if (!$wooApi->testConnection()) {
                $this->redirect($this->url() . '?error=' . urlencode('Cannot sync: Connection failed.'));
                return;
            }

            $this->redirect($this->url() . '?success=' . urlencode('Synchronization started successfully!'));
        } catch (\Exception $e) {
            Tools::log()->error('WooSync: Sync all error: ' . $e->getMessage());
            $this->redirect($this->url() . '?error=' . urlencode('Sync error: ' . $e->getMessage()));
        }
    }

    private function syncOrders(): void
    {
        Tools::log()->info('WooSync: Starting order synchronization');

        if (empty($this->woocommerce_url) || empty($this->woocommerce_key) || empty($this->woocommerce_secret)) {
            $this->redirect($this->url() . '?error=' . urlencode('Please configure WooCommerce settings first.'));
            return;
        }

        try {
            $wooApi = new \FacturaScripts\Plugins\WooSync\Lib\WooCommerceAPI();

            $orders = $wooApi->getOrders(['per_page' => 5]);
            $count = is_array($orders) ? count($orders) : 0;

            Tools::log()->info("WooSync: Found {$count} orders");

            // TODO: implement order import into FacturaScripts
            $this->redirect($this->url() . '?success=' . urlencode("Found {$count} orders. Order sync needs implementation."));
        } catch (\Exception $e) {
            Tools::log()->error('WooSync: Order sync error: ' . $e->getMessage());
            $this->redirect($this->url() . '?error=' . urlencode('Order sync error: ' . $e->getMessage()));
        }
    }

    private function syncProducts(): void
    {
        Tools::log()->info('WooSync: Starting product synchronization');

        if (empty($this->woocommerce_url) || empty($this->woocommerce_key) || empty($this->woocommerce_secret)) {
            $this->redirect($this->url() . '?error=' . urlencode('Please configure WooCommerce settings first.'));
            return;
        }

        try {
            $wooApi = new \FacturaScripts\Plugins\WooSync\Lib\WooCommerceAPI();

            $products = $wooApi->getProducts(['per_page' => 50]);
            $count = is_array($products) ? count($products) : 0;

            Tools::log()->info("WooSync: Found {$count} products");

            // Save a log entry per product to verify the content & mapping
            foreach ($products as $p) {
                try {
                    $log = new \FacturaScripts\Plugins\WooSync\Model\WooSyncLog();
                    $log->message = substr(json_encode([
                        'id'    => $p['id'] ?? null,
                        'sku'   => $p['sku'] ?? null,
                        'name'  => $p['name'] ?? null,
                        'price' => $p['price'] ?? null
                    ], JSON_UNESCAPED_UNICODE), 0, 2000);
                    $log->level = 'info';
                    $log->type = 'product';
                    $log->reference = (string)($p['id'] ?? '');
                    $log->save();
                } catch (\Exception $inner) {
                    Tools::log()->error('WooSync: Failed to log product: ' . $inner->getMessage());
                }
            }

            $this->redirect($this->url() . '?success=' . urlencode("Found {$count} products. Logged {$count} items (proof of concept)."));
        } catch (\Exception $e) {
            Tools::log()->error('WooSync: Product sync error: ' . $e->getMessage());
            $this->redirect($this->url() . '?error=' . urlencode('Product sync error: ' . $e->getMessage()));
        }
    }

    private function syncStock(): void
    {
        Tools::log()->info('WooSync: Starting stock synchronization');
        $this->redirect($this->url() . '?info=' . urlencode('Stock sync needs implementation.'));
    }

    protected function createViews(): void
    {
        // No custom views to create
    }
}
