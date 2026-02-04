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
        $this->url = rtrim(Tools::settings('WooSync', 'woocommerce_url', ''), '/');
        $this->consumerKey = Tools::settings('WooSync', 'woocommerce_key', '');
        $this->consumerSecret = Tools::settings('WooSync', 'woocommerce_secret', '');
        $this->verifySSL = Tools::settings('WooSync', 'verify_ssl', false);
    }

    private function request(string $endpoint, array $params = [], string $method = 'GET'): array
    {
        if (empty($this->url) || empty($this->consumerKey) || empty($this->consumerSecret)) {
            throw new \RuntimeException('WooCommerce API credentials are missing.');
        }

        // Build URL
        $url = $this->url . $endpoint;
        // Add auth as query params (works for many WooCommerce configurations)
        $auth = [
            'consumer_key' => $this->consumerKey,
            'consumer_secret' => $this->consumerSecret
        ];
        $qs = http_build_query(array_merge($auth, $params));
        $url = $url . '?' . $qs;

        Tools::log()->debug("WooCommerceAPI request: {$method} {$url}");

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, (bool)$this->verifySSL);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

        // Some hosts require a user agent
        curl_setopt($ch, CURLOPT_USERAGENT, 'FacturaScripts-WooSync/1.0');

        $response = curl_exec($ch);
        $errNo = curl_errno($ch);
        $err = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($errNo) {
            Tools::log()->error("WooCommerce API cURL error ({$errNo}): {$err}");
            throw new \RuntimeException("cURL error: {$err}");
        }

        if ($httpCode < 200 || $httpCode >= 300) {
            Tools::log()->error("WooCommerce API HTTP {$httpCode}: {$response}");
            throw new \RuntimeException("HTTP error {$httpCode}");
        }

        $decoded = json_decode($response, true);
        if ($decoded === null && json_last_error() !== JSON_ERROR_NONE) {
            Tools::log()->error('WooCommerce API JSON decode error: ' . json_last_error_msg());
            throw new \RuntimeException('Invalid JSON response from WooCommerce');
        }

        return (array)$decoded;
    }

    public function getProducts(array $params = []): array
    {
        // Endpoint root: /wp-json/wc/v3/products
        return $this->request('/wp-json/wc/v3/products', $params, 'GET');
    }

    public function getOrders(array $params = []): array
    {
        // Endpoint root: /wp-json/wc/v3/orders
        return $this->request('/wp-json/wc/v3/orders', $params, 'GET');
    }

    public function testConnection(): bool
    {
        try {
            $result = $this->getProducts(['per_page' => 1]);
            return is_array($result);
        } catch (\Exception $e) {
            Tools::log()->error('WooCommerce API Test Error: ' . $e->getMessage());
            return false;
        }
    }
}
