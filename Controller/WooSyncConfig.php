<?php
namespace FacturaScripts\Plugins\WooSync\Controller;

use FacturaScripts\Core\Base\Controller;
use FacturaScripts\Core\Base\ControllerPermissions;
use FacturaScripts\Core\Tools;
use FacturaScripts\Core\Base\DataBase;
use FacturaScripts\Plugins\WooSync\Lib\OrderSyncService;
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

        // Ensure settings table exists
        $this->ensureSettingsTable();

        // Always load settings first
        $this->loadSettings();
        $this->debug_messages[] = "After initial load - URL: '" . $this->woocommerce_url . "'";

        // Check for messages in URL (from redirects)
        if ($this->request->query->has('saved')) {
            $this->last_success = 'Settings saved successfully!';
            $this->debug_messages[] = "Detected 'saved' query param - reloading settings";
            $this->loadSettings();
            $this->debug_messages[] = "After reload - URL: '" . $this->woocommerce_url . "'";
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
                $this->debug_messages[] = "Processing POST save action";
                $this->saveSettings();
                $this->loadSettings();
                $this->debug_messages[] = "After save and reload - URL: '" . $this->woocommerce_url . "'";
                $this->redirect($this->url() . '?saved=1');
                return;
            }
        }

        // Process GET actions (test, sync buttons)
        $action = $this->request->get('action', '');
        if (!empty($action)) {
            $this->processAction($action);
        }
    }

    private function ensureSettingsTable(): void
    {
        try {
            $db = new DataBase();
            
            // Check if table exists
            $sql = "SHOW TABLES LIKE 'woosync_settings'";
            $result = $db->select($sql);
            
            if (empty($result)) {
                // Table doesn't exist, create it
                $createTableSQL = 'CREATE TABLE IF NOT EXISTS `woosync_settings` (
                    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                    `key` VARCHAR(255) NOT NULL UNIQUE,
                    `value` LONGTEXT,
                    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                )';
                
                $db->exec($createTableSQL);
                Tools::log()->info('WooSync: Created woosync_settings table');
            }
        } catch (\Exception $e) {
            Tools::log()->error('WooSync: Error ensuring settings table: ' . $e->getMessage());
        }
    }

    private function loadSettings(): void
    {
        try {
            $db = new DataBase();
            
            // Simple select query
            $sql = 'SELECT `key`, `value` FROM `woosync_settings`';
            $rows = $db->select($sql);
            
            $this->debug_messages[] = "Database query returned " . count($rows) . " rows";
            
            if (!empty($rows)) {
                foreach ($rows as $row) {
                    $key = $row['key'] ?? '';
                    $value = $row['value'] ?? '';
                    
                    if ($key === 'woocommerce_url') {
                        $this->woocommerce_url = $value;
                        $this->debug_messages[] = "Loaded woocommerce_url: " . substr($value, 0, 30);
                    } elseif ($key === 'woocommerce_key') {
                        $this->woocommerce_key = $value;
                        $this->debug_messages[] = "Loaded woocommerce_key (length: " . strlen($value) . ")";
                    } elseif ($key === 'woocommerce_secret') {
                        $this->woocommerce_secret = $value;
                        $this->debug_messages[] = "Loaded woocommerce_secret (length: " . strlen($value) . ")";
                    }
                }
            } else {
                $this->debug_messages[] = "No settings found in database";
            }
            
            Tools::log()->debug('WooSync loadSettings - URL: ' . $this->woocommerce_url);
        } catch (\Exception $e) {
            Tools::log()->error('WooSync: Error loading settings: ' . $e->getMessage());
            $this->debug_messages[] = "Error loading settings: " . $e->getMessage();
        }
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
                Tools::log()->warning('WooSync: Unknown action requested: ' . $action);
                break;
        }
    }

    private function saveSettings(): bool
    {
        $url = $this->request->request->get('woocommerce_url', '');
        $key = $this->request->request->get('woocommerce_key', '');
        $secret = $this->request->request->get('woocommerce_secret', '');

        $this->debug_messages[] = "Received POST - URL: " . substr($url, 0, 30) . "...";
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
            $db = new DataBase();
            $this->ensureSettingsTable();
            
            $this->debug_messages[] = "Deleting old settings...";
            // Delete old settings
            $db->exec('DELETE FROM `woosync_settings`');
            
            // Escape values for SQL
            $urlEscaped = $db->escape($url);
            $keyEscaped = $db->escape($key);
            $secretEscaped = $db->escape($secret);
            
            $this->debug_messages[] = "Inserting woocommerce_url...";
            // Insert URL
            $sql1 = "INSERT INTO `woosync_settings` (`key`, `value`) VALUES ('woocommerce_url', {$urlEscaped})";
            $db->exec($sql1);
            
            $this->debug_messages[] = "Inserting woocommerce_key...";
            // Insert Key
            $sql2 = "INSERT INTO `woosync_settings` (`key`, `value`) VALUES ('woocommerce_key', {$keyEscaped})";
            $db->exec($sql2);
            
            $this->debug_messages[] = "Inserting woocommerce_secret...";
            // Insert Secret
            $sql3 = "INSERT INTO `woosync_settings` (`key`, `value`) VALUES ('woocommerce_secret', {$secretEscaped})";
            $db->exec($sql3);
            
            Tools::log()->info('WooSync settings saved: ' . $url);
            $this->debug_messages[] = "All settings saved successfully";

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

            // Sync orders using OrderSyncService
            $orderSync = new OrderSyncService();
            $orderSync->sync();

            $this->redirect($this->url() . '?success=' . urlencode('Synchronization completed successfully!'));
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

            foreach ($orders as $o) {
                try {
                    $log = new \FacturaScripts\Plugins\WooSync\Model\WooSyncLog();
                    $customerName = ($o['billing']['first_name'] ?? '') . ' ' . ($o['billing']['last_name'] ?? '');
                    $log->message = substr(json_encode([
                        'id'        => $o['id'] ?? null,
                        'order_num' => $o['number'] ?? null,
                        'status'    => $o['status'] ?? null,
                        'total'     => $o['total'] ?? null,
                        'customer'  => trim($customerName)
                    ], JSON_UNESCAPED_UNICODE), 0, 2000);
                    $log->level = 'info';
                    $log->type = 'order';
                    $log->reference = (string)($o['id'] ?? '');
                    $log->save();
                } catch (\Exception $inner) {
                    Tools::log()->error('WooSync: Failed to log order: ' . $inner->getMessage());
                }
            }

            $this->redirect($this->url() . '?success=' . urlencode("Found {$count} orders. Logged {$count} items."));
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

            $this->redirect($this->url() . '?success=' . urlencode("Found {$count} products. Logged {$count} items."));
        } catch (\Exception $e) {
            Tools::log()->error('WooSync: Product sync error: ' . $e->getMessage());
            $this->redirect($this->url() . '?error=' . urlencode('Product sync error: ' . $e->getMessage()));
        }
    }

    private function syncStock(): void
    {
        Tools::log()->info('WooSync: Starting stock synchronization');
        $this->redirect($this->url() . '?success=' . urlencode('Stock sync needs implementation.'));
    }

    protected function createViews(): void
    {
        // No custom views to create
    }
}
