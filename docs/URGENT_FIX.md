# 🚨 URGENT: Fix Your INI File Error

**You're getting this error because the `facturascripts.ini` file on your server is incorrect or outdated.**

---

## 🎯 FASTEST FIX (2 Minutes)

### Step 1: Download the Verification Script

1. **Download this file:**
   - https://raw.githubusercontent.com/yevea/WooSync/copilot/create-woosync-plugin/verify-ini.php
   - Right-click → Save As → `verify-ini.php`

2. **Upload to your server:**
   - Put it in: `/Plugins/WooSync/verify-ini.php`

3. **Run it in your browser:**
   - Go to: `https://yevea.com/053-contabilidad/fs1/Plugins/WooSync/verify-ini.php`
   - This will show you EXACTLY what's wrong with your file

### Step 2: Fix The File

After running the verification script, it will tell you if your file is broken.

**If the file is broken, fix it:**

1. **Download the correct file:**
   - https://raw.githubusercontent.com/yevea/WooSync/copilot/create-woosync-plugin/facturascripts.ini
   - Right-click → Save As → `facturascripts.ini`
   - **Make sure it saves as .ini, not .txt!**

2. **Upload to your server:**
   - Location: `/Plugins/WooSync/facturascripts.ini`
   - **OVERWRITE** the old file

3. **Verify it's fixed:**
   - Run verify-ini.php again
   - Should show ✅ SUCCESS

4. **Refresh FacturaScripts:**
   - Go to: https://yevea.com/053-contabilidad/fs1/AdminPlugins
   - Press **Ctrl+F5** (force refresh)
   - Error should be GONE!

---

## 📋 What The File Should Look Like

Your `facturascripts.ini` **MUST** look EXACTLY like this:

```ini
name = "WooSync"
description = "Sincroniza productos, clientes, pedidos, stock y taxes de WooCommerce con FacturaScripts (one-way sync)"
version = "2.0"
min_version = "2025"
require = "Core"
```

**Key Points:**
- ✅ ALL values MUST have quotes
- ✅ Exactly 5 lines
- ✅ No blank lines before or after
- ✅ No extra spaces

**Common Mistakes:**
- ❌ `version = 2.0` (no quotes) - WRONG!
- ❌ `require = Core` (no quotes) - WRONG!
- ❌ `version = "2.0"` (has quotes) - CORRECT!

---

## 🔍 How to Check Your Current File

### Method 1: Use Verification Script (Recommended)
- Upload verify-ini.php
- Access it via browser
- It shows you exactly what's in your file

### Method 2: Check Manually in cPanel
1. Open cPanel File Manager
2. Navigate to: `/Plugins/WooSync/`
3. Right-click `facturascripts.ini` → Edit
4. Check if ALL values have quotes
5. If ANY value is missing quotes → THAT'S THE PROBLEM!

---

## ⚠️ Why This Happens

**The Problem:**
- PHP's `parse_ini_file()` is VERY strict about formatting
- If values don't have quotes → Parse fails
- If quoting is inconsistent → Parse fails
- Parse failure returns FALSE instead of array
- FacturaScripts expects array → TypeError

**The Solution:**
- Quote ALL values consistently
- Download the correct file from GitHub
- Upload it to your server

---

## 🆘 Still Not Working?

If you've done all the above and STILL get the error:

### Check These:

1. **File Location**
   - Should be: `/Plugins/WooSync/facturascripts.ini`
   - NOT: `/Plugins/WooSync/WooSync/facturascripts.ini` (double folder)
   - NOT: `/Plugins/facturascripts.ini` (wrong location)

2. **File Permissions**
   - Should be: 644 (rw-r--r--)
   - If different, change to 644

3. **File Content**
   - Run verify-ini.php to see actual content
   - Compare with expected format above
   - Every line should have quotes

4. **Cache**
   - Clear FacturaScripts cache
   - Admin → Tools → Clear Cache
   - Refresh with Ctrl+F5

5. **Branch**
   - Make sure you downloaded from: `copilot/create-woosync-plugin`
   - NOT from: `main` (old broken version)

---

## 📞 Get Help

If nothing works, provide this information:

1. **Output from verify-ini.php** (screenshot or copy-paste)
2. **Content of your facturascripts.ini** (copy-paste entire file)
3. **File path** (where is the file located exactly?)
4. **Method used** (cPanel Git, FTP, manual download?)

---

## ✅ Quick Checklist

Before asking for more help, verify:

- [ ] Downloaded verify-ini.php from GitHub
- [ ] Uploaded it to `/Plugins/WooSync/`
- [ ] Ran it in browser and checked results
- [ ] If file is broken, downloaded correct facturascripts.ini
- [ ] Uploaded correct file to `/Plugins/WooSync/`
- [ ] Ran verify-ini.php again to confirm it's fixed
- [ ] Cleared FacturaScripts cache
- [ ] Refreshed page with Ctrl+F5
- [ ] Checked file is in correct location
- [ ] Checked ALL values have quotes

---

## 🔗 Links

- **Verification Script:** https://raw.githubusercontent.com/yevea/WooSync/copilot/create-woosync-plugin/verify-ini.php
- **Correct INI File:** https://raw.githubusercontent.com/yevea/WooSync/copilot/create-woosync-plugin/facturascripts.ini
- **GitHub Repository:** https://github.com/yevea/WooSync
- **Branch to Use:** copilot/create-woosync-plugin

---

## 📝 Summary

**Problem:** INI file has wrong format or is missing quotes
**Solution:** Download correct file from GitHub and upload to server
**Verify:** Use verify-ini.php script to check
**Time:** 2-5 minutes
**Result:** Error will be gone!

---

**REMEMBER:** The file on GitHub is correct. Your server has the old/wrong version. You need to re-download and re-upload it!
