# Customer Sync Diagnostic Guide

## Still Getting "0 synced, 387 errors"? 

You need to run the diagnostic tool to find out WHY customers are failing.

## Quick Diagnostic (2 minutes)

### Step 1: Download Debug Script

Download this file:
```
debug-customer-sync.php
```

From GitHub:
```
https://raw.githubusercontent.com/yevea/WooSync/copilot/create-woosync-plugin/debug-customer-sync.php
```

### Step 2: Upload to Server

Upload to your FacturaScripts root directory:
```
/home/shopcat/public_html/053-contabilidad/fs1/debug-customer-sync.php
```

(Same directory as index.php)

### Step 3: Run in Browser

Open in your browser:
```
https://yevea.com/053-contabilidad/fs1/debug-customer-sync.php
```

### Step 4: Read Results

The script will show you EXACTLY what's wrong:

## Common Problems & Fixes

### Problem 1: No Countries Found ❌

**You see:**
```
❌ No countries found in paises table!
```

**What it means:**
FacturaScripts doesn't have any countries in the database. Customers MUST have a country code.

**How to fix:**
1. Go to FacturaScripts admin
2. Navigate to: **Admin → Tools → Countries** (or similar)
3. Click "Initialize Countries" or "Import Countries"
4. This loads default countries into the database

OR manually add Spain:
```sql
INSERT INTO paises (codpais, nombre) VALUES ('ESP', 'España');
```

**Then try customer sync again!**

### Problem 2: Country Code Mismatch ⚠️

**You see:**
```
⚠ US: Not found
⚠ GB: Not found
✅ ESP: España
```

**What it means:**
- WooCommerce uses 2-letter codes (US, GB)
- Your FacturaScripts uses 3-letter codes (USA, GBR)
- Plugin tries to use default (ESP) but maybe that doesn't exist either

**How to fix:**
Make sure at least ONE country exists. The plugin will use it as default.

### Problem 3: Required Field Missing ❌

**You see:**
```
❌ Failed to save test customer!
Errors: codpais is required
```

**What it means:**
A required field is missing or invalid.

**How to fix:**
This is a code issue - the enhanced logging will show which field.
Check the WooSync logs for details.

### Problem 4: Test Customer Succeeds ✅

**You see:**
```
✅ Test customer saved successfully!
```

**What it means:**
The database and structure are fine! The problem is in the actual WooCommerce data.

**How to fix:**
1. Pull latest code (has enhanced logging)
2. Try customer sync
3. Check FacturaScripts logs
4. Logs now show EXACT error for each customer
5. Look for pattern in errors

## After Running Diagnostic

### If Countries Missing

1. Initialize countries in FacturaScripts
2. Verify countries appear in diagnostic
3. Try customer sync again
4. Should work!

### If Other Issue

1. Read the error message carefully
2. Follow the specific fix shown
3. Re-run diagnostic to verify fix
4. Try customer sync again

## Important: Delete Script After Use!

**For security, delete the diagnostic script:**
```
rm /home/shopcat/public_html/053-contabilidad/fs1/debug-customer-sync.php
```

## Enhanced Logging

Latest code version includes detailed logging that shows:
- Customer code being used
- Country code being set
- All field values
- Exact save() errors
- Stack traces

Check your FacturaScripts logs or WooSync logs table for these details.

## Next Steps

1. **Run diagnostic** (2 minutes)
2. **Fix issue shown** (varies)
3. **Verify fix** (re-run diagnostic)
4. **Sync customers** (1-2 minutes)
5. **Success!** 🎉

## Need Help?

If diagnostic shows an issue you don't understand:
1. Take screenshot of diagnostic results
2. Check FacturaScripts documentation
3. Verify FacturaScripts is properly initialized
4. Check that all FacturaScripts features are working

The diagnostic will show you EXACTLY what needs to be fixed!
