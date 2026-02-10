<?php
namespace FacturaScripts\Plugins\WooSync\Controller;

use FacturaScripts\Core\Base\Controller;
use FacturaScripts\Core\Base\DataBase;
use FacturaScripts\Core\Tools;

class WooSyncStore extends Controller
{
    private const PRODUCT_LIMIT = 24;

    public $products = [];
    public $search = '';
    public $store_error = '';

    public function getPageData(): array
    {
        $pageData = parent::getPageData();
        $pageData['title'] = 'WooSync Store';
        $pageData['menu'] = 'public';
        $pageData['icon'] = 'fas fa-store';
        $pageData['showonmenu'] = false;
        return $pageData;
    }

    public function publicCore(&$response)
    {
        parent::publicCore($response);
        $this->setTemplate('WooSyncStore');
        $this->loadProducts();
    }

    public function privateCore(&$response, $user, $permissions): void
    {
        parent::privateCore($response, $user, $permissions);
        $this->setTemplate('WooSyncStore');
        $this->loadProducts();
    }

    private function loadProducts(): void
    {
        $this->search = trim($this->request->query->get('q', ''));
        $this->products = [];

        try {
            $db = new DataBase();
            $conditions = [];

            if ($this->search !== '') {
                $searchLiteral = addcslashes($this->search, "\\%_");
                $searchEscaped = $db->var2str('%' . $searchLiteral . '%');
                $conditions[] = "(descripcion LIKE {$searchEscaped} ESCAPE '\\\\' OR referencia LIKE {$searchEscaped} ESCAPE '\\\\')";
            }

            $whereSql = empty($conditions) ? '' : ' WHERE ' . implode(' AND ', $conditions);
            $sql = 'SELECT referencia, descripcion, pvp FROM productos' . $whereSql
                . ' ORDER BY descripcion ASC LIMIT ' . self::PRODUCT_LIMIT;
            $rows = $db->select($sql);

            foreach ($rows as $row) {
                $this->products[] = [
                    'referencia' => $row['referencia'] ?? '',
                    'descripcion' => $row['descripcion'] ?? '',
                    'pvp' => isset($row['pvp']) ? (float)$row['pvp'] : 0.0,
                ];
            }
        } catch (\Exception $e) {
            Tools::log()->error('WooSyncStore: Error loading products: ' . $e->getMessage());
            $this->store_error = 'Unable to load products right now.';
        }
    }

    protected function createViews(): void
    {
        // No custom views to create
    }
}
