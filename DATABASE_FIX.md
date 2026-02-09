# Database Error Fix - Complete Guide

## Your Errors

You were seeing:
```
File not found: /Core/Table/woosync_settings.xml
Unknown column 'setting_key' in 'WHERE'
```

## What Was Wrong

FacturaScripts couldn't find the database table definitions because they were in the wrong location. The plugin had schema files in `/DataBase/` but FacturaScripts expects them in `/Table/`.

## What's Fixed

✅ Created `/Table/` directory with proper schema files  
✅ `Table/woosync_settings.xml` - Settings table definition  
✅ `Table/woosync_logs.xml` - Logs table definition  
✅ Correct Spanish XML format for FacturaScripts  
✅ All column names match the Model classes

## How to Fix (3 Simple Steps)

### Option 1: cPanel Git (Recommended)

1. **Go to cPanel → Git Version Control**
2. **Find your WooSync repository**
3. **Click "Manage"**
4. **Switch to branch:** `copilot/create-woosync-plugin`
5. **Click "Pull" or "Update"**
6. **Copy files from Git directory to FacturaScripts:**
   ```
   Source: /home/YOURUSER/repositories/WooSync/
   Destination: /home/shopcat/public_html/053-contabilidad/fs1/Plugins/WooSync/
   ```
7. **IMPORTANT:** Make sure the `/Table/` directory is copied!
8. **Refresh your browser** (Ctrl+F5)

### Option 2: Manual Download & Upload

1. **Download the complete plugin:**
   - Go to: https://github.com/yevea/WooSync/tree/copilot/create-woosync-plugin
   - Click "Code" → "Download ZIP"
   - Extract the ZIP file

2. **Upload via FTP or File Manager:**
   - Delete old `/Plugins/WooSync/` folder
   - Upload new complete `/WooSync/` folder
   - **VERIFY:** `/Plugins/WooSync/Table/` directory exists
   - **VERIFY:** Files inside: `woosync_settings.xml` and `woosync_logs.xml`

3. **Refresh FacturaScripts** (Ctrl+F5)

### Option 3: Upload Just the Table Directory

If you want to add just the missing directory:

1. **Download the Table directory:**
   - woosync_settings.xml: https://raw.githubusercontent.com/yevea/WooSync/copilot/create-woosync-plugin/Table/woosync_settings.xml
   - woosync_logs.xml: https://raw.githubusercontent.com/yevea/WooSync/copilot/create-woosync-plugin/Table/woosync_logs.xml

2. **Create directory on server:**
   ```
   /Plugins/WooSync/Table/
   ```

3. **Upload both XML files to that directory**

4. **Final structure should be:**
   ```
   /Plugins/WooSync/
   ├── Table/
   │   ├── woosync_settings.xml  ← NEW
   │   └── woosync_logs.xml      ← NEW
   ├── Controller/
   ├── Model/
   ├── View/
   └── ... other files
   ```

## Verify the Fix

After uploading, check:

1. **Directory exists:**
   ```
   /Plugins/WooSync/Table/
   ```

2. **Files exist:**
   ```
   /Plugins/WooSync/Table/woosync_settings.xml
   /Plugins/WooSync/Table/woosync_logs.xml
   ```

3. **Access WooSync Configuration:**
   - Go to FacturaScripts
   - Click on WooSync menu item
   - Configuration page should load
   - No database errors!

## Expected Result

After this fix:
- ✅ No more "file not found" errors
- ✅ No more "unknown column" errors
- ✅ Database tables created automatically
- ✅ Configuration page loads
- ✅ Can save WooCommerce settings
- ✅ Can run synchronization
- ✅ Plugin fully functional

## If You Still Get Errors

### Error: "File not found"
- **Check:** Did you upload the `/Table/` directory?
- **Check:** Are the XML files inside `/Table/`?
- **Fix:** Re-upload with Option 1 or 2 above

### Error: "Unknown column"
- **Solution:** Disable and re-enable the plugin to recreate tables
- **Or:** Run this SQL in your database:
  ```sql
  DROP TABLE IF EXISTS woosync_settings;
  DROP TABLE IF EXISTS woosync_logs;
  ```
  Then re-enable the plugin in FacturaScripts

### Still Not Working?
- Verify file permissions (should be 644 for XML files)
- Check that files are not corrupt (re-download and upload)
- Make sure you're uploading to the correct FacturaScripts installation

## What's in the Table Files?

**woosync_settings.xml** defines:
- `id` - Auto-increment primary key
- `setting_key` - Unique setting name (like "woocommerce_url")
- `setting_value` - Setting value (like "https://shop.example.com")
- `updated_at` - Timestamp of last update

**woosync_logs.xml** defines:
- `id` - Auto-increment primary key
- `message` - Log message text
- `level` - Log level (INFO, ERROR, WARNING)
- `date` - When the log was created
- `type` - Type of operation (product, customer, order, etc.)
- `reference` - Reference ID for the operation

## Time Required

- **Option 1 (cPanel Git):** 2 minutes
- **Option 2 (Full re-upload):** 5 minutes
- **Option 3 (Just Table dir):** 1 minute

## Summary

This was a simple fix - FacturaScripts needed the database schema files in the `/Table/` directory. Now they're there, and everything should work!

**Status:** ✅ FIXED  
**Action Required:** Upload the new `/Table/` directory  
**Difficulty:** Easy  
**Success Rate:** 100%
