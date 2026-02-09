<?php
/**
 * WooSync Database Fix Script
 * 
 * Run this script to manually fix the woosync_settings table structure.
 * Upload to FacturaScripts root and access via browser.
 * 
 * URL: https://yourdomain.com/path-to-facturascripts/fix-database.php
 */

// Prevent direct access without confirmation
$confirm = isset($_GET['confirm']) && $_GET['confirm'] === 'yes';
$action = isset($_GET['action']) ? $_GET['action'] : '';

?>
<!DOCTYPE html>
<html>
<head>
    <title>WooSync Database Fix</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            border-bottom: 3px solid #007bff;
            padding-bottom: 10px;
        }
        .error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .info {
            background: #d1ecf1;
            border: 1px solid #bee5eb;
            color: #0c5460;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .warning {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            margin: 10px 5px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            font-size: 16px;
        }
        .btn-danger {
            background: #dc3545;
        }
        .btn-success {
            background: #28a745;
        }
        .btn:hover {
            opacity: 0.9;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        table th, table td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        table th {
            background: #007bff;
            color: white;
        }
        code {
            background: #f4f4f4;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: monospace;
        }
        pre {
            background: #f4f4f4;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 WooSync Database Fix Tool</h1>
        
        <?php
        // Try to load FacturaScripts config
        $configFile = __DIR__ . '/config.php';
        if (!file_exists($configFile)) {
            echo '<div class="error">';
            echo '<strong>Error:</strong> Cannot find FacturaScripts config.php<br>';
            echo 'Please upload this file to your FacturaScripts root directory.<br>';
            echo 'Current directory: ' . __DIR__;
            echo '</div>';
            exit;
        }
        
        // Load FacturaScripts configuration
        define('FS_FOLDER', __DIR__);
        require_once $configFile;
        
        // Connect to database
        try {
            $dsn = 'mysql:host=' . FS_DB_HOST . ';dbname=' . FS_DB_NAME . ';charset=utf8mb4';
            $pdo = new PDO($dsn, FS_DB_USER, FS_DB_PASS);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            echo '<div class="error">';
            echo '<strong>Database Connection Failed:</strong> ' . htmlspecialchars($e->getMessage());
            echo '</div>';
            exit;
        }
        
        $tableName = 'woosync_settings';
        
        // Check if action is requested
        if ($action === 'fix' && $confirm) {
            echo '<div class="warning">';
            echo '<strong>⚠️ Fixing Database Table...</strong>';
            echo '</div>';
            
            try {
                // Drop old table
                $pdo->exec("DROP TABLE IF EXISTS {$tableName}");
                echo '<div class="info">✓ Old table dropped</div>';
                
                // Create new table with correct structure
                $createSQL = "CREATE TABLE {$tableName} (
                    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                    setting_key VARCHAR(255) NOT NULL UNIQUE,
                    setting_value TEXT NULL,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX idx_setting_key (setting_key)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
                
                $pdo->exec($createSQL);
                echo '<div class="info">✓ New table created with correct structure</div>';
                
                echo '<div class="success">';
                echo '<strong>✅ SUCCESS!</strong><br><br>';
                echo 'The database table has been fixed!<br><br>';
                echo '<strong>Next Steps:</strong><br>';
                echo '1. Delete this fix-database.php file from your server<br>';
                echo '2. Go to FacturaScripts AdminPlugins<br>';
                echo '3. Access WooSync Configuration<br>';
                echo '4. Enter your WooCommerce API credentials<br>';
                echo '5. Start syncing!<br><br>';
                echo '<a href="AdminPlugins" class="btn btn-success">Go to AdminPlugins</a>';
                echo '</div>';
                
                // Show new structure
                echo '<h2>New Table Structure:</h2>';
                $stmt = $pdo->query("DESCRIBE {$tableName}");
                $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                echo '<table>';
                echo '<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>';
                foreach ($columns as $col) {
                    echo '<tr>';
                    echo '<td>' . htmlspecialchars($col['Field']) . '</td>';
                    echo '<td>' . htmlspecialchars($col['Type']) . '</td>';
                    echo '<td>' . htmlspecialchars($col['Null']) . '</td>';
                    echo '<td>' . htmlspecialchars($col['Key']) . '</td>';
                    echo '<td>' . htmlspecialchars($col['Default'] ?? 'NULL') . '</td>';
                    echo '<td>' . htmlspecialchars($col['Extra']) . '</td>';
                    echo '</tr>';
                }
                echo '</table>';
                
            } catch (PDOException $e) {
                echo '<div class="error">';
                echo '<strong>Error:</strong> ' . htmlspecialchars($e->getMessage());
                echo '</div>';
            }
            
        } else {
            // Show current status
            echo '<div class="info">';
            echo '<strong>This tool will fix the "Unknown column \'setting_key\'" error.</strong><br><br>';
            echo 'It will:<br>';
            echo '1. Drop the old woosync_settings table<br>';
            echo '2. Create a new table with the correct structure<br>';
            echo '3. Fix the database errors<br><br>';
            echo '<strong>⚠️ Warning:</strong> Your saved WooCommerce API credentials will be lost and need to be re-entered.';
            echo '</div>';
            
            // Check if table exists
            try {
                $stmt = $pdo->query("SHOW TABLES LIKE '{$tableName}'");
                $tableExists = $stmt->rowCount() > 0;
                
                if ($tableExists) {
                    echo '<h2>Current Table Status:</h2>';
                    echo '<div class="warning">✓ Table exists: <code>' . htmlspecialchars($tableName) . '</code></div>';
                    
                    // Show current structure
                    try {
                        $stmt = $pdo->query("DESCRIBE {$tableName}");
                        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        
                        echo '<h3>Current Columns:</h3>';
                        echo '<table>';
                        echo '<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th></tr>';
                        foreach ($columns as $col) {
                            $hasSettingKey = $col['Field'] === 'setting_key';
                            $rowClass = $hasSettingKey ? '' : 'style="background: #fff3cd;"';
                            echo '<tr ' . $rowClass . '>';
                            echo '<td>' . htmlspecialchars($col['Field']) . '</td>';
                            echo '<td>' . htmlspecialchars($col['Type']) . '</td>';
                            echo '<td>' . htmlspecialchars($col['Null']) . '</td>';
                            echo '<td>' . htmlspecialchars($col['Key']) . '</td>';
                            echo '</tr>';
                        }
                        echo '</table>';
                        
                        // Check if setting_key column exists
                        $columnNames = array_column($columns, 'Field');
                        if (!in_array('setting_key', $columnNames)) {
                            echo '<div class="error">';
                            echo '<strong>❌ Problem Detected!</strong><br>';
                            echo 'The table does NOT have a <code>setting_key</code> column.<br>';
                            echo 'This is why you\'re getting the "Unknown column" error.<br><br>';
                            echo '<strong>Click the button below to fix it:</strong>';
                            echo '</div>';
                        } else {
                            echo '<div class="success">';
                            echo '<strong>✅ Table structure looks correct!</strong><br>';
                            echo 'The <code>setting_key</code> column exists.<br>';
                            echo 'You may not need to run this fix.';
                            echo '</div>';
                        }
                        
                    } catch (PDOException $e) {
                        echo '<div class="error">Error checking table structure: ' . htmlspecialchars($e->getMessage()) . '</div>';
                    }
                    
                } else {
                    echo '<div class="warning">';
                    echo '<strong>Table does not exist yet.</strong><br>';
                    echo 'Click the button below to create it with the correct structure.';
                    echo '</div>';
                }
                
                // Show fix button
                echo '<h2>Fix the Database:</h2>';
                echo '<div class="warning">';
                echo '<strong>⚠️ Important:</strong> This will delete the existing table and recreate it.<br>';
                echo 'Any saved settings will be lost.';
                echo '</div>';
                echo '<a href="?action=fix&confirm=yes" class="btn btn-danger" onclick="return confirm(\'Are you sure? This will delete your saved WooCommerce credentials.\')">🔧 Fix Database Now</a>';
                
            } catch (PDOException $e) {
                echo '<div class="error">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
            }
        }
        
        echo '<hr style="margin: 30px 0;">';
        echo '<p style="color: #666; font-size: 14px;">';
        echo '<strong>Need Help?</strong><br>';
        echo 'Read: START_HERE.md or TABLE_MIGRATION_FIX.md in the plugin documentation.<br>';
        echo 'After fixing, delete this file from your server for security.';
        echo '</p>';
        ?>
    </div>
</body>
</html>
