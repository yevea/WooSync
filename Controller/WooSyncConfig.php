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
        
        // Debug: Log that we're in the controller
        Tools::log()->debug('WooSyncConfig: privateCore called');
        
        $action = $this->request->get('action', '');
        Tools::log()->debug('WooSyncConfig: action = ' . $action);
        
        if ($action === 'save') {
            Tools::log()->debug('WooSyncConfig: Calling saveSettings');
            $this->saveSettings();
        }
    }

    private function saveSettings(): void
    {
        Tools::log()->debug('WooSyncConfig: saveSettings called');
        
        // Get form data
        $url = $this->request->get('woocommerce_url', '');
        $key = $this->request->get('woocommerce_key', '');
        $secret = $this->request->get('woocommerce_secret', '');
        
        Tools::log()->debug('WooSyncConfig: URL = ' . $url);
        Tools::log()->debug('WooSyncConfig: Key = ' . (!empty($key) ? 'SET' : 'EMPTY'));
        Tools::log()->debug('WooSyncConfig: Secret = ' . (!empty($secret) ? 'SET' : 'EMPTY'));
        
        // Save settings
        Tools::settingsSet('WooSync', 'woocommerce_url', $url);
        Tools::settingsSet('WooSync', 'woocommerce_key', $key);
        Tools::settingsSet('WooSync', 'woocommerce_secret', $secret);
        
        Tools::log()->info('WooSync settings saved');
        
        // Debug: Verify settings were saved
        $savedUrl = Tools::settings('WooSync', 'woocommerce_url', 'NOT SAVED');
        Tools::log()->debug('WooSyncConfig: Verify URL saved = ' . $savedUrl);
        
        // Redirect with success parameter
        Tools::log()->debug('WooSyncConfig: Redirecting...');
        $this->redirect($this->url() . '?saved=1');
    }

    protected function createViews(): void
    {
        // Empty
    }
}
