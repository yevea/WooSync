<?php
// Quick debug script to check settings storage
require_once __DIR__ . '/../../Core/Base/DataBase.php';

use FacturaScripts\Core\Base\DataBase;
use FacturaScripts\Core\Tools;

// Initialize database
$db = new DataBase();

// Try to read settings
echo "<h2>WooSync Debug Info</h2>";
echo "<pre>";

// Check if settings table exists
$sql = "SHOW TABLES LIKE 'settings'";
$result = $db->select($sql);
echo "Settings table exists: " . (count($result) > 0 ? "YES" : "NO") . "\n\n";

// Check what's in settings for WooSync
$sql = "SELECT * FROM settings WHERE name LIKE '%WooSync%' OR name LIKE '%woocommerce%'";
$result = $db->select($sql);
echo "Current WooSync settings in database:\n";
print_r($result);

// Also check the structure
$sql = "DESCRIBE settings";
$result = $db->select($sql);
echo "\nSettings table structure:\n";
print_r($result);

echo "</pre>";
?>
