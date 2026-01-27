<?php
/**
 * WooSync Configuration Controller
 */

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
        
        $action = $this->request->get('action', '');
        
        if ($action === 'save') {
            $this->saveSettings();
        }
    }

    private function saveSettings(): void
    {
        $url = $this->request->get('woocommerce_url', '');
        $key = $this->request->get('woocommerce_key', '');
        $secret = $this->request->get('woocommerce_secret', '');
        
        Tools::settingsSet('WooSync', 'woocommerce_url', $url);
        Tools::settingsSet('WooSync', 'woocommerce_key', $key);
        Tools::settingsSet('WooSync', 'woocommerce_secret', $secret);
        
        Tools::log()->info('WooSync settings saved');
        $this->toolBox()->i18nLog()->info('settings-saved');
        
        // Redirect to avoid form resubmission
        $this->redirect($this->url());
    }

    // REMOVE the createViews() method or make it empty
    protected function createViews(): void
    {
        // Empty - we're not using ExtendedController views
    }
}
