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
        
        // Show success message (using i18n if needed, or simple message)
        $this->dataBase->close();
        Tools::flash()->success('Settings saved successfully');
        
        // Redirect to avoid form resubmission
        $this->redirect($this->url() . '?ok=1');
    }

    // Keep createViews() empty or remove it
    protected function createViews(): void
    {
        // Empty - we're not using ExtendedController views
    }
}
