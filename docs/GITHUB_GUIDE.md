# GitHub Interface Guide for Beginners

**How to Download the WooSync Plugin from GitHub**

This guide shows you exactly what to look for on GitHub's website.

---

## What is GitHub?

GitHub is a website where programmers store and share code. Think of it like Google Drive, but for software projects.

**You don't need an account** to download the plugin!

---

## Visual Guide: Downloading the Plugin

### Step 1: Go to the GitHub Page

**Open your browser and type:**
```
https://github.com/yevea/WooSync
```

**Press Enter**

You'll see a page that looks like a file directory.

---

### Step 2: Find the Branch Selector

**Look at the top-left area of the page**

You'll see a button that says something like:
```
🌿 main    ▼
```
or
```
🌿 master  ▼
```

**This is the "branch" selector**

Think of branches like different versions of the code. The new WooSync plugin is in a specific branch.

---

### Step 3: Click on the Branch Selector

**Click on that button**

A dropdown menu will appear showing all available branches:
```
Branches:
  ○ main
  ○ copilot/create-woosync-plugin  ← YOU WANT THIS ONE!
```

---

### Step 4: Select the Correct Branch

**Click on:**
```
copilot/create-woosync-plugin
```

The page will reload, and now you'll see the new plugin files!

**You'll know you're on the right branch because:**
- The branch selector now shows: `copilot/create-woosync-plugin`
- You'll see folders like: Controller, DataBase, Lib, Model, View
- You'll see files like: README.md, DEPLOYMENT_GUIDE.md

---

### Step 5: Download as ZIP

**Look for a green button that says "Code"**

It's usually on the right side of the page, above the file list.

**Click on the green "Code" button**

A small menu will appear with options:
```
Clone:
  HTTPS
  SSH
  GitHub CLI

📥 Download ZIP  ← CLICK THIS!
```

**Click "Download ZIP"**

---

### Step 6: Save the ZIP File

Your browser will download a file named something like:
```
WooSync-copilot-create-woosync-plugin.zip
```

**Save it to your computer**
- Desktop is a good place
- Or Downloads folder

---

### Step 7: Extract the ZIP File

**Windows:**
1. Right-click on the ZIP file
2. Choose "Extract All..."
3. Click "Extract"

**Mac:**
1. Double-click the ZIP file
2. It extracts automatically

**You'll get a folder** containing all the plugin files.

---

## What You Should See After Extracting

Inside the extracted folder, you should see:

```
WooSync-copilot-create-woosync-plugin/
├── 📁 Controller/
├── 📁 DataBase/
├── 📁 Lib/
├── 📁 Model/
├── 📁 View/
├── 📄 CHANGELOG.md
├── 📄 DEPLOYMENT_GUIDE.md
├── 📄 README.md
├── 📄 QUICK_START.md
├── 📄 SECURITY.md
├── 📄 UI_OVERVIEW.md
├── 📄 WooSync.php
├── 📄 composer.json
├── 📄 facturascripts.ini
├── 📄 init.php
└── 📄 .gitignore
```

**These are the plugin files!**

---

## Next Steps

Now that you have the files:

1. **Go back to DEPLOYMENT_GUIDE.md**
2. **Continue from "Step 2: Upload to Your Server"**
3. **Follow the rest of the instructions**

---

## Alternative: Using GitHub Desktop (Optional)

If you want an easier way to manage GitHub projects:

1. **Download GitHub Desktop:**
   ```
   https://desktop.github.com/
   ```

2. **Install it** on your computer

3. **Click "Clone a repository"**

4. **Enter:**
   ```
   https://github.com/yevea/WooSync
   ```

5. **Choose where to save it** on your computer

6. **Click "Clone"**

7. **In GitHub Desktop, click the "Current Branch" dropdown**

8. **Select:**
   ```
   copilot/create-woosync-plugin
   ```

9. **The files are now on your computer!**
   - You can open the folder from GitHub Desktop
   - Look in: File → Show in Finder (Mac) or Show in Explorer (Windows)

---

## Understanding GitHub Terms

**Branch:**
- Like different versions of the project
- `main` or `master` = original version
- `copilot/create-woosync-plugin` = new version with rebuilt plugin

**Clone:**
- Download a complete copy of the project
- Keeps connection to GitHub for updates

**Download ZIP:**
- Simple download without GitHub connection
- Easiest for one-time installation

**Pull Request:**
- A proposed change to the project
- Don't worry about this - you just need to download

**Commit:**
- A saved change to the code
- Like a save point in a video game
- You can see the history of commits

**Repository (Repo):**
- The project folder on GitHub
- Contains all the code and files

---

## Troubleshooting

### "I don't see the branch selector"

- Make sure you're on `github.com/yevea/WooSync`
- Look at the top-left, just above the file list
- It might say "main" or "master"
- If you still don't see it, try refreshing the page

### "I don't see copilot/create-woosync-plugin in the branch list"

- Make sure you're on the correct repository
- Try refreshing the page
- The branch might have been merged - check the commits

### "The green Code button doesn't appear"

- Make sure you've selected the correct branch first
- Try refreshing the page
- Make sure JavaScript is enabled in your browser

### "Download ZIP option is grayed out"

- This shouldn't happen
- Try using a different browser (Chrome, Firefox)
- Clear your browser cache

### "The ZIP file won't extract"

- Make sure the download completed fully
- Try downloading again
- Use a different extraction tool (7-Zip, WinRAR)

---

## Need More Help?

**For GitHub-specific questions:**
- Visit: https://docs.github.com/en/get-started
- GitHub's help documentation is very beginner-friendly

**For WooSync installation:**
- See DEPLOYMENT_GUIDE.md (complete instructions)
- See QUICK_START.md (quick reference)
- See README.md (technical details)

---

## Summary: Quick Steps

For those who want just the essentials:

1. Go to: `https://github.com/yevea/WooSync`
2. Click branch selector (top-left)
3. Choose: `copilot/create-woosync-plugin`
4. Click green "Code" button
5. Click "Download ZIP"
6. Extract the ZIP file
7. Upload files to your server
8. Follow DEPLOYMENT_GUIDE.md

**That's it!**

---

*This is a beginner-friendly guide. If you're already familiar with GitHub, see README.md for technical documentation.*
