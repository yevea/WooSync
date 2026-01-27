<?php
namespace FacturaScripts\Plugins\WooSync\Controller;

use FacturaScripts\Core\Base\Controller;
use FacturaScripts\Core\Base\ControllerPermissions;
use FacturaScripts\Core\Tools;
use Symfony\Component\HttpFoundation\Response;

class WooSyncConfig extends Controller
{
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
        
        // Check if this is a POST request with form submission
        if ($this->request->getMethod() === 'POST') {
            $action = $this->request->request->get('action', '');
            Tools::log()->debug('WooSyncConfig: POST action = ' . $action);
            
            if ($action === 'save') {
                $this->saveSettings();
                return; // Stop further processing after save
            }
        } else {
            // GET request - check for success parameter
            $saved = $this->request->query->get('saved', '');
            if ($saved === '1') {
                Tools::log()->debug('WooSyncConfig: Showing success message');
                // Success message will be shown in template
            }
        }
    }

    private function saveSettings(): void
    {
        Tools::log()->debug('WooSyncConfig: saveSettings called');
        
        // Get POST data
        $url = $this->request->request->get('woocommerce_url', '');
        $key = $this->request->request->get('woocommerce_key', '');
        $secret = $this->request->request->get('woocommerce_secret', '');
        
        Tools::log()->debug('WooSyncConfig: URL = ' . $url);
        Tools::log()->debug('WooSyncConfig: Key length = ' . strlen($key));
        Tools::log()->debug('WooSyncConfig: Secret length = ' . strlen($secret));
        
        // Validate
        if (empty($url) || empty($key) || empty($secret)) {
            Tools::log()->error('WooSyncConfig: Validation failed - empty fields');
            // Don't redirect - show error on same page
            return;
        }
        
        // Save settings
        Tools::settingsSet('WooSync', 'woocommerce_url', $url);
        Tools::settingsSet('WooSync', 'woocommerce_key', $key);
        Tools::settingsSet('WooSync', 'woocommerce_secret', $secret);
        
        Tools::log()->info('WooSync settings saved: ' . $url);
        
        // Redirect to show success message
        Tools::log()->debug('WooSyncConfig: Redirecting to success page');
        $this->redirect($this->url() . '?saved=1');
    }

    protected function createViews(): void
    {
        // Empty
    }
}
