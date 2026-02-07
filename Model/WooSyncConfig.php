<?php
/**
 * WooSync Configuration Model
 * Stores WooCommerce API credentials and sync settings
 */
namespace FacturaScripts\Plugins\WooSync\Model;

use FacturaScripts\Core\Model\Base\ModelClass;
use FacturaScripts\Core\Model\Base\ModelTrait;

class WooSyncConfig extends ModelClass
{
    use ModelTrait;

    /** @var int */
    public $id;
    
    /** @var string */
    public $setting_key;
    
    /** @var string */
    public $setting_value;
    
    /** @var string */
    public $updated_at;

    public static function primaryColumn(): string
    {
        return 'id';
    }

    public static function tableName(): string
    {
        return 'woosync_settings';
    }

    public function install(): string
    {
        return 'CREATE TABLE IF NOT EXISTS ' . static::tableName() . ' (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            setting_key VARCHAR(255) NOT NULL UNIQUE,
            setting_value TEXT NULL,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_setting_key (setting_key)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;';
    }
    
    /**
     * Get a setting value by key
     */
    public static function getSetting(string $key, $default = null)
    {
        $model = new static();
        $where = [new \FacturaScripts\Core\Base\DataBase\DataBaseWhere('setting_key', $key)];
        
        if ($model->loadFromCode('', $where)) {
            return $model->setting_value;
        }
        
        return $default;
    }
    
    /**
     * Set a setting value by key
     */
    public static function setSetting(string $key, $value): bool
    {
        $model = new static();
        $where = [new \FacturaScripts\Core\Base\DataBase\DataBaseWhere('setting_key', $key)];
        
        if ($model->loadFromCode('', $where)) {
            // Update existing
            $model->setting_value = $value;
            $model->updated_at = date('Y-m-d H:i:s');
        } else {
            // Create new
            $model->setting_key = $key;
            $model->setting_value = $value;
            $model->updated_at = date('Y-m-d H:i:s');
        }
        
        return $model->save();
    }
    
    /**
     * Get all WooCommerce settings
     */
    public static function getWooCommerceSettings(): array
    {
        return [
            'url' => static::getSetting('woocommerce_url', ''),
            'consumer_key' => static::getSetting('woocommerce_key', ''),
            'consumer_secret' => static::getSetting('woocommerce_secret', ''),
            'verify_ssl' => static::getSetting('verify_ssl', 'false') === 'true',
            'timeout' => (int)static::getSetting('api_timeout', '30'),
        ];
    }
}
