# Diagnostic Steps - Still Getting INI Error

**If you're still getting the error after following all instructions, the file on your server is likely incorrect or corrupted.**

---

## What Went Wrong

The `facturascripts.ini` file on your server is **NOT** the correct version. Here's what you need to check:

### The Problem

Your server's INI file probably looks like this (WRONG):
```ini
name = "WooSync"
description = "Sincroniza..."
version = 2.0          ← NO QUOTES (BREAKS PARSING!)
min_version = 2025     ← NO QUOTES (BREAKS PARSING!)
require = Core         ← NO QUOTES (BREAKS PARSING!)
```

It **MUST** look like this (CORRECT):
```ini
name = "WooSync"
description = "Sincroniza productos, clientes, pedidos, stock y taxes de WooCommerce con FacturaScripts (one-way sync)"
version = "2.0"        ← QUOTES REQUIRED!
min_version = "2025"   ← QUOTES REQUIRED!
require = "Core"       ← QUOTES REQUIRED!
```

---

## Step 1: Check What's Actually On Your Server

### Using cPanel File Manager:

1. **Open cPanel File Manager**

2. **Navigate to your plugin folder:**
   ```
   /public_html/facturascripts/Plugins/WooSync/
   ```
   (Or wherever your FacturaScripts is installed)

3. **Find and open:** `facturascripts.ini`

4. **Check if ALL values have quotes:**
   - If ANY line is missing quotes around the value → THAT'S THE PROBLEM!

### Using FTP:

1. Connect to your server
2. Go to `/Plugins/WooSync/`
3. Download `facturascripts.ini`
4. Open it with a text editor (Notepad++, VS Code, etc.)
5. Check if ALL lines have quotes

---

## Step 2: Replace The File

You have **THREE options** to fix this:

### Option A: Download Just The INI File (FASTEST - 2 minutes)

1. **Download the correct file:**
   - Go to: https://github.com/yevea/WooSync/blob/copilot/create-woosync-plugin/facturascripts.ini
   - Click "Raw" button (top right)
   - Right-click → Save As → `facturascripts.ini`
   - **Make sure it saves as .ini, not .txt!**

2. **Upload to your server:**
   - Use cPanel File Manager or FTP
   - Navigate to: `/Plugins/WooSync/`
   - Upload the file (overwrite the old one)

3. **Verify it uploaded correctly:**
   - Open the file on your server
   - ALL values should have quotes
   - Should be exactly 5 lines (no blank lines at start)

4. **Refresh FacturaScripts:**
   - Go to Admin → Plugins
   - Press Ctrl+F5 (force refresh)
   - Error should be GONE!

### Option B: Edit The File Directly On Server (QUICK - 3 minutes)

If you can edit files on your server:

1. **Open the file:**
   - In cPanel File Manager or FTP client
   - Find: `/Plugins/WooSync/facturascripts.ini`
   - Click "Edit" or "Code Editor"

2. **Replace ENTIRE content with this:**
   ```ini
   name = "WooSync"
   description = "Sincroniza productos, clientes, pedidos, stock y taxes de WooCommerce con FacturaScripts (one-way sync)"
   version = "2.0"
   min_version = "2025"
   require = "Core"
   ```

3. **Save the file**

4. **Verify:**
   - Re-open the file
   - Check ALL values have quotes
   - Check there are NO extra spaces or blank lines

5. **Refresh FacturaScripts**

### Option C: Re-download Everything (SAFEST - 5 minutes)

1. **Delete the old plugin:**
   - In cPanel File Manager
   - Delete folder: `/Plugins/WooSync/`

2. **Download from GitHub:**
   - Go to: https://github.com/yevea/WooSync
   - Make sure branch is: **copilot/create-woosync-plugin** (top-left)
   - Click "Code" → "Download ZIP"

3. **Extract and upload:**
   - Extract the ZIP
   - Upload the WooSync folder to `/Plugins/`
   - Make sure path is: `/Plugins/WooSync/` (not `/Plugins/WooSync/WooSync/`)

4. **Refresh FacturaScripts**

---

## Step 3: Verify It's Fixed

### Check The File On Server:

Open `/Plugins/WooSync/facturascripts.ini` and verify:

✅ Line 1: `name = "WooSync"` (has quotes)
✅ Line 2: `description = "Sincroniza..."` (has quotes)
✅ Line 3: `version = "2.0"` (has quotes)
✅ Line 4: `min_version = "2025"` (has quotes)
✅ Line 5: `require = "Core"` (has quotes)

**If ANY line doesn't have quotes, the file is WRONG!**

### Check FacturaScripts:

1. Go to: Admin → Plugins
2. Press Ctrl+F5 (force refresh)
3. You should see:
   - ✅ WooSync in the list
   - ✅ NO error messages
   - ✅ Can click "Enable"

