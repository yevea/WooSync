# Complete Deployment Guide - WooSync Plugin

**For Users New to GitHub**

This guide will walk you through every step needed to get the WooSync plugin working on your server. No prior GitHub experience is required.

---

## Table of Contents

1. [Overview](#overview)
2. [What You Need Before Starting](#what-you-need-before-starting)
3. [Step 1: Download the Plugin from GitHub](#step-1-download-the-plugin-from-github)
4. [Step 2: Upload to Your Server](#step-2-upload-to-your-server)
5. [Step 3: Enable the Plugin](#step-3-enable-the-plugin)
6. [Step 4: Configure WooCommerce REST API](#step-4-configure-woocommerce-rest-api)
7. [Step 5: Configure WooSync Plugin](#step-5-configure-woosync-plugin)
8. [Step 6: Run Your First Sync](#step-6-run-your-first-sync)
9. [Step 7: Verify Everything Works](#step-7-verify-everything-works)
10. [Daily Usage](#daily-usage)
11. [Troubleshooting](#troubleshooting)
12. [Getting Help](#getting-help)

---

## Overview

The WooSync plugin has been completely rebuilt and is ready to use. It will sync data from your WooCommerce store to FacturaScripts:
- Products
- Customers
- Orders
- Stock levels
- Tax rates

**Important**: The sync is **one-way only** (WooCommerce → FacturaScripts). Changes in FacturaScripts will NOT sync back to WooCommerce.

**Time needed**: About 15-20 minutes total

---

## What You Need Before Starting

Before you begin, make sure you have:

✅ **Access to your web hosting** (FTP, cPanel File Manager, or similar)
✅ **Your FacturaScripts admin login** (username and password)
✅ **Your WordPress/WooCommerce admin login** (username and password)
✅ **Both WooCommerce and FacturaScripts on the same server** (shared hosting)
✅ **Basic file management skills** (uploading files via FTP or File Manager)

---

## Step 1: Download the Plugin from GitHub

### Option A: Download as ZIP (Easiest - Recommended)

1. **Open your web browser** and go to:
   ```
   https://github.com/yevea/WooSync
   ```

2. **Look for the branch selector** (usually says "main" by default)
   - Click on it to see the list of branches

3. **Select the branch** called:
   ```
   copilot/create-woosync-plugin
   ```
   - This is where the new plugin code is located

4. **Click the green "Code" button** (top right area)

5. **Click "Download ZIP"**
   - Your browser will download a file named something like:
   ```
   WooSync-copilot-create-woosync-plugin.zip
   ```

6. **Extract the ZIP file** on your computer
   - Right-click → Extract All (Windows)
   - Double-click (Mac)
   - You'll get a folder with all the plugin files inside

7. **Find the plugin files**
   - Inside the extracted folder, you should see:
     - Controller folder
     - DataBase folder
     - Lib folder
     - Model folder
     - View folder
     - README.md file
     - And other files

### Option B: Using Git (For Advanced Users)

If you're comfortable with command line:

```bash
# Clone the repository
git clone https://github.com/yevea/WooSync.git

# Navigate into the folder
cd WooSync

# Switch to the new plugin branch
git checkout copilot/create-woosync-plugin

# You now have all the files ready
```

---

## Step 2: Upload to Your Server

### Important: File Location

The plugin files must go to:
```
/your-facturascripts-installation/Plugins/WooSync/
```

For example, if your FacturaScripts is in `/home/username/public_html/facturascripts/`, then upload to:
```
/home/username/public_html/facturascripts/Plugins/WooSync/
```

### Using FTP (FileZilla, etc.)

1. **Open your FTP program**

2. **Connect to your server** using your FTP credentials

3. **Navigate to your FacturaScripts folder**

4. **Open the Plugins folder**
   - If the "Plugins" folder doesn't exist, create it

5. **Create a folder named "WooSync"** inside Plugins
   ```
   Plugins/WooSync/
   ```

6. **Upload all the plugin files** into this WooSync folder
   - Upload ALL files and folders you extracted from the ZIP
   - This includes:
     - Controller folder
     - DataBase folder
     - Lib folder
     - Model folder
     - View folder
     - All .php files
     - All .md files
     - facturascripts.ini
     - composer.json
     - .gitignore

7. **Wait for the upload to complete**
   - This usually takes 1-2 minutes

### Using cPanel File Manager

1. **Log into your cPanel**

2. **Open "File Manager"**

3. **Navigate to your FacturaScripts installation folder**

4. **Go into the "Plugins" folder**
   - If it doesn't exist, click "New Folder" and create it

5. **Create a new folder named "WooSync"**
   - Click "New Folder" → Type "WooSync" → Create

6. **Open the WooSync folder you just created**

7. **Click "Upload"**

8. **Select the ZIP file** you downloaded from GitHub
   - Or select all individual files if you've already extracted them

9. **If you uploaded a ZIP, extract it:**
   - Right-click on the ZIP file → Extract

10. **Move the files to the correct location**
    - Make sure all files are directly in `/Plugins/WooSync/`
    - NOT in `/Plugins/WooSync/WooSync-copilot-create-woosync-plugin/`
    - If they're in a subfolder, move them up one level

### Verify File Structure

After upload, your file structure should look like:
```
Plugins/
└── WooSync/
    ├── Controller/
    │   └── WooSyncConfig.php
    ├── DataBase/
    │   └── woosync.xml
    ├── Lib/
    │   ├── CustomerSyncService.php
    │   ├── OrderSyncService.php
    │   ├── ProductSyncService.php
    │   ├── StockSyncService.php
    │   ├── SyncService.php
    │   ├── TaxSyncService.php
    │   └── WooCommerceAPI.php
    ├── Model/
    │   ├── WooSyncConfig.php
    │   └── WooSyncLog.php
    ├── View/
    │   └── WooSyncConfig.html.twig
    ├── README.md
    ├── QUICK_START.md
    ├── WooSync.php
    ├── init.php
    ├── facturascripts.ini
    └── composer.json
```

---

## Step 3: Enable the Plugin

1. **Open your web browser**

2. **Go to your FacturaScripts admin panel**
   ```
   https://yourwebsite.com/facturascripts/
   ```
   (Replace with your actual URL)

3. **Log in** with your admin credentials

4. **In the menu, go to:**
   ```
   Admin → Plugins
   ```
   Or look for "Administrador" → "Complementos" (if in Spanish)

5. **Find "WooSync" in the list**
   - It should appear in the list of available plugins
   - Status: Disabled (red icon)

6. **Click the "Enable" button** next to WooSync
   - Or click on the plugin name, then click "Enable"

7. **Wait for the page to reload**
   - FacturaScripts will now install the plugin
   - This creates the necessary database tables

8. **You should see a success message**
   - "Plugin enabled successfully" or similar

9. **The plugin is now installed!**

### If the plugin doesn't appear:

- Check file permissions (should be 644 for files, 755 for folders)
- Make sure all files were uploaded correctly
- Check that `facturascripts.ini` file exists in the WooSync folder
- Refresh the page (Ctrl+F5)

---

## Step 4: Configure WooCommerce REST API

Now we need to create API credentials in WooCommerce so FacturaScripts can connect to it.

### Step 4.1: Access WooCommerce Settings

1. **Open a new browser tab**

2. **Go to your WordPress admin panel**
   ```
   https://yourwebsite.com/wp-admin/
   ```

3. **Log in** with your WordPress admin credentials

4. **In the left menu, hover over "WooCommerce"**

5. **Click "Settings"**

### Step 4.2: Navigate to REST API

1. **Click the "Advanced" tab** (at the top)

2. **Click "REST API"** (in the submenu)

3. **You'll see a list of API keys** (probably empty)

### Step 4.3: Create New API Key

1. **Click "Add key"** button

2. **Fill in the form:**

   - **Description**: Type something like:
     ```
     FacturaScripts Sync
     ```
     (This is just for your reference)

   - **User**: Select your admin user from the dropdown
     (Usually your username or "admin")

   - **Permissions**: Select **"Read"**
     (We only need to read data from WooCommerce)
     - If you want to be safer, use "Read"
     - If you might add features later, you can use "Read/Write"

3. **Click "Generate API key"** button

### Step 4.4: Save Your Credentials

**IMPORTANT**: The next screen is critical!

You will see:
- **Consumer Key**: A long string like `ck_abc123def456...`
- **Consumer Secret**: A long string like `cs_xyz789uvw012...`

**⚠️ COPY THESE NOW!** They will only be shown once!

1. **Copy the Consumer Key**
   - Click the copy icon, or select all and copy
   - Paste it into a text file on your computer
   - Label it "Consumer Key"

2. **Copy the Consumer Secret**
   - Click the copy icon, or select all and copy
   - Paste it into the same text file
   - Label it "Consumer Secret"

3. **Also write down your WooCommerce URL**
   - For example: `https://yourwebsite.com`
   - Make sure it starts with `https://`

### Step 4.5: Verify API is Active

1. **You should now see your API key in the list**
   - With the description you gave it
   - Status should show as active

2. **Keep your text file with the credentials handy**
   - You'll need them in the next step

---

## Step 5: Configure WooSync Plugin

Now we'll enter the API credentials into the WooSync plugin.

### Step 5.1: Access WooSync Configuration

1. **Go back to your FacturaScripts admin panel**

2. **In the menu, look for "WooSync Configuration"**
   - It should be under "Admin" section
   - Or "Administrador" if in Spanish

3. **Click "WooSync Configuration"**

4. **You'll see the configuration page** with a form

### Step 5.2: Enter WooCommerce Credentials

Fill in the form with your credentials:

1. **WooCommerce URL**
   - Enter your store URL
   - Example: `https://yourwebsite.com`
   - ⚠️ Must start with `https://` (not `http://`)
   - ⚠️ Do NOT include `/wp-admin` or anything after the domain
   - ⚠️ Do NOT include a trailing slash

2. **Consumer Key**
   - Paste the Consumer Key you copied from WooCommerce
   - It looks like: `ck_abc123def456...`

3. **Consumer Secret**
   - Paste the Consumer Secret you copied from WooCommerce
   - It looks like: `cs_xyz789uvw012...`

### Step 5.3: Save Settings

1. **Double-check all three fields**
   - Make sure there are no extra spaces
   - Make sure the URL starts with `https://`
   - Make sure you copied the complete keys

2. **Click "Save Settings"** button

3. **Wait for the page to reload**

4. **You should see a green success message:**
   ```
   ✓ Settings saved successfully!
   ```

5. **The status badge should now show:**
   ```
   Configured ✓ (green badge)
   ```

---

## Step 6: Run Your First Sync

Now comes the exciting part - syncing your data!

### Step 6.1: Test the Connection

Before syncing, let's make sure everything works:

1. **On the WooSync Configuration page**

2. **Click the "Test Connection" button**

3. **Wait a few seconds**

4. **You should see a success message:**
   ```
   ✅ Connection to WooCommerce successful!
   ```

#### If you see an error:

- **"Connection failed"**: Check your URL, it must be `https://`
- **"Invalid credentials"**: Double-check your Consumer Key and Secret
- **"SSL error"**: Your SSL certificate might have issues
- See the [Troubleshooting](#troubleshooting) section below

### Step 6.2: Run Full Synchronization

For the first sync, we'll sync everything:

1. **Click the "Sync All" button**

2. **A confirmation dialog appears:**
   ```
   This will sync all data (products, customers, orders, stock, taxes). Continue?
   ```

3. **Click "OK"**

4. **Wait patiently!**
   - This can take 30-60 seconds (or longer if you have lots of data)
   - DO NOT close the browser window
   - DO NOT click anything else
   - The page will reload when done

5. **You should see a success message** like:
   ```
   Full sync completed! Taxes: 3, Products: 45, Customers: 128, Orders: 89, Stock: 45
   ```
   (Your numbers will be different)

### What Just Happened?

The plugin just:
1. ✅ Imported all tax rates from WooCommerce
2. ✅ Imported all products (with prices and descriptions)
3. ✅ Imported all customers (with addresses and contact info)
4. ✅ Imported all orders (with order details)
5. ✅ Updated stock levels for all products

---

## Step 7: Verify Everything Works

Let's make sure your data was actually synced.

### Check Products

1. **In FacturaScripts menu, go to:**
   ```
   Sales → Products
   ```
   Or "Ventas" → "Productos"

2. **You should see your WooCommerce products!**
   - Check that names are correct
   - Check that prices match WooCommerce
   - Check that SKUs are there

### Check Customers

1. **In FacturaScripts menu, go to:**
   ```
   Sales → Customers
   ```
   Or "Ventas" → "Clientes"

2. **You should see your WooCommerce customers!**
   - Check that names are correct
   - Check that emails match
   - Click on a customer to see their full details

### Check Orders

1. **In FacturaScripts menu, go to:**
   ```
   Sales → Orders
   ```
   Or "Ventas" → "Pedidos"

2. **You should see your WooCommerce orders!**
   - Check that order numbers match
   - Click on an order to see details
   - Check that customer names are correct
   - Check that order items are listed

### Check Logs (Optional)

If you want to see what happened during the sync:

1. **Go to:**
   ```
   Admin → Logs
   ```

2. **Look for entries mentioning "WooSync"**

3. **You'll see details about:**
   - What was synced
   - How many items
   - Any errors (if there were any)

---

## Daily Usage

After the initial setup, here's how to use WooSync daily:

### When to Sync

You should sync when:
- ✅ **New orders come in** → Click "Sync Orders"
- ✅ **Products are added/changed** → Click "Sync Products"
- ✅ **Stock levels change** → Click "Sync Stock"
- ✅ **New customers register** → Click "Sync Customers"

### How to Sync

1. **Go to:** Admin → WooSync Configuration

2. **Choose what to sync:**
   - **Sync All** - Syncs everything (use once per day max)
   - **Sync Orders** - Just new orders (use multiple times per day)
   - **Sync Products** - Just product updates
   - **Sync Customers** - Just customer updates
   - **Sync Stock** - Just stock levels
   - **Sync Taxes** - Just tax rates (rarely needed)

3. **Click the appropriate button**

4. **Wait for completion**

5. **Check the success message**

### Recommended Sync Schedule

For a typical business:
- **Orders**: Sync 2-3 times per day (morning, afternoon, evening)
- **Products**: Sync once per day (or after making product changes)
- **Stock**: Sync once per day (or after stock updates)
- **Customers**: Sync once per day
- **Taxes**: Only when you change tax settings (rare)

### Things to Remember

⚠️ **One-Way Sync**: Changes in FacturaScripts will NOT sync back to WooCommerce

⚠️ **Manual Process**: You need to click the sync buttons manually (no automatic sync on shared hosting)

✅ **Safe to Run Multiple Times**: It won't create duplicates - existing items will be updated

✅ **Check Logs**: If something seems wrong, check Admin → Logs for details

---

## Troubleshooting

### Problem: "Plugin doesn't appear in Plugins list"

**Solutions:**
1. Check file permissions (folders: 755, files: 644)
2. Make sure `facturascripts.ini` exists in the WooSync folder
3. Clear browser cache (Ctrl+F5)
4. Check that files are in `Plugins/WooSync/` not in a subfolder

### Problem: "Connection failed"

**Solutions:**
1. **Check URL format:**
   - Must be `https://yourwebsite.com`
   - NOT `http://` (must use HTTPS)
   - NO trailing slash
   - NO `/wp-admin` or other paths

2. **Check credentials:**
   - Copy Consumer Key and Secret again from WooCommerce
   - Make sure no extra spaces
   - Re-save in WooSync

3. **Check WooCommerce:**
   - Make sure WooCommerce is active
   - Make sure REST API key is active
   - Try regenerating the API key

### Problem: "Products not syncing"

**Cause:** Products in WooCommerce must have a SKU

**Solutions:**
1. Go to WooCommerce → Products
2. Edit products without SKUs
3. Add a SKU to each product
4. Run "Sync Products" again

### Problem: "Customers not syncing"

**Cause:** Customers must have an email address

**Solutions:**
1. Check that WooCommerce customers have email addresses
2. Invalid/duplicate emails will be skipped

### Problem: "Orders not syncing"

**Solutions:**
1. Make sure customers are synced first (run "Sync Customers")
2. Make sure products are synced first (run "Sync Products")
3. Then run "Sync Orders"

### Problem: "Timeout / Page takes too long"

**Cause:** Shared hosting has time limits (usually 30-60 seconds)

**Solutions:**
1. Don't use "Sync All" on large stores
2. Use individual sync buttons instead:
   - First: "Sync Taxes"
   - Then: "Sync Products" (wait for completion)
   - Then: "Sync Customers" (wait for completion)
   - Then: "Sync Orders" (wait for completion)
   - Finally: "Sync Stock"
3. Contact your hosting provider to increase PHP max_execution_time

### Problem: "Settings not saving"

**Solutions:**
1. Check database permissions
2. Check that database tables were created:
   - `woosync_settings`
   - `woosync_logs`
3. Check Admin → Logs for error messages
4. Try disabling and re-enabling the plugin

### Problem: "Duplicate products/customers"

**Cause:** Shouldn't happen, but if it does:

**Solutions:**
1. Products are matched by SKU - check for duplicate SKUs
2. Customers are matched by email - check for duplicate emails
3. Orders are matched by WooCommerce ID in observations field

---

## Getting Help

### Check the Documentation

1. **README.md** - Complete documentation
2. **QUICK_START.md** - Quick 5-minute guide
3. **SECURITY.md** - Security information
4. **CHANGELOG.md** - What changed in version 2.0

### Check Logs

Most problems can be diagnosed by checking:
```
Admin → Logs
```
Filter by "WooSync" to see sync-related messages.

### Common Log Messages

- **"Connection test successful"** - Everything is working
- **"Skipping product ID X - no SKU"** - Add SKUs to products
- **"Skipping customer ID X - no email"** - Customer needs email
- **"Successfully synced product: XXXX"** - Product synced OK
- **"Error syncing..."** - Check the error message for details

### Still Need Help?

If you're still stuck:

1. **Check database tables:**
   - Look for `woosync_settings` table
   - Look for `woosync_logs` table

2. **Check PHP error logs:**
   - Ask your hosting provider how to access these

3. **Contact GitHub:**
   - Go to: https://github.com/yevea/WooSync
   - Create an "Issue" describing your problem
   - Include:
     - What you tried to do
     - What error message you got
     - PHP version
     - FacturaScripts version
     - WooCommerce version

---

## Summary Checklist

Use this checklist to make sure you've done everything:

### Installation
- [ ] Downloaded plugin from GitHub (branch: copilot/create-woosync-plugin)
- [ ] Uploaded files to `/Plugins/WooSync/`
- [ ] Files are in correct structure (no extra subfolders)
- [ ] Enabled plugin in FacturaScripts Admin → Plugins

### WooCommerce API
- [ ] Created REST API key in WooCommerce
- [ ] Copied Consumer Key
- [ ] Copied Consumer Secret
- [ ] Permissions set to "Read" (or "Read/Write")

### Plugin Configuration
- [ ] Entered WooCommerce URL (starts with `https://`)
- [ ] Entered Consumer Key
- [ ] Entered Consumer Secret
- [ ] Clicked "Save Settings"
- [ ] Clicked "Test Connection" - got success message

### First Sync
- [ ] Clicked "Sync All" button
- [ ] Wait for completion (30-60 seconds)
- [ ] Got success message with counts

### Verification
- [ ] Checked Products in FacturaScripts
- [ ] Checked Customers in FacturaScripts
- [ ] Checked Orders in FacturaScripts
- [ ] Everything looks correct

### Daily Use
- [ ] Know how to access WooSync Configuration
- [ ] Know which sync button to use
- [ ] Know when to sync (daily/multiple times)

---

## Congratulations! 🎉

If you've completed all the steps above, your WooSync plugin is now operational!

Your WooCommerce data is now syncing to FacturaScripts, and you can manage everything from one place.

**Remember:**
- Sync regularly (at least once per day for orders)
- Check logs if something seems wrong
- The sync is one-way only (WooCommerce → FacturaScripts)
- It's safe to run syncs multiple times

**Enjoy your synchronized data!**

---

*Last updated: Version 2.0*
*For technical documentation, see README.md*
