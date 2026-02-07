# Which Plugin Is Failing?

## Important Discovery

Martin, the error you're seeing happens when FacturaScripts **scans ALL plugins** in your `/Plugins/` directory. 

**The error might not be caused by WooSync at all!**

It could be:
- Another plugin that has a broken `facturascripts.ini` file
- A test/backup plugin directory with issues
- An old version of a plugin

## How To Find The Failing Plugin

### Step 1: List All Plugins

1. Go to your cPanel File Manager
2. Navigate to: `/home/YOURUSER/053-contabilidad/fs1/Plugins/`
3. List ALL directories you see there

**Write down all plugin names.**

### Step 2: Check Each Plugin

For each plugin directory, check if it has a `facturascripts.ini` file:

**Look for:**
- Missing `facturascripts.ini` file
- Empty `facturascripts.ini` file  
- Corrupted `facturascripts.ini` file
- Extra spaces or weird characters

### Step 3: Temporarily Remove Plugins

To find the problem plugin:

1. Create a backup folder: `/Plugins/_BACKUP/`
2. Move ALL plugins to `_BACKUP` except Core ones
3. Try accessing AdminPlugins page
4. If it works, move plugins back ONE AT A TIME
5. When error appears again, you found the bad plugin!

## Quick Test

Create this PHP file and upload to your FacturaScripts root:

**File: `test-plugins.php`**

```php
<?php
// Test which plugin has the broken INI file

$pluginsDir = __DIR__ . '/Plugins';
echo "<h1>Testing All Plugins</h1>\n";
echo "<pre>\n";

if (!is_dir($pluginsDir)) {
    die("Plugins directory not found: $pluginsDir\n");
}

$plugins = scandir($pluginsDir);

foreach ($plugins as $plugin) {
    if ($plugin === '.' || $plugin === '..') continue;
    
    $pluginPath = $pluginsDir . '/' . $plugin;
    if (!is_dir($pluginPath)) continue;
    
    $iniFile = $pluginPath . '/facturascripts.ini';
    
    echo "\n=================================\n";
    echo "Plugin: $plugin\n";
    echo "=================================\n";
    
    if (!file_exists($iniFile)) {
        echo "❌ No facturascripts.ini file found\n";
        continue;
    }
    
    if (!is_readable($iniFile)) {
        echo "❌ INI file exists but is NOT READABLE\n";
        echo "   File: $iniFile\n";
        continue;
    }
    
    // Try to read the file
    $content = file_get_contents($iniFile);
    if ($content === false) {
        echo "❌ Cannot read INI file\n";
        continue;
    }
    
    if (empty($content)) {
        echo "❌ INI file is EMPTY\n";
        continue;
    }
    
    // Try to parse it
    $data = parse_ini_file($iniFile);
    
    if ($data === false) {
        echo "❌ ❌ ❌ PARSE ERROR - THIS IS THE PROBLEM PLUGIN! ❌ ❌ ❌\n";
        echo "   File path: $iniFile\n";
        echo "   File size: " . filesize($iniFile) . " bytes\n";
        echo "\n   Content:\n";
        echo "   ---START---\n";
        echo "   " . str_replace("\n", "\n   ", $content);
        echo "\n   ---END---\n";
        echo "\n";
        
        // Show hex dump
        echo "   Hex dump (first 200 bytes):\n   ";
        for ($i = 0; $i < min(200, strlen($content)); $i++) {
            printf("%02x ", ord($content[$i]));
            if (($i + 1) % 16 === 0) echo "\n   ";
        }
        echo "\n";
        
    } else {
        echo "✅ Plugin OK\n";
        echo "   Name: " . ($data['name'] ?? 'N/A') . "\n";
        echo "   Version: " . ($data['version'] ?? 'N/A') . "\n";
    }
}

echo "\n\nTest complete!\n";
echo "</pre>\n";
?>
```

### How to Use:

1. Download this file
2. Upload to: `/home/YOURUSER/053-contabilidad/fs1/test-plugins.php`
3. Access in browser: `https://yevea.com/053-contabilidad/fs1/test-plugins.php`
4. **Look for the plugin with ❌❌❌ marks**

That's the plugin causing your error!

## Most Likely Scenarios

### Scenario 1: WooSync Itself
- **Solution:** Re-download from GitHub branch `copilot/create-woosync-plugin`
- **File needed:** facturascripts.ini (187 bytes)

### Scenario 2: Another Plugin
- **Solution:** Fix or remove that plugin's INI file
- **Prevention:** Check before installing new plugins

### Scenario 3: Backup/Old Files
- **Solution:** Remove old backup directories from `/Plugins/`
- **Example:** `/Plugins/WooSync.backup/` or `/Plugins/WooSync-old/`

## After Finding The Problem

Once you identify which plugin is failing:

1. **If it's WooSync:** Download fresh from GitHub
2. **If it's another plugin:** Fix its `facturascripts.ini` or remove it
3. **If it's a backup:** Delete the backup directory
4. **If it's unknown:** Send me the plugin name and I'll help

## Expected Result

The `test-plugins.php` script will show EXACTLY which plugin has the broken INI file, making it easy to fix.

---

Martin, please run this test and let me know what you find!
