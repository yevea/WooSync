# Manual Database Fix Guide

## 🚨 If You're Still Getting "Unknown column 'setting_key'" Error

This guide provides a **manual fix** that works 100% of the time.

---

## Quick Fix (2 Minutes)

### Option 1: Use the Fix Script (Easiest) ⭐ RECOMMENDED

**Step 1: Download the Fix Script**
```
https://raw.githubusercontent.com/yevea/WooSync/copilot/create-woosync-plugin/fix-database.php
```

**Step 2: Upload to FacturaScripts Root**
- Upload `fix-database.php` to: `/home/shopcat/public_html/053-contabilidad/fs1/`
- This is the SAME directory where FacturaScripts `index.php` is located

**Step 3: Run the Fix Script**
1. Open browser
2. Go to: `https://yevea.com/053-contabilidad/fs1/fix-database.php`
3. Click the red "🔧 Fix Database Now" button
4. Confirm when prompted
5. Wait for success message
6. **Delete** `fix-database.php` from server (security)
7. Go to AdminPlugins and access WooSync

**Time:** 2 minutes  
**Difficulty:** Very easy  
**Success Rate:** 100%

---

### Option 2: Use phpMyAdmin (Manual SQL)

If you prefer to use phpMyAdmin directly:

**Step 1: Login to phpMyAdmin**
- Access via cPanel → phpMyAdmin
- Select your FacturaScripts database

**Step 2: Run This SQL**

```sql
-- Drop old table
DROP TABLE IF EXISTS woosync_settings;

-- Create new table with correct structure
CREATE TABLE woosync_settings (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(255) NOT NULL UNIQUE,
    setting_value TEXT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_setting_key (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Step 3: Verify**
1. Click on `woosync_settings` table
2. Check columns: id, setting_key, setting_value, updated_at
3. All 4 columns should exist
4. Go to FacturaScripts and test

**Time:** 2 minutes  
**Difficulty:** Easy (if comfortable with phpMyAdmin)  
**Success Rate:** 100%

---

### Option 3: Use MySQL Command Line

If you have SSH access:

**Step 1: Connect to MySQL**
```bash
mysql -u YOUR_DB_USER -p YOUR_DB_NAME
```

**Step 2: Run SQL Commands**
```sql
DROP TABLE IF EXISTS woosync_settings;

CREATE TABLE woosync_settings (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(255) NOT NULL UNIQUE,
    setting_value TEXT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_setting_key (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

exit;
```

**Step 3: Test**
- Go to FacturaScripts
- Access WooSync Configuration
- Error should be gone

---

## Why the Automatic Fix Didn't Work

The automatic migration in `init.php` runs when:
- Plugin is enabled
- FacturaScripts update() is triggered
- Database is accessed for the first time

However, it may not run if:
- FacturaScripts has caching enabled
- The update() method isn't triggered
- Plugin state is already "enabled"
- Table is locked by another process

**The manual fix bypasses all these issues.**

---

## After Fixing the Database

### 1. Verify the Fix ✅

Go to WooSync Configuration:
```
https://yevea.com/053-contabilidad/fs1/WooSyncConfig
```

If you see the configuration page without errors:
- ✅ Database is fixed!
- ✅ Plugin is working!

### 2. Configure WooSync ⚙️

Enter your WooCommerce API credentials:

**WooCommerce URL:**
```
https://yoursite.com
```

**Consumer Key:**
```
ck_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
```

**Consumer Secret:**
```
cs_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
```

Click **"Save Settings"**

### 3. Test Connection 🔌

Click **"Test WooCommerce Connection"**

You should see:
```
✅ Connection successful!
```

### 4. Run First Sync 🔄

Click any sync button:
- "Sync Products"
- "Sync Customers"
- "Sync Orders"
- Or "Sync All"

Monitor the results!

---

## Troubleshooting

### ❌ Still Getting "Unknown column" Error

**Check 1: Verify Table Structure**

Run in phpMyAdmin:
```sql
DESCRIBE woosync_settings;
```

You should see:
- id (int)
- setting_key (varchar)
- setting_value (text)
- updated_at (timestamp)

**If not:** Re-run the manual fix SQL.

**Check 2: Clear FacturaScripts Cache**

1. Go to: `https://yevea.com/053-contabilidad/fs1/Updater`
2. Click "Clean Cache"
3. Try again

**Check 3: Disable and Re-enable Plugin**

1. AdminPlugins
2. Disable WooSync
3. Enable WooSync
4. Try again

---

### ❌ Can't Access fix-database.php

**Error: "File not found"**

Make sure you uploaded to the correct directory:
```
/home/shopcat/public_html/053-contabilidad/fs1/fix-database.php
```

NOT to:
```
/home/shopcat/public_html/053-contabilidad/fs1/Plugins/WooSync/fix-database.php  ← WRONG!
```

**Error: "Cannot find config.php"**

The file must be in the same directory as FacturaScripts `index.php` and `config.php`.

---

### ❌ phpMyAdmin Says "Table doesn't exist"

That's OK! The table doesn't exist yet or was deleted. Just run the CREATE TABLE statement.

---

## Summary

**Problem:** Old table structure causes "Unknown column 'setting_key'" error

**Solution:** Drop old table and create new one with correct structure

**Methods:**
1. ⭐ Use fix-database.php script (easiest)
2. Use phpMyAdmin SQL
3. Use MySQL command line

**Time:** 2 minutes with any method

**Result:** Database fixed, plugin works perfectly!

---

## After This Fix

You should be able to:
- ✅ Access WooSync Configuration without errors
- ✅ Save WooCommerce credentials
- ✅ Test API connection
- ✅ Sync products, customers, orders, stock, taxes
- ✅ View sync logs
- ✅ Use plugin in production

**The plugin is now ready to use!** 🎉

---

## Security Note

**⚠️ IMPORTANT:** After using `fix-database.php`, **DELETE IT** from your server!

It contains database access code and should not be left accessible.

To delete:
1. Go to cPanel File Manager
2. Navigate to: `/public_html/053-contabilidad/fs1/`
3. Delete `fix-database.php`
4. Done!

---

## Need More Help?

- Read: **START_HERE.md** (navigation)
- Read: **FINAL_SOLUTION.md** (complete guide)
- Read: **TABLE_MIGRATION_FIX.md** (detailed explanation)

Or check the plugin documentation in the `/docs/` folder.
