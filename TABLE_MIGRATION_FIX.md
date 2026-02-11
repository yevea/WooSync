# Fix: Unknown Column 'setting_key' Error

## Your Situation

You pulled the latest code from GitHub using cPanel Git Version Control, but you're getting:
```
Unknown column 'setting_key' in 'WHERE'
Unknown column 'setting_key' in 'WHERE'
Unknown column 'setting_key' in 'WHERE'
Unknown column 'setting_key' in 'WHERE'
Unknown column 'setting_key' in 'WHERE'
```

## Why This Happens

Your database has the **old table structure** from a previous plugin version. The new code expects a column called `setting_key`, but your table has different columns.

The plugin now includes **automatic migration** that will:
1. Detect the old table structure
2. Drop the old table
3. Create the new table with correct structure

**Note:** This will reset your saved settings (WooCommerce API credentials). You'll need to re-enter them after the fix.

## The Automatic Fix

### Step 1: Update Plugin via cPanel Git

You already did this! ✅

### Step 2: Trigger the Migration

The migration runs when the plugin is **enabled** or when FacturaScripts detects an update.

**Try these methods:**

#### Method A: Disable and Re-Enable Plugin (Quickest)
1. Go to FacturaScripts: `/AdminPlugins`
2. Find "WooSync" in the list
3. Click "Disable" (or the toggle to turn it off)
4. Wait 2 seconds
5. Click "Enable" (or toggle it back on)
6. The migration should run automatically
7. Refresh the page (Ctrl+F5)

#### Method B: Use the Updater
1. Go to FacturaScripts menu
2. Click "Admin" → "Updater" (or "Actualizador")
3. Click "Update" or "Check for updates"
4. This triggers the plugin update() method
5. Migration runs automatically
6. Refresh and check

#### Method C: Clear Cache
1. Go to FacturaScripts
2. Admin → "Clear cache" (if available)
3. Or access: `https://yevea.com/053-contabilidad/fs1/?logout=TRUE`
4. Log back in
5. Go to AdminPlugins
6. Should trigger migration

## Manual Fix (If Automatic Doesn't Work)

If the automatic migration doesn't work, you can manually drop the old table:

### Option 1: Via phpMyAdmin

1. Log into cPanel
2. Go to phpMyAdmin
3. Select your FacturaScripts database
4. Find table: `woosync_settings`
5. Click on it
6. Click "Drop" tab at the top
7. Confirm the drop
8. Go back to FacturaScripts
9. Go to AdminPlugins
10. Disable and re-enable WooSync
11. New table will be created

### Option 2: Via SQL Query

Run this SQL in phpMyAdmin:

```sql
DROP TABLE IF EXISTS woosync_settings;
```

Then disable and re-enable the plugin in FacturaScripts.

## After the Fix

Once the table is recreated:

1. ✅ Go to FacturaScripts
2. ✅ Click "WooSync Configuration" in menu
3. ✅ You should see the configuration page without errors
4. ✅ Enter your WooCommerce settings:
   - Store URL: `https://your-store.com`
   - Consumer Key: (from WooCommerce)
   - Consumer Secret: (from WooCommerce)
5. ✅ Click "Save Settings"
6. ✅ Click "Test Connection"
7. ✅ If connection works, click "Sync All"

## Verification

To verify the fix worked:

**Check 1: No Error Messages**
- Go to `/AdminPlugins`
- No "Unknown column 'setting_key'" errors ✅

**Check 2: Configuration Page Loads**
- Click "WooSync Configuration"
- Page loads with input fields ✅
- No database errors ✅

**Check 3: Settings Save**
- Enter test values in configuration
- Click "Save Settings"
- Should see "Settings saved successfully" ✅

**Check 4: Test Connection**
- Enter real WooCommerce credentials
- Click "Test Connection"
- Should see connection result ✅

## If Still Not Working

### Check Table Structure

Go to phpMyAdmin and check if `woosync_settings` table has these columns:
- `id` (INT, PRIMARY KEY)
- `setting_key` (VARCHAR, UNIQUE)
- `setting_value` (TEXT)
- `updated_at` (TIMESTAMP)

If columns are different, the migration didn't run. Try manual drop method above.

### Re-Pull from GitHub

Make sure you have the latest code:

1. cPanel → Git Version Control
2. Find WooSync repository
3. Click "Manage"
4. Make sure branch is: `copilot/create-woosync-plugin`
5. Click "Pull" or "Update"
6. Check "Updated" timestamp is recent
7. Go to FacturaScripts AdminPlugins
8. Disable and re-enable WooSync

## What Changed

**Old Table (Problem):**
- Had different column names
- Plugin code expected `setting_key`
- Database had something else
- Caused "Unknown column" error

**New Table (Fixed):**
- Columns: id, setting_key, setting_value, updated_at
- Matches what code expects
- Auto-migration detects old structure
- Drops and recreates table automatically

## Time Required

- **Automatic fix:** 30 seconds (disable/enable plugin)
- **Manual fix:** 2 minutes (drop table via phpMyAdmin)
- **Re-entering settings:** 1 minute

**Total:** 1-3 minutes

## Important Notes

⚠️ **Settings Will Be Lost**
- Old settings are deleted when table is dropped
- You'll need to re-enter WooCommerce API credentials
- This only happens once during migration

✅ **One-Time Fix**
- After migration, table structure is correct
- Future updates won't require this
- Settings will persist going forward

✅ **No Data Loss**
- Only settings table is affected
- WooCommerce and FacturaScripts data is safe
- Sync can be re-run to get data again

## Success Indicators

You'll know it's fixed when:
- ✅ No "Unknown column 'setting_key'" errors
- ✅ Configuration page loads properly
- ✅ Can save and load settings
- ✅ Can test WooCommerce connection
- ✅ Can sync products, customers, orders
- ✅ Logs show "Settings saved successfully"

## Next Steps After Fix

1. Enter WooCommerce API credentials
2. Test connection
3. Run first sync (Products)
4. Check FacturaScripts for synced products
5. If successful, sync other entities
6. Set up regular sync schedule

## Support

If you still have issues after trying both methods:
- Check phpMyAdmin for table structure
- Check FacturaScripts logs
- Try full plugin re-install
- Check file permissions on server

The plugin should now work correctly once the table is migrated! 🎉
