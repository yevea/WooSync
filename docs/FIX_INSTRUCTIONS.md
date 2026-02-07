# SOLUTION: INI File Parsing Error

## Your Error
You encountered this error in FacturaScripts:
```
🚨 Error ac09439e284e33dac060804e85884911
Uncaught TypeError: FacturaScripts\Core\Internal\Plugin::loadIniData(): 
Argument #1 ($data) must be of type array, false given
```

## What Happened
The `facturascripts.ini` file in the plugin had a syntax error. The description field contained parentheses `(one-way sync)` without quotes, which caused PHP's INI file parser to fail.

## The Fix
✅ **The problem has been FIXED in the GitHub repository!**

The `facturascripts.ini` file now has all values properly quoted:

**Before (broken):**
```ini
name = WooSync
description = Sincroniza productos, clientes, pedidos, stock y taxes de WooCommerce con FacturaScripts (one-way sync)
version = 2.0
```

**After (fixed):**
```ini
name = "WooSync"
description = "Sincroniza productos, clientes, pedidos, stock y taxes de WooCommerce con FacturaScripts (one-way sync)"
version = "2.0"
```

## What You Need To Do

### Option 1: Re-upload Just the INI File (Fastest)

1. **Download the fixed file:**
   - Go to: https://github.com/yevea/WooSync
   - Make sure branch `copilot/create-woosync-plugin` is selected
   - Navigate to the file: `facturascripts.ini`
   - Click "Raw" or "Download"
   - Save the file to your computer

2. **Re-upload to your server:**
   - Using FTP or cPanel File Manager
   - Go to: `/Plugins/WooSync/`
   - Upload the new `facturascripts.ini` file
   - **Replace** the old one (overwrite it)

3. **Refresh FacturaScripts:**
   - Go back to your browser
   - Go to: Admin → Plugins
   - Press Ctrl+F5 (force refresh)
   - The error should be gone!

### Option 2: Re-download Everything (Recommended)

If you want to make sure you have all the latest fixes:

1. **Download the complete plugin:**
   - Go to: https://github.com/yevea/WooSync
   - Select branch: `copilot/create-woosync-plugin`
   - Click green "Code" button → "Download ZIP"
   - Extract the ZIP file

2. **Delete the old plugin from your server:**
   - Via FTP or cPanel File Manager
   - Delete the entire `/Plugins/WooSync/` folder

3. **Upload the new files:**
   - Upload the entire WooSync folder to `/Plugins/`
   - Make sure the structure is: `/Plugins/WooSync/` (not `/Plugins/WooSync/WooSync/`)

4. **Refresh FacturaScripts:**
   - Go to: Admin → Plugins
   - Press Ctrl+F5
   - WooSync should appear without errors

### Option 3: Manual Fix (If you can't re-download)

If you prefer to fix the file manually:

1. **Edit the file on your server:**
   - Open: `/Plugins/WooSync/facturascripts.ini`
   - Using a text editor (cPanel File Manager or FTP)

2. **Add quotes around all values:**
   ```ini
   name = "WooSync"
   description = "Sincroniza productos, clientes, pedidos, stock y taxes de WooCommerce con FacturaScripts (one-way sync)"
   version = "2.0"
   min_version = "2025"
   require = "Core"
   ```

3. **Save the file**
   - Make sure it's saved as plain text (UTF-8 or ASCII)
   - No special formatting or BOM

4. **Refresh FacturaScripts:**
   - Press Ctrl+F5 in your browser
   - Go to: Admin → Plugins

## Verification

After applying the fix, you should see:
- ✅ No error messages
- ✅ WooSync appears in the plugins list
- ✅ Status shows "Disabled" (ready to enable)
- ✅ You can click "Enable" without errors

## Next Steps

Once the error is fixed:
1. Enable the plugin (click "Enable" button)
2. Continue with the setup from DEPLOYMENT_GUIDE.md
3. Follow from "Step 4: Configure WooCommerce REST API"

## Need More Help?

If you still see errors after trying these solutions:

1. **Check the file:**
   - Make sure `facturascripts.ini` exists in `/Plugins/WooSync/`
   - Make sure it has the quotes around all values
   - Check file permissions (should be 644)

2. **Check the documentation:**
   - See DEPLOYMENT_GUIDE.md → Troubleshooting section
   - Look for "Error loading plugin" section

3. **Ask for help:**
   - Create an issue on GitHub: https://github.com/yevea/WooSync/issues
   - Include the exact error message
   - Mention you've already tried the INI file fix

## Technical Details (For Reference)

**Why did this happen?**
PHP's `parse_ini_file()` function treats certain characters as special:
- Parentheses `( )`
- Square brackets `[ ]`
- Semicolons `;`
- Equal signs `=` (outside of value assignment)

When these appear in values without quotes, the parser fails and returns `false` instead of an array.

**The Solution:**
Quote all string values in INI files to avoid parsing issues.

**PHP Version:**
You're using PHP 8.4.17, which has strict type checking. The error is caught immediately when `parse_ini_file()` returns `false` (boolean) instead of an array.

---

**Status:** ✅ FIXED in GitHub repository
**Action Required:** Re-download and re-upload the `facturascripts.ini` file
**Time Needed:** 2-3 minutes

Good luck! The fix is simple and should work immediately. 🎉
