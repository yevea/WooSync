<?php
/**
 * WooCommerce REST API Client
 * Handles all communication with WooCommerce REST API
 */
namespace FacturaScripts\Plugins\WooSync\Lib;

use FacturaScripts\Core\Tools;
use FacturaScripts\Plugins\WooSync\Model\WooSyncConfig;
use FacturaScripts\Plugins\WooSync\Model\WooSyncLog;

class WooCommerceAPI
{
    private $url;
    private $consumerKey;
    private $consumerSecret;
    private $timeout = 30;
    private $verifySSL = false;

    public function __construct()
    {
        $settings = WooSyncConfig::getWooCommerceSettings();
        $this->url = rtrim($settings['url'], '/');
        $this->consumerKey = $settings['consumer_key'];
        $this->consumerSecret = $settings['consumer_secret'];
        $this->verifySSL = $settings['verify_ssl'];
        $this->timeout = $settings['timeout'];
    }

    /**
     * Make a request to WooCommerce REST API
     */
    private function request(string $endpoint, array $params = [], string $method = 'GET'): array
    {
        if (empty($this->url) || empty($this->consumerKey) || empty($this->consumerSecret)) {
            throw new \RuntimeException('WooCommerce API credentials are missing. Please configure them first.');
        }

        // Build URL
        $url = $this->url . $endpoint;
        
        // Add auth as query params (OAuth 1.0a compatible)
        $auth = [
            'consumer_key' => $this->consumerKey,
            'consumer_secret' => $this->consumerSecret
        ];
        $qs = http_build_query(array_merge($auth, $params));
        $url = $url . '?' . $qs;

        WooSyncLog::logMessage("API Request: {$method} {$endpoint}", 'DEBUG', 'api');

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $this->verifySSL);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_USERAGENT, 'FacturaScripts-WooSync/2.0');
        
        // Set headers
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json',
            'Content-Type: application/json'
        ]);

        $response = curl_exec($ch);
        $errNo = curl_errno($ch);
        $err = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($errNo) {
            $errorMsg = "cURL error ({$errNo}): {$err}";
            WooSyncLog::logMessage($errorMsg, 'ERROR', 'api');
            throw new \RuntimeException($errorMsg);
        }

        if ($httpCode < 200 || $httpCode >= 300) {
            $errorMsg = "HTTP error {$httpCode}: " . substr($response, 0, 500);
            WooSyncLog::logMessage($errorMsg, 'ERROR', 'api');
            throw new \RuntimeException("HTTP error {$httpCode}");
        }

        $decoded = json_decode($response, true);
        if ($decoded === null && json_last_error() !== JSON_ERROR_NONE) {
            $errorMsg = 'Invalid JSON response: ' . json_last_error_msg();
            WooSyncLog::logMessage($errorMsg, 'ERROR', 'api');
            throw new \RuntimeException($errorMsg);
        }

        return (array)$decoded;
    }

    /**
     * Get products from WooCommerce
     */
    public function getProducts(array $params = []): array
    {
        return $this->request('/wp-json/wc/v3/products', $params, 'GET');
    }

    /**
     * Get a single product by ID
     */
    public function getProduct(int $id): array
    {
        return $this->request("/wp-json/wc/v3/products/{$id}", [], 'GET');
    }

    /**
     * Get customers from WooCommerce
     */
    public function getCustomers(array $params = []): array
    {
        return $this->request('/wp-json/wc/v3/customers', $params, 'GET');
    }

    /**
     * Get a single customer by ID
     */
    public function getCustomer(int $id): array
    {
        return $this->request("/wp-json/wc/v3/customers/{$id}", [], 'GET');
    }

    /**
     * Get orders from WooCommerce
     */
    public function getOrders(array $params = []): array
    {
        return $this->request('/wp-json/wc/v3/orders', $params, 'GET');
    }

    /**
     * Get a single order by ID
     */
    public function getOrder(int $id): array
    {
        return $this->request("/wp-json/wc/v3/orders/{$id}", [], 'GET');
    }

    /**
     * Get tax rates from WooCommerce
     */
    public function getTaxRates(array $params = []): array
    {
        return $this->request('/wp-json/wc/v3/taxes', $params, 'GET');
    }

    /**
     * Test connection to WooCommerce
     */
    public function testConnection(): bool
    {
        try {
            $result = $this->getProducts(['per_page' => 1]);
            WooSyncLog::logMessage('Connection test successful', 'INFO', 'api');
            return is_array($result);
        } catch (\Exception $e) {
            WooSyncLog::logMessage('Connection test failed: ' . $e->getMessage(), 'ERROR', 'api');
            return false;
        }
    }
}
