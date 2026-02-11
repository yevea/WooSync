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
    
    public function install(): string
    {
        return 'CREATE TABLE IF NOT EXISTS ' . static::tableName() . ' (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            message TEXT NOT NULL,
            level VARCHAR(10) NOT NULL DEFAULT "INFO",
            date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            type VARCHAR(50) NULL,
            reference VARCHAR(100) NULL,
            INDEX idx_date (date),
            INDEX idx_type (type),
            INDEX idx_level (level)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;';
    }

    public function save(): bool
    {
        if (empty($this->date)) {
            $this->date = date('Y-m-d H:i:s');
        }
        
        // Ensure level is uppercase
        if (!empty($this->level)) {
            $this->level = strtoupper($this->level);
        }
        
        return parent::save();
    }

    public function clearOldLogs(int $days = 30): int
    {
        // Keep logs for specified days
        $sql = "DELETE FROM " . static::tableName() 
             . " WHERE date < '" . date('Y-m-d H:i:s', strtotime("-{$days} days")) . "'";
        
        return $this->dataBase->exec($sql);
    }

    public static function logMessage(string $message, string $level = 'INFO', string $type = '', string $reference = ''): void
    {
        try {
            $log = new static();
            $log->message = substr($message, 0, 5000); // Limit message length
            $log->level = strtoupper($level);
            $log->type = substr($type, 0, 50);
            $log->reference = substr($reference, 0, 100);
            $log->save();
        } catch (\Exception $e) {
            // Fail silently to not break the sync process
            error_log('WooSync: Failed to log message: ' . $e->getMessage());
        }
    }
}
