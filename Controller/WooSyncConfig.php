<?php

namespace FacturaScripts\Plugins\WooSync\Controller;

class WooSyncConfig extends \FacturaScripts\Core\Base\Controller
{
    public function getPageData(): array
    {
        $data = parent::getPageData();
        $data['title'] = 'WooSync';
        $data['menu']  = 'tools';
        $data['icon']  = 'fas fa-sync';
        return $data;
    }

    protected function exec()
    {
        if ($this->request->method() === 'POST') {
            \FacturaScripts\Core\Tools::settingsSet('woosync', 'url', $this->request->post('url'));
            \FacturaScripts\Core\Tools::settingsSet('woosync', 'ck', $this->request->post('ck'));
            \FacturaScripts\Core\Tools::settingsSet('woosync', 'cs', $this->request->post('cs'));
            $this->toolBox()->i18nLog()->notice('Configuración guardada');
        }
    }
}
