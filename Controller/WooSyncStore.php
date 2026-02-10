<?php
namespace FacturaScripts\Plugins\WooSync\Controller;

use FacturaScripts\Core\Base\Controller;
use FacturaScripts\Core\Base\DataBase;
use FacturaScripts\Core\DataSrc\Divisas;
use FacturaScripts\Core\Tools;

class WooSyncStore extends Controller
{
    private const PRODUCT_TABLE = 'productos';
    private const PRODUCT_LIMIT = 24;
    private const LIKE_ESCAPE = '!';

    public $products = [];
    public $search = '';
    public $store_error = '';
    public $decimal_separator;
    public $thousands_separator;
    public $currency_symbol;

    public function getPageData(): array
    {
        $pageData = parent::getPageData();
        $pageData['title'] = 'woosync-store-title';
        $pageData['menu'] = 'public';
        $pageData['icon'] = 'fas fa-store';
        $pageData['showonmenu'] = false;
        return $pageData;
    }

    public function publicCore(&$response): void
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
        $currency = Divisas::default();
        if ($currency && !empty($currency->simbolo)) {
            $this->currency_symbol = $currency->simbolo;
        } elseif ($currency && !empty($currency->coddivisa)) {
            $this->currency_symbol = $currency->coddivisa;
        } else {
            $this->currency_symbol = 'EUR';
        }

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
                $reference = (string)($row['referencia'] ?? '');
                $description = trim((string)($row['descripcion'] ?? ''));
                $displayName = $description !== '' ? $description : $reference;
                $showReference = $description !== '' && $description !== $reference;
                $price = $row['pvp'] ?? null;
                $priceValue = ($price === null || $price === '') ? null : (float)$price;
                $formattedPrice = $priceValue === null
                    ? null
                    : number_format($priceValue, 2, $this->decimal_separator, $this->thousands_separator)
                        . ' ' . $this->currency_symbol;

                $this->products[] = [
                    'referencia' => $reference,
                    'descripcion' => $description,
                    'display_name' => $displayName,
                    'show_reference' => $showReference,
                    'pvp' => $priceValue,
                    'formatted_price' => $formattedPrice,
                ];
            }
        } catch (\Exception $e) {
            Tools::log()->error('WooSyncStore: Error loading products: ' . $e->getMessage());
            $this->store_error = Tools::lang()->trans('woosync-store-error');
        }
    }

}
