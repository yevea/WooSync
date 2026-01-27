<?php
/**
 * WooSyncLog model for tracking synchronization activities.
 */

namespace FacturaScripts\Plugins\WooSync\Model;

use FacturaScripts\Core\Model\Base\ModelClass;
use FacturaScripts\Core\Model\Base\ModelTrait;

class WooSyncLog extends ModelClass
{
    use ModelTrait;

    /** @var int */
    public $id;

    /** @var string */
    public $message;

    /** @var string */
    public $level;

    /** @var string */
    public $date;

    /** @var string */
    public $type;

    /** @var string */
    public $reference;

    public static function primaryColumn(): string
    {
        return 'id';
    }

    public static function tableName(): string
    {
        return 'woosync_logs';
    }

    public function save(): bool
    {
        if (empty($this->date)) {
            $this->date = date('Y-m-d H:i:s');
        }
        
        return parent::save();
    }

    public function clearOldLogs(): int
    {
        // Keep logs for 30 days
        $sql = "DELETE FROM " . static::tableName() 
             . " WHERE date < '" . date('Y-m-d H:i:s', strtotime('-30 days')) . "'";
        
        return $this->dataBase->exec($sql);
    }

    public static function logMessage(string $message, string $level = 'INFO', string $type = '', string $reference = ''): void
    {
        $log = new static();
        $log->message = $message;
        $log->level = $level;
        $log->type = $type;
        $log->reference = $reference;
        $log->save();
    }
}
