# cPanel Git Deployment Guide

**For users deploying WooSync from GitHub to shared hosting using cPanel Git Version Control**

---

## What This Guide Covers

This guide explains how to:
1. Set up cPanel Git Version Control
2. Connect your cPanel to GitHub repository
3. Deploy the WooSync plugin automatically
4. Update the plugin when new versions are released
5. Troubleshoot common deployment issues

---

## Prerequisites

Before starting, make sure you have:
- ✅ cPanel access to your shared hosting
- ✅ GitHub account (the plugin repository is at: https://github.com/yevea/WooSync)
- ✅ FacturaScripts installed on your server
- ✅ File Manager or FTP access (for initial setup)

---

## Part 1: Setting Up cPanel Git Version Control

### Step 1.1: Access Git Version Control

1. **Log into your cPanel**
   - URL is usually: `https://yourwebsite.com:2083`
   - Or provided by your hosting provider

2. **Find "Git Version Control"**
   - In cPanel search box, type: "Git"
   - Click on **"Git™ Version Control"**
   - Icon looks like a branching tree

3. **You'll see the Git Repositories page**
   - This shows all your Git repositories (if any)
   - Click **"Create"** to add a new repository

### Step 1.2: Create Repository Connection

1. **Click "Create" button**

2. **Fill in the form:**

   **Clone a Repository**
   - Select this option (should be default)

   **Repository Path:**
   ```
   https://github.com/yevea/WooSync
   ```
   - This is the GitHub repository URL
   - Copy it exactly as shown

   **Repository Name:**
   ```
   WooSync
   ```
   - This is just a label for you to identify it in cPanel

   **Clone URL:**
   - This is auto-filled based on Repository Path
   - Should be: `https://github.com/yevea/WooSync.git`

   **Repository Directory:**
   ```
   /home/yourusername/WooSync-repo
   ```
   - Replace `yourusername` with your actual cPanel username
   - This is where Git will store the repository files
   - **Important:** This is NOT your final plugin location
   - This is just the Git working directory

3. **Click "Create"**
   - cPanel will clone the repository
   - This may take 30-60 seconds
   - You'll see a success message

### Step 1.3: Switch to Main Branch

**Important:** By default, Git might not be on the `main` branch.

1. **In the Git Repositories list**, find your WooSync repository

2. **Click "Manage"** next to it

3. **Look for "Current Branch"** at the top
   - It might show a different branch

4. **Switch to `main` branch:**
   - Click the **"Switch Branch"** button or dropdown
   - Select **"main"** from the list
   - Click "Switch" or "Checkout"

5. **Verify you're on `main`:**
   - Current Branch should now show: **main**
   - This is the branch with all the fixed files

---

## Part 2: Deploying to FacturaScripts

Now you need to copy the plugin files from the Git directory to your FacturaScripts Plugins folder.

### Step 2.1: Identify Your Paths

You have two important paths:

**Git Repository Path:**
```
/home/yourusername/WooSync-repo/
```
This is where cPanel Git stores the files.

**FacturaScripts Plugin Path:**
```
/home/yourusername/public_html/facturascripts/Plugins/WooSync/
```
This is where FacturaScripts needs the files.

**Your paths may be different!** Common variations:
- `/home/yourusername/www/facturascripts/`
- `/home/yourusername/htdocs/facturascripts/`
- `/home/yourusername/public_html/fs/`

### Step 2.2: Copy Files (First Time Setup)

**Using cPanel File Manager:**

1. **Open File Manager** in cPanel

2. **Navigate to your Git repository:**
   - Go to: `/home/yourusername/WooSync-repo/`
   - You should see all the plugin files

3. **Select all plugin files:**
   - Controller folder
   - DataBase folder
   - Lib folder
   - Model folder
   - View folder
   - All .php, .ini, .md files
   - **Do NOT** select the .git folder

4. **Copy the files:**
   - Click "Copy" button at the top
   - Or right-click → Copy

5. **Navigate to FacturaScripts:**
   - Go to: `/public_html/facturascripts/Plugins/`
   - If `Plugins` folder doesn't exist, create it

6. **Create WooSync folder:**
   - Inside Plugins, create a folder named: `WooSync`
   - Click into this folder

7. **Paste the files:**
   - Click "Paste" button
   - All files should be copied here

8. **Verify the structure:**
   ```
   /Plugins/WooSync/
   ├── Controller/
   ├── DataBase/
   ├── Lib/
   ├── Model/
   ├── View/
   ├── facturascripts.ini
   ├── WooSync.php
   ├── init.php
   └── (other files)
   ```

### Step 2.3: Set File Permissions

1. **Still in File Manager**, select the WooSync folder

2. **Right-click → Permissions** (or click "Permissions" button)

3. **Set folder permissions:**
   - Folders: **755** (rwxr-xr-x)
   - Files: **644** (rw-r--r--)

4. **Apply recursively:**
   - Check "Recurse into subdirectories"
   - Click "Change Permissions"

---

## Part 3: Enabling the Plugin in FacturaScripts

Now that files are in place, enable the plugin:

1. **Open FacturaScripts admin**
   - Go to: `https://yourwebsite.com/facturascripts/`
   - Log in with admin credentials

2. **Go to Plugins page:**
   - Admin → Plugins
   - Or Administrador → Complementos (if Spanish)

3. **Find WooSync:**
   - Should appear in the list
   - Status: Disabled

4. **Click "Enable"**
   - Wait for page reload
   - Plugin is now active!

5. **If you see an error:**
   - Check FIX_INSTRUCTIONS.md
   - Most common issue: old INI file cached
   - Solution: Force refresh (Ctrl+F5)

---

## Part 4: Updating the Plugin (When New Versions Released)

When updates are available on GitHub:

### Step 4.1: Pull Updates in cPanel

1. **Go to cPanel → Git Version Control**

2. **Find your WooSync repository**

3. **Click "Manage"**

4. **Click "Pull or Deploy"** button
   - Or look for "Update" or "Sync" button
   - This downloads the latest changes from GitHub

5. **cPanel will pull the changes:**
   - Shows what files were updated
   - Takes a few seconds

### Step 4.2: Copy Updated Files

**Option 1: Manual Copy (Safest)**

1. Open File Manager

2. Compare files in:
   - Source: `/home/yourusername/WooSync-repo/`
   - Destination: `/public_html/facturascripts/Plugins/WooSync/`

3. Copy updated files from Git repo to Plugins folder

4. Overwrite existing files

**Option 2: Automated with Symlinks (Advanced)**

Instead of copying, you can create symbolic links:

⚠️ **Warning:** Not all shared hosting allows symlinks!

```bash
# In cPanel Terminal or SSH (if available)
cd /home/yourusername/public_html/facturascripts/Plugins/
rm -rf WooSync
ln -s /home/yourusername/WooSync-repo WooSync
```

This makes the Plugins/WooSync folder point directly to the Git repository.

**Benefits:**
- Automatic updates (no manual copying)
- Git pull automatically updates plugin

**Drawbacks:**
- Exposes .git folder (could be security risk)
- Not all shared hosting supports symlinks
- May not work with cPanel file permissions

### Step 4.3: Clear FacturaScripts Cache

After updating:

1. In FacturaScripts admin panel

2. Go to: Admin → Tools → Clear Cache
   - Or look for cache clearing option

3. Refresh your browser (Ctrl+F5)

4. Check that plugin still works

---

## Part 5: Troubleshooting

### Problem: "Permission denied" when pulling updates

**Solution:**
1. In cPanel Git Management page
2. Check repository credentials
3. Make sure you have read access to GitHub repo
4. If repository is private, you need to set up SSH keys or access tokens

### Problem: Changes don't appear after Git pull

**Cause:** Files not copied from Git directory to Plugins folder

**Solution:**
1. Verify files in Git directory are updated
2. Manually copy updated files to Plugins folder
3. Check file timestamps to confirm they're new

### Problem: Plugin breaks after update

**Solution:**
1. Check what changed in the update (GitHub commits)
2. Look for errors in Admin → Logs
3. Re-download and re-upload specific files
4. Check file permissions (755 for folders, 644 for files)

### Problem: Cannot find Git Version Control in cPanel

**Cause:** Not all shared hosting enables Git

**Solution:**
1. Contact your hosting provider
2. Ask them to enable "Git Version Control" feature
3. Alternative: Use FTP to manually upload files (see DEPLOYMENT_GUIDE.md)

### Problem: Branch doesn't update even after pull

**Cause:** May be on wrong branch

**Solution:**
1. In Git Management page, check "Current Branch"
2. Should be: **main**
3. If different, switch to `main` branch
4. Then pull again

---

## Part 6: Best Practices

### Regular Updates

- ✅ Check GitHub for updates weekly
- ✅ Pull updates in cPanel Git
- ✅ Copy to Plugins folder
- ✅ Test in FacturaScripts
- ✅ Clear cache after updates

### Backup Before Updates

Before updating:
1. Backup your current WooSync folder
2. Backup FacturaScripts database (just in case)
3. Can use cPanel Backup feature

### Monitor Logs

After updates:
- Check Admin → Logs in FacturaScripts
- Look for WooSync errors
- Address any issues immediately

---

## Part 7: Alternative: Deploy Script (Advanced)

If you have SSH/Terminal access, you can create a deployment script.

### Create deploy.sh script:

```bash
#!/bin/bash
# WooSync Deployment Script

# Paths (CHANGE THESE!)
GIT_DIR="/home/yourusername/WooSync-repo"
PLUGIN_DIR="/home/yourusername/public_html/facturascripts/Plugins/WooSync"

# Pull latest changes
cd "$GIT_DIR"
git pull origin main

# Copy files (excluding .git)
rsync -av --exclude='.git' --exclude='.gitignore' "$GIT_DIR/" "$PLUGIN_DIR/"

# Set permissions
find "$PLUGIN_DIR" -type d -exec chmod 755 {} \;
find "$PLUGIN_DIR" -type f -exec chmod 644 {} \;

echo "Deployment complete!"
```

### To use:

```bash
# Make executable
chmod +x deploy.sh

# Run whenever you want to update
./deploy.sh
```

**Note:** This requires SSH access, which many shared hosts don't provide.

---

## Summary: Quick Reference

**Initial Setup (One-time):**
1. cPanel → Git Version Control → Create
2. Repository: `https://github.com/yevea/WooSync`
3. Switch to `main` branch
4. Copy files from Git dir to Plugins/WooSync/
5. Enable plugin in FacturaScripts

**Updating (Recurring):**
1. cPanel → Git Version Control → Manage
2. Click "Pull/Update"
3. Copy updated files to Plugins folder
4. Clear FacturaScripts cache
5. Test plugin functionality

**Paths to Remember:**
- Git repository: `/home/yourusername/WooSync-repo/`
- Plugin location: `/public_html/facturascripts/Plugins/WooSync/`
- GitHub repo: `https://github.com/yevea/WooSync`
- Branch to use: **main**

---

## Need More Help?

- **Initial installation:** See DEPLOYMENT_GUIDE.md
- **INI file errors:** See FIX_INSTRUCTIONS.md
- **General usage:** See QUICK_START.md
- **GitHub issues:** https://github.com/yevea/WooSync/issues

---

**Version:** 2.0  
**Last Updated:** 2024  
**For:** cPanel Git Version Control users on shared hosting
