<?php
namespace FacturaScripts\Plugins\WooSync\Model;

use FacturaScripts\Core\Base\DataBase\DataBaseWhere;
use FacturaScripts\Core\Model\Base\ModelClass;
use FacturaScripts\Core\Model\Base\ModelTrait;

class WooSyncConfig extends ModelClass
{
    use ModelTrait;

    public $id;
    public $wc_url;
    public $wc_key;
    public $wc_secret;

    public static function primaryColumn(): string
    {
        return 'id';
    }

    public static function tableName(): string
    {
        return 'woosync_config';
    }

    public function install(): string
    {
        return 'CREATE TABLE IF NOT EXISTS woosync_config (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            wc_url VARCHAR(255),
            wc_key VARCHAR(255),
            wc_secret VARCHAR(255)
        );';
    }
}
