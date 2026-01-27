<?php
namespace FacturaScripts\Plugins\WooSync\Lib;

use FacturaScripts\Core\Tools;

class WooCommerceAPI
{
    private $url;
    private $consumerKey;
    private $consumerSecret;
    private $timeout = 30;
    private $verifySSL = false;

    public function __construct()
    {
        $this->url = Tools::settings('WooSync', 'woocommerce_url');
        $this->consumerKey = Tools::settings('WooSync', 'woocommerce_key');
        $this->consumerSecret = Tools::settings('WooSync', 'woocommerce_secret');
        $this->verifySSL = Tools::settings('WooSync', 'verify_ssl', false);
    }

    public function getOrders(array $params = []): array
    {
        return $this->request('GET', '/wp-json/wc/v3/orders', $params);
    }

    public function getProducts(array $params = []): array
    {
        return $this->request('GET', '/wp-json/wc/v3/products', $params);
    }

    public function updateProductStock($productId, $stockQuantity): array
    {
        $data = [
            'stock_quantity' => $stockQuantity,
            'manage_stock' => true
        ];
        
        return $this->request('PUT', "/wp-json/wc/v3/products/{$productId}", $data);
    }

    private function request(string $method, string $endpoint, array $params = []): array
    {
        $url = rtrim($this->url, '/') . $endpoint;
        
        // Add authentication
        $params['consumer_key'] = $this->consumerKey;
        $params['consumer_secret'] = $this->consumerSecret;
        
        $ch = curl_init();
        
        if ($method === 'GET') {
            $url .= '?' . http_build_query($params);
        } else {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Content-Length: ' . strlen(json_encode($params))
            ]);
        }
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_SSL_VERIFYPEER => $this->verifySSL,
            CURLOPT_SSL_VERIFYHOST => $this->verifySSL ? 2 : 0,
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            throw new \Exception('cURL Error: ' . $error);
        }
        
        $data = json_decode($response, true);
        
        if ($httpCode >= 400) {
            $message = $data['message'] ?? 'Unknown error';
            throw new \Exception("API Error {$httpCode}: {$message}");
        }
        
        return $data ?? [];
    }

   public function testConnection(): bool
{
    try {
        $result = $this->getProducts(['per_page' => 1]);
        return is_array($result) && (isset($result[0]) || empty($result));
    } catch (\Exception $e) {
        Tools::log()->error('WooCommerce API Test Error: ' . $e->getMessage());
        return false;
    }
}

public function getOrders(array $params = []): array
{
    return $this->request('GET', '/wp-json/wc/v3/orders', $params);
}

public function getProducts(array $params = []): array
{
    return $this->request('GET', '/wp-json/wc/v3/products', $params);
}
}
