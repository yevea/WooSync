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
        
        // Load saved settings
        $this->woocommerce_url = Tools::settings('WooSync', 'woocommerce_url', '');
        $this->woocommerce_key = Tools::settings('WooSync', 'woocommerce_key', '');
        $this->woocommerce_secret = Tools::settings('WooSync', 'woocommerce_secret', '');
        
        Tools::log()->debug('WooSyncConfig: Loaded URL = ' . $this->woocommerce_url);
        Tools::log()->debug('WooSyncConfig: Loaded Key length = ' . strlen($this->woocommerce_key));
        
        // Process form submission
        if ($this->request->request->get('action') === 'save') {
            $this->saveSettings();
        }
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
        
        // Update instance variables for immediate display
        $this->woocommerce_url = $url;
        $this->woocommerce_key = $key;
        $this->woocommerce_secret = $secret;
        
        return true;
    }

    protected function execAfterAction(string $action): void
    {
        Tools::log()->debug('WooSyncConfig: execAfterAction called with action: ' . $action);
        
        if ($action === 'save') {
            // Redirect after successful save
            $this->redirect($this->url() . '?saved=1&refresh=' . time());
        }
    }

    protected function createViews(): void
    {
        // Empty
    }
}
