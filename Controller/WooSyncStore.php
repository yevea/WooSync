<?php
namespace FacturaScripts\Plugins\WooSync\Controller;

use FacturaScripts\Core\Base\Controller;
use FacturaScripts\Core\Base\DataBase;
use FacturaScripts\Core\Tools;

class WooSyncStore extends Controller
{
    private const PRODUCT_TABLE = 'productos';
    private const PRODUCT_LIMIT = 24;
    private const LIKE_ESCAPE = '!';

    public $products = [];
    public $search = '';
    public $store_error = '';
    public $decimal_separator = ',';
    public $thousands_separator = '.';

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
        $this->decimal_separator = Tools::config('nf1', ',');
        $this->thousands_separator = Tools::config('nf2', '.');

        try {
            $db = new DataBase();
            $conditions = [];

            if ($this->search !== '') {
                $searchLiteral = str_replace(
                    [self::LIKE_ESCAPE, '%', '_'],
                    [self::LIKE_ESCAPE . self::LIKE_ESCAPE, self::LIKE_ESCAPE . '%', self::LIKE_ESCAPE . '_'],
                    $this->search
                );
                $searchEscaped = $db->var2str('%' . $searchLiteral . '%');
                $conditions[] = "(descripcion LIKE {$searchEscaped} ESCAPE '" . self::LIKE_ESCAPE
                    . "' OR referencia LIKE {$searchEscaped} ESCAPE '" . self::LIKE_ESCAPE . "')";
            }

            $whereSql = empty($conditions) ? '' : ' WHERE ' . implode(' AND ', $conditions);
            $sql = 'SELECT referencia, descripcion, pvp FROM ' . self::PRODUCT_TABLE . $whereSql
                . ' ORDER BY descripcion ASC';
            $rows = $db->selectLimit($sql, self::PRODUCT_LIMIT);

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
