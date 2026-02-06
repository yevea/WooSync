<?php
/**
 * Base Sync Service
 * Provides common functionality for all sync services
 */
namespace FacturaScripts\Plugins\WooSync\Lib;

use FacturaScripts\Plugins\WooSync\Model\WooSyncLog;

abstract class SyncService
{
    protected $wooApi;
    protected $syncedCount = 0;
    protected $errorCount = 0;
    protected $skippedCount = 0;

    public function __construct(WooCommerceAPI $wooApi)
    {
        $this->wooApi = $wooApi;
    }

    /**
     * Get sync results
     */
    public function getResults(): array
    {
        return [
            'synced' => $this->syncedCount,
            'errors' => $this->errorCount,
            'skipped' => $this->skippedCount,
            'total' => $this->syncedCount + $this->errorCount + $this->skippedCount
        ];
    }

    /**
     * Log sync message
     */
    protected function log(string $message, string $level = 'INFO', string $type = '', string $reference = ''): void
    {
        WooSyncLog::logMessage($message, $level, $type, $reference);
    }

    /**
     * Abstract method to be implemented by child classes
     */
    abstract public function sync(array $options = []): array;
}
