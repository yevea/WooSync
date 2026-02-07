<?php
/**
 * WooSync INI File Verification Script
 * 
 * Upload this file to your /Plugins/WooSync/ directory and access it via browser.
 * It will show you exactly what's in your facturascripts.ini file and whether it parses correctly.
 */

header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>WooSync INI Verification</title>
    <style>
        body {
            font-family: 'Courier New', monospace;
            margin: 20px;
            background: #f5f5f5;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            border-bottom: 3px solid #4CAF50;
            padding-bottom: 10px;
        }
        .success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
            padding: 15px;
            margin: 10px 0;
            border-radius: 4px;
        }
        .error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
            padding: 15px;
            margin: 10px 0;
            border-radius: 4px;
        }
        .warning {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 15px;
            margin: 10px 0;
            border-radius: 4px;
        }
        .info {
            background: #d1ecf1;
            border: 1px solid #bee5eb;
            color: #0c5460;
            padding: 15px;
            margin: 10px 0;
            border-radius: 4px;
        }
        pre {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            padding: 15px;
            overflow-x: auto;
            border-radius: 4px;
        }
        .line {
            padding: 2px 0;
        }
        .line-number {
            display: inline-block;
            width: 30px;
            color: #999;
            text-align: right;
            margin-right: 10px;
        }
        .correct {
            color: #28a745;
            font-weight: bold;
        }
        .incorrect {
            color: #dc3545;
            font-weight: bold;
            background: #fff3cd;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }
        th, td {
            padding: 8px;
            text-align: left;
            border: 1px solid #ddd;
        }
        th {
            background: #f8f9fa;
        }
        .check {
            font-size: 20px;
            font-weight: bold;
        }
        .check.pass { color: #28a745; }
        .check.fail { color: #dc3545; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 WooSync INI File Verification</h1>

<?php
$iniFile = __DIR__ . '/facturascripts.ini';
$phpVersion = PHP_VERSION;
$serverInfo = php_uname();

echo "<div class='info'>";
echo "<strong>Server Information:</strong><br>";
echo "PHP Version: {$phpVersion}<br>";
echo "Server: {$serverInfo}<br>";
echo "Current Directory: " . __DIR__ . "<br>";
echo "INI File Path: {$iniFile}";
echo "</div>";

// Check if file exists
if (!file_exists($iniFile)) {
    echo "<div class='error'>";
    echo "<h2>❌ ERROR: File Not Found!</h2>";
    echo "<p>The file <code>facturascripts.ini</code> does not exist in this directory.</p>";
    echo "<p><strong>Expected location:</strong> {$iniFile}</p>";
    echo "<p><strong>Action:</strong> Make sure you uploaded the INI file to the correct directory.</p>";
    echo "</div>";
    exit;
}

echo "<div class='success'>";
echo "<h2>✅ File Exists</h2>";
echo "<p>Found: <code>{$iniFile}</code></p>";
echo "</div>";

// Check if file is readable
if (!is_readable($iniFile)) {
    echo "<div class='error'>";
    echo "<h2>❌ ERROR: File Not Readable!</h2>";
    echo "<p>The file exists but cannot be read. Check file permissions.</p>";
    echo "<p><strong>Required:</strong> File should have 644 permissions (rw-r--r--)</p>";
    echo "</div>";
    exit;
}

// Get file content
$content = file_get_contents($iniFile);
$lines = explode("\n", $content);
$lineCount = count($lines);

// Show file size
$fileSize = filesize($iniFile);
echo "<div class='info'>";
echo "<strong>File Size:</strong> {$fileSize} bytes<br>";
echo "<strong>Line Count:</strong> {$lineCount} lines";
echo "</div>";

// Show raw file content
echo "<h2>📄 Raw File Content</h2>";
echo "<pre>";
echo htmlspecialchars($content);
echo "</pre>";

// Show line-by-line analysis
echo "<h2>📋 Line-by-Line Analysis</h2>";
echo "<pre>";
$lineNum = 1;
foreach ($lines as $line) {
    $line = rtrim($line); // Remove trailing whitespace for display
    if (empty($line)) {
        echo "<div class='line'><span class='line-number'>{$lineNum}.</span><em>(blank line)</em></div>";
    } else {
        // Check if line has proper format
        $hasQuotes = (preg_match('/=\s*"[^"]*"/', $line) === 1);
        $class = $hasQuotes ? 'correct' : 'incorrect';
        $indicator = $hasQuotes ? '✅' : '❌';
        
        echo "<div class='line {$class}'>";
        echo "<span class='line-number'>{$lineNum}.</span>";
        echo htmlspecialchars($line);
        echo " {$indicator}";
        echo "</div>";
    }
    $lineNum++;
}
echo "</pre>";

// Parse the INI file
echo "<h2>🔬 PHP Parse Test</h2>";
$parseResult = @parse_ini_file($iniFile);

if ($parseResult === false) {
    echo "<div class='error'>";
    echo "<h3>❌ PARSE FAILED!</h3>";
    echo "<p><strong>This is the problem!</strong> PHP's <code>parse_ini_file()</code> returned FALSE.</p>";
    echo "<p>This means your INI file has incorrect formatting.</p>";
    echo "<h4>Common Causes:</h4>";
    echo "<ul>";
    echo "<li>Values without quotes (e.g., <code>version = 2.0</code> instead of <code>version = \"2.0\"</code>)</li>";
    echo "<li>Inconsistent quoting (some values quoted, others not)</li>";
    echo "<li>Special characters not properly escaped</li>";
    echo "<li>Syntax errors in the file</li>";
    echo "</ul>";
    echo "</div>";
    $allGood = false;
} else {
    echo "<div class='success'>";
    echo "<h3>✅ PARSE SUCCESSFUL!</h3>";
    echo "<p>PHP successfully parsed the INI file.</p>";
    echo "</div>";
    
    // Show parsed values
    echo "<h3>Parsed Values:</h3>";
    echo "<table>";
    echo "<tr><th>Key</th><th>Value</th><th>Type</th><th>Status</th></tr>";
    
    $allGood = true;
    $expectedKeys = ['name', 'description', 'version', 'min_version', 'require'];
    
    foreach ($expectedKeys as $key) {
        $status = isset($parseResult[$key]) ? '✅ Present' : '❌ Missing';
        $value = isset($parseResult[$key]) ? htmlspecialchars($parseResult[$key]) : '-';
        $type = isset($parseResult[$key]) ? gettype($parseResult[$key]) : '-';
        
        if (!isset($parseResult[$key])) {
            $allGood = false;
        }
        
        echo "<tr>";
        echo "<td><strong>{$key}</strong></td>";
        echo "<td>{$value}</td>";
        echo "<td>{$type}</td>";
        echo "<td>{$status}</td>";
        echo "</tr>";
    }
    echo "</table>";
}

// Final verdict
echo "<h2>🎯 Final Verdict</h2>";

if (!isset($parseResult) || $parseResult === false) {
    echo "<div class='error'>";
    echo "<h3 class='check fail'>❌ FAIL - FILE IS BROKEN</h3>";
    echo "<p><strong>Your facturascripts.ini file is NOT parsing correctly.</strong></p>";
    echo "<p>This is why you're getting the error in FacturaScripts!</p>";
    echo "</div>";
    
    echo "<div class='warning'>";
    echo "<h3>🔧 How to Fix:</h3>";
    echo "<ol>";
    echo "<li><strong>Download the correct file:</strong><br>";
    echo "<a href='https://raw.githubusercontent.com/yevea/WooSync/copilot/create-woosync-plugin/facturascripts.ini' target='_blank'>";
    echo "https://raw.githubusercontent.com/yevea/WooSync/copilot/create-woosync-plugin/facturascripts.ini";
    echo "</a></li>";
    echo "<li><strong>Save it as:</strong> <code>facturascripts.ini</code> (make sure extension is .ini, not .txt)</li>";
    echo "<li><strong>Upload to:</strong> <code>/Plugins/WooSync/</code> (overwrite the old file)</li>";
    echo "<li><strong>Run this script again</strong> to verify it's fixed</li>";
    echo "<li><strong>Refresh FacturaScripts</strong> (Ctrl+F5)</li>";
    echo "</ol>";
    echo "</div>";
    
} elseif (!$allGood) {
    echo "<div class='warning'>";
    echo "<h3 class='check fail'>⚠️ WARNING - FILE IS INCOMPLETE</h3>";
    echo "<p>The file parses, but some required fields are missing.</p>";
    echo "</div>";
} else {
    echo "<div class='success'>";
    echo "<h3 class='check pass'>✅ SUCCESS - FILE IS CORRECT!</h3>";
    echo "<p><strong>Your facturascripts.ini file is perfect!</strong></p>";
    echo "<p>If you're still getting errors in FacturaScripts:</p>";
    echo "<ol>";
    echo "<li>Clear FacturaScripts cache: Admin → Tools → Clear Cache</li>";
    echo "<li>Refresh the page with Ctrl+F5 (force refresh)</li>";
    echo "<li>Check that the file is in the correct location: <code>/Plugins/WooSync/facturascripts.ini</code></li>";
    echo "<li>Make sure you're not in a subdirectory like <code>/Plugins/WooSync/WooSync/</code></li>";
    echo "</ol>";
    echo "</div>";
}

// Show expected format
echo "<h2>📝 Expected Format</h2>";
echo "<div class='info'>";
echo "<p>Your <code>facturascripts.ini</code> should look EXACTLY like this:</p>";
echo "</div>";
echo "<pre>";
echo 'name = "WooSync"' . "\n";
echo 'description = "Sincroniza productos, clientes, pedidos, stock y taxes de WooCommerce con FacturaScripts (one-way sync)"' . "\n";
echo 'version = "2.0"' . "\n";
echo 'min_version = "2025"' . "\n";
echo 'require = "Core"' . "\n";
echo "</pre>";

echo "<div class='warning'>";
echo "<p><strong>Important:</strong> ALL values MUST have quotes around them!</p>";
echo "<p>❌ WRONG: <code>version = 2.0</code></p>";
echo "<p>✅ CORRECT: <code>version = \"2.0\"</code></p>";
echo "</div>";

?>

        <hr>
        <p style="text-align: center; color: #666; font-size: 12px;">
            WooSync Plugin v2.0 | Verification Script<br>
            <a href="https://github.com/yevea/WooSync" target="_blank">GitHub Repository</a>
        </p>
    </div>
</body>
</html>
