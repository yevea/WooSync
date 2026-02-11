# 🚨 PULL CODE FIRST! - How to Get the Debug Script

## Why You're Seeing 404

You tried to access:
```
https://yevea.com/053-contabilidad/fs1/debug-customer-sync.php
```

And got: **404 Page Not Found**

### The Reason

The `debug-customer-sync.php` file IS in the GitHub repository, but it's NOT on your server yet!

**You need to "pull" the code from Git to your server first.**

---

## Solution: Pull Code from Git

### Method 1: cPanel Git Version Control (Recommended)

**Step-by-Step:**

1. **Login to cPanel**
   - Go to: https://yevea.com/cpanel
   - Login with your credentials

2. **Open Git Version Control**
   - Find: "Git Version Control" tool
   - Click to open it

3. **Find Your Repository**
   - Look for: WooSync repository
   - Should show path: `/home/shopcat/public_html/053-contabilidad/fs1/Plugins/WooSync`

4. **Check Branch**
   - Current branch should be: `copilot/create-woosync-plugin`
   - If not, switch to this branch first

5. **Pull Latest Code**
   - Click: "Pull or Deploy" button
   - Or click: "Update" or "Pull" (depends on cPanel version)
   - Wait for: "Pull successful" message

6. **Verify Success**
   - Check that these files now exist on your server:
     - `/home/shopcat/public_html/053-contabilidad/fs1/debug-customer-sync.php` ✅
     - Updated plugin files in `/Plugins/WooSync/` ✅

**Time:** 2 minutes  
**Difficulty:** Easy

---

### Method 2: Manual Download (Alternative)

If cPanel Git isn't working:

1. **Go to GitHub**
   ```
   https://github.com/yevea/WooSync
   ```

2. **Select Branch**
   - Click branch dropdown (usually says "main")
   - Select: `copilot/create-woosync-plugin`

3. **Download the File**
   - Navigate to: `debug-customer-sync.php`
   - Click: "Raw" button
   - Right-click → Save As
   - Save to your computer

4. **Upload to Server**
   - Use cPanel File Manager or FTP
   - Upload to: `/home/shopcat/public_html/053-contabilidad/fs1/`
   - File should be next to `index.php`

5. **Set Permissions**
   - File permissions: 644 (not 755, it's a PHP file)
   - Owner: Your web server user

**Time:** 5 minutes  
**Difficulty:** Medium

---

## After Pulling Code

### 1. Verify File Exists

Check if file is on your server:
```
/home/shopcat/public_html/053-contabilidad/fs1/debug-customer-sync.php
```

**How to check:**
- cPanel File Manager → Navigate to fs1 folder → Look for file
- Or use FTP client
- File should be ~5.5 KB

### 2. Access Diagnostic

Now try accessing:
```
https://yevea.com/053-contabilidad/fs1/debug-customer-sync.php
```

**Should work!** No more 404!

### 3. Read Results

The diagnostic will show:
- ✅ What's working
- ❌ What's broken
- 🔧 How to fix it

---

## What Files Should Be Present

After pulling, you should have:

**In FacturaScripts root (`fs1/`):**
- `debug-customer-sync.php` ← NEW (this is what you need!)
- `fix-database.php` (from earlier fix)
- `test-plugins.php` (from earlier diagnostic)
- `verify-ini.php` (from earlier diagnostic)
- `index.php` (FacturaScripts main file)

**In Plugin folder (`fs1/Plugins/WooSync/`):**
- Updated `Lib/CustomerSyncService.php` (with fixes)
- Updated `Lib/SyncService.php` (with database init)
- All other plugin files

---

## Troubleshooting

### "I don't see Git Version Control in cPanel"

**Solution:** Use Method 2 (manual download)

### "Pull button is grayed out"

**Possible reasons:**
- Already up to date (check file modification dates)
- Git conflicts (unlikely for first pull)
- Need to commit local changes first

**Fix:** Try manual download instead

### "Still getting 404 after pull"

**Check:**
1. File actually exists: `/home/shopcat/public_html/053-contabilidad/fs1/debug-customer-sync.php`
2. File permissions: Should be 644
3. URL is correct: Must include `fs1/` in path
4. Clear browser cache: Ctrl+F5

### "File exists but shows blank page"

**Check:**
- PHP errors in log
- File permissions
- FacturaScripts `config.php` exists and is correct

---

## Common Git Confusion

This is normal! Many people think:
- ❌ "I changed files on GitHub, they should be on my server"
- ✅ **Reality:** You must "pull" to sync GitHub → Server

**Git workflow:**
1. Code is updated on GitHub ✅
2. Your server doesn't automatically know ❌
3. You "pull" to sync ✅
4. Files appear on server ✅

---

## Summary

**The Problem:** 404 error  
**The Cause:** File not on server yet  
**The Solution:** Pull code from Git  
**The Time:** 2-5 minutes  
**The Result:** Diagnostic accessible!

---

## Next Steps

1. ✅ Pull code (you're doing this now!)
2. ✅ Verify file exists
3. ✅ Access diagnostic
4. ✅ Read results
5. ✅ Fix identified issue
6. ✅ Customer sync works!

**You're almost there! Just pull the code first!** 🚀