---

## Common Mistakes

### Mistake 1: Wrong Branch

❌ **Problem:** Downloaded from `main` branch instead of `copilot/create-woosync-plugin`

✅ **Solution:** 
- Make sure branch selector shows: **copilot/create-woosync-plugin**
- Main branch still has the old broken file!

### Mistake 2: Wrong File Location

❌ **Problem:** Files in `/Plugins/WooSync/WooSync/` (double folder)

✅ **Solution:**
- Should be: `/Plugins/WooSync/facturascripts.ini`
- NOT: `/Plugins/WooSync/WooSync/facturascripts.ini`

### Mistake 3: File Encoding/Line Endings

❌ **Problem:** File saved with wrong encoding or line endings

✅ **Solution:**
- Use UTF-8 encoding (no BOM)
- Use Unix line endings (LF, not CRLF)
- Or just download the file from GitHub Raw

### Mistake 4: Extra Spaces or Characters

❌ **Problem:** Copy-paste added extra spaces or invisible characters

✅ **Solution:**
- Download from GitHub Raw (don't copy-paste)
- Or use the exact content provided above

### Mistake 5: Cached Version

❌ **Problem:** FacturaScripts is showing cached/old version

✅ **Solution:**
- Press Ctrl+F5 (force refresh)
- Or clear FacturaScripts cache: Admin → Tools → Clear Cache
- Or restart PHP-FPM in cPanel if available

---

## Still Not Working?

If you've tried all of the above and STILL get the error:

### 1. Verify File Content

Download the file FROM YOUR SERVER (not from GitHub) and check:
- Does it have exactly 5 lines?
- Does EVERY line have quotes around the value?
- Are there any extra blank lines at the start or end?
- Are there any weird characters?

### 2. Check File Permissions

In cPanel File Manager or FTP:
- File should be: **644** (rw-r--r--)
- Folder should be: **755** (rwxr-xr-x)

### 3. Check File Path

Make sure the file is in the EXACT location:
```
/home/yourusername/public_html/facturascripts/Plugins/WooSync/facturascripts.ini
```

Not in:
- `/Plugins/WooSync-repo/` (that's the Git directory!)
- `/Plugins/WooSync/WooSync/` (double folder)
- `/Plugins/` (missing WooSync folder)

### 4. Check PHP Error Logs

In cPanel:
- Go to: Errors or Error Log
- Look for messages about the INI file
- Might show WHICH file it's trying to parse

### 5. Test The File

Create a test PHP file on your server: `test-ini.php`
```php
<?php
$file = __DIR__ . '/facturascripts.ini';
echo "File: $file\n";
echo "Exists: " . (file_exists($file) ? 'YES' : 'NO') . "\n";
echo "Readable: " . (is_readable($file) ? 'YES' : 'NO') . "\n";
echo "\nContent:\n";
echo file_get_contents($file);
echo "\n\nParse result:\n";
var_dump(parse_ini_file($file));
?>
```

Upload it to `/Plugins/WooSync/` and access:
```
https://yoursite.com/facturascripts/Plugins/WooSync/test-ini.php
```

This will show you:
- If the file exists
- What content is actually in it
- If it parses correctly

---

## The Correct File

Here's the EXACT content that should be in `facturascripts.ini`:

```ini
name = "WooSync"
description = "Sincroniza productos, clientes, pedidos, stock y taxes de WooCommerce con FacturaScripts (one-way sync)"
version = "2.0"
min_version = "2025"
require = "Core"
```

**5 lines, no blank lines before or after, ALL values in quotes.**

---

## Quick Checklist

Before asking for more help, verify:

- [ ] Downloaded from **copilot/create-woosync-plugin** branch (not main)
- [ ] File is in `/Plugins/WooSync/facturascripts.ini` (correct path)
- [ ] File has exactly 5 lines
- [ ] ALL values have quotes (including version, min_version, require)
- [ ] No extra blank lines at start or end
- [ ] Refreshed page with Ctrl+F5
- [ ] File permissions are correct (644)
- [ ] Using the latest version from GitHub

---

## Get Help

If still stuck, create a GitHub issue with:

1. **Exact content of your server's facturascripts.ini** (copy-paste it)
2. **File path** (where exactly is the file located?)
3. **Branch used** (main or copilot/create-woosync-plugin?)
4. **Method used** (cPanel Git, manual download, FTP?)
5. **Screenshot** of the error

---

**Remember:** The file MUST have quotes around ALL values, not just some of them!

**GitHub:** https://github.com/yevea/WooSync  
**Branch:** copilot/create-woosync-plugin  
**File:** facturascripts.ini  
**Status:** NOW FIXED with all quotes!
