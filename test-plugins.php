<?php
/**
 * Test which plugin has the broken INI file
 * Upload this to your FacturaScripts root directory and access via browser
 * Example: https://yevea.com/053-contabilidad/fs1/test-plugins.php
 */

$pluginsDir = __DIR__ . '/Plugins';
?>
<!DOCTYPE html>
<html>
<head>
    <title>FacturaScripts Plugin Diagnostic</title>
    <style>
        body {font-family: monospace; padding: 20px; background: #f5f5f5;}
        .plugin {background: white; margin: 10px 0; padding: 15px; border-left: 5px solid #ddd;}
        .plugin.ok {border-color: #4CAF50;}
        .plugin.error {border-color: #f44336; background: #ffebee;}
        .plugin-name {font-size: 18px; font-weight: bold; margin-bottom: 10px;}
        .status {padding: 5px 10px; border-radius: 3px; display: inline-block;}
        .status.ok {background: #4CAF50; color: white;}
        .status.error {background: #f44336; color: white;}
        .content {background: #f9f9f9; padding: 10px; margin-top: 10px; overflow-x: auto;}
        .hex {color: #666; font-size: 11px;}
        h1 {color: #333;}
    </style>
</head>
<body>
<h1>🔍 FacturaScripts Plugin Diagnostic</h1>
<p>Testing all plugins in: <code><?php echo htmlspecialchars($pluginsDir); ?></code></p>
<hr>

<?php

if (!is_dir($pluginsDir)) {
    echo "<div class='plugin error'>";
    echo "<div class='plugin-name'>❌ ERROR: Plugins directory not found</div>";
    echo "<p>Path: " . htmlspecialchars($pluginsDir) . "</p>";
    echo "<p>This script must be in the FacturaScripts root directory.</p>";
    echo "</div>";
    exit;
}

$plugins = scandir($pluginsDir);
$errorCount = 0;
$okCount = 0;

foreach ($plugins as $plugin) {
    if ($plugin === '.' || $plugin === '..') continue;
    
    $pluginPath = $pluginsDir . '/' . $plugin;
    if (!is_dir($pluginPath)) continue;
    
    $iniFile = $pluginPath . '/facturascripts.ini';
    
    $hasError = false;
    $errorMsg = '';
    
    if (!file_exists($iniFile)) {
        $hasError = true;
        $errorMsg = '❌ No facturascripts.ini file found';
    } elseif (!is_readable($iniFile)) {
        $hasError = true;
        $errorMsg = '❌ INI file exists but is NOT READABLE';
    } else {
        $content = @file_get_contents($iniFile);
        if ($content === false) {
            $hasError = true;
            $errorMsg = '❌ Cannot read INI file';
        } elseif (empty($content)) {
            $hasError = true;
            $errorMsg = '❌ INI file is EMPTY';
        } else {
            $data = @parse_ini_file($iniFile);
            if ($data === false) {
                $hasError = true;
                $errorMsg = '❌ ❌ ❌ PARSE ERROR - THIS IS THE PROBLEM! ❌ ❌ ❌';
                $errorCount++;
            } else {
                $okCount++;
            }
        }
    }
    
    $cssClass = $hasError ? 'error' : 'ok';
    echo "<div class='plugin $cssClass'>";
    echo "<div class='plugin-name'>Plugin: " . htmlspecialchars($plugin) . "</div>";
    
    if ($hasError) {
        echo "<div class='status error'>FAILED</div>";
        echo "<p><strong>" . htmlspecialchars($errorMsg) . "</strong></p>";
        
        if (isset($content)) {
            echo "<div class='content'>";
            echo "<strong>File path:</strong> " . htmlspecialchars($iniFile) . "<br>";
            echo "<strong>File size:</strong> " . filesize($iniFile) . " bytes<br><br>";
            echo "<strong>Content:</strong><br>";
            echo "<pre>" . htmlspecialchars($content) . "</pre>";
            
            echo "<br><strong>Hex dump (first 200 bytes):</strong><br>";
            echo "<div class='hex'>";
            for ($i = 0; $i < min(200, strlen($content)); $i++) {
                printf("%02x ", ord($content[$i]));
                if (($i + 1) % 16 === 0) echo "<br>";
            }
            echo "</div>";
            echo "</div>";
        }
    } else {
        echo "<div class='status ok'>OK</div>";
        if (isset($data)) {
            echo "<p>";
            echo "Name: <strong>" . htmlspecialchars($data['name'] ?? 'N/A') . "</strong><br>";
            echo "Version: <strong>" . htmlspecialchars($data['version'] ?? 'N/A') . "</strong><br>";
            echo "Min Version: <strong>" . htmlspecialchars($data['min_version'] ?? 'N/A') . "</strong>";
            echo "</p>";
        }
    }
    
    echo "</div>";
}

echo "<hr>";
echo "<h2>Summary</h2>";
echo "<p>✅ OK Plugins: <strong>$okCount</strong></p>";
echo "<p>❌ Failed Plugins: <strong>$errorCount</strong></p>";

if ($errorCount > 0) {
    echo "<div class='plugin error'>";
    echo "<h3>🚨 ACTION REQUIRED</h3>";
    echo "<p>Fix or remove the plugins marked with ❌ above.</p>";
    echo "<p>The error on AdminPlugins page is caused by one of these plugins.</p>";
    echo "</div>";
} else {
    echo "<div class='plugin ok'>";
    echo "<h3>✅ All Plugins OK!</h3>";
    echo "<p>All plugin INI files are valid. If you still get errors, the problem might be:</p>";
    echo "<ul>";
    echo "<li>File permissions issue</li>";
    echo "<li>PHP configuration</li>";
    echo "<li>FacturaScripts cache</li>";
    echo "</ul>";
    echo "</div>";
}

?>

</body>
</html>
