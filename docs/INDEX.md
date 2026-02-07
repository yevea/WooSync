# WooSync Plugin - Documentation Index

**Complete Documentation for the WooSync Plugin v2.0**

Welcome! This plugin syncs data from WooCommerce to FacturaScripts on shared hosting environments.

---

## 🚀 Quick Links - Start Here

**Never used GitHub before?**
→ Read [GITHUB_GUIDE.md](GITHUB_GUIDE.md) first!

**Using cPanel Git Version Control?** ⭐ NEW
→ Read [CPANEL_DEPLOYMENT.md](CPANEL_DEPLOYMENT.md) - Deploy from GitHub automatically!

**Ready to install manually?**
→ Read [DEPLOYMENT_GUIDE.md](DEPLOYMENT_GUIDE.md) - Complete step-by-step instructions!

**Already installed and just need a reminder?**
→ Read [QUICK_START.md](QUICK_START.md) - 5-minute reference guide

**Want technical details?**
→ Read [README.md](README.md) - Full technical documentation

---

## 📚 All Available Documentation

### For Installation & Setup

1. **[GITHUB_GUIDE.md](GITHUB_GUIDE.md)** (6KB)
   - **For:** Complete GitHub beginners
   - **Learn:** How to download from GitHub
   - **Time:** 5 minutes
   - **Covers:**
     - What is GitHub?
     - How to find the correct branch
     - How to download as ZIP
     - Alternative methods
     - Troubleshooting download issues

2. **[CPANEL_DEPLOYMENT.md](CPANEL_DEPLOYMENT.md)** (11KB) ⭐ **NEW - FOR CPANEL USERS**
   - **For:** Users with cPanel Git Version Control
   - **Learn:** Automatic deployment from GitHub
   - **Time:** 20 minutes
   - **Covers:**
     - Setting up cPanel Git
     - Connecting to GitHub repository
     - Deploying to FacturaScripts
     - Automatic updates
     - Branch management (main vs copilot branch)
     - Troubleshooting cPanel Git issues

3. **[DEPLOYMENT_GUIDE.md](DEPLOYMENT_GUIDE.md)** (22KB) ⭐ **START HERE (MANUAL)**
   - **For:** Installing and configuring the plugin
   - **Learn:** Complete installation process
   - **Time:** 15-20 minutes
   - **Covers:**
     - Download from GitHub
     - Upload to server (FTP/cPanel)
     - Enable plugin
     - Configure WooCommerce API
     - Configure WooSync
     - First sync
     - Verification
     - Daily usage
     - Troubleshooting

4. **[FIX_INSTRUCTIONS.md](FIX_INSTRUCTIONS.md)** (5KB)
   - **For:** Fixing INI file parsing errors
   - **Learn:** How to resolve installation errors
   - **Time:** 3 minutes
   - **Covers:**
     - INI file syntax error fix
     - Three different fix options
     - Verification steps
     - What to do after fix

5. **[QUICK_START.md](QUICK_START.md)** (2KB)
   - **For:** Quick reference after installation
   - **Learn:** Fast setup recap
   - **Time:** 5 minutes
   - **Covers:**
     - 4 installation steps
     - First sync
     - Daily usage
     - Common problems

### For Technical Details

4. **[README.md](README.md)** (9KB)
   - **For:** Technical users and developers
   - **Learn:** How the plugin works
   - **Covers:**
     - Features overview
     - System requirements
     - Data mapping (WooCommerce ↔ FacturaScripts)
     - API integration details
     - Sync logic
     - Limitations
     - Advanced troubleshooting

5. **[UI_OVERVIEW.md](UI_OVERVIEW.md)** (4KB)
   - **For:** Understanding the admin interface
   - **Learn:** What each button does
   - **Covers:**
     - Configuration form
     - Quick action buttons
     - Individual sync operations
     - Status indicators
     - User flow
     - Form validation

### For Security & History

6. **[SECURITY.md](SECURITY.md)** (5KB)
   - **For:** Security-conscious administrators
   - **Learn:** Security measures implemented
   - **Covers:**
     - Input validation
     - Database security
     - API security
     - Authentication
     - Data sanitization
     - Error handling
     - Best practices

7. **[CHANGELOG.md](CHANGELOG.md)** (2KB)
   - **For:** Tracking what changed
   - **Learn:** Version history
   - **Covers:**
     - Version 2.0 (complete rebuild)
     - Version 1.1 (initial version)
     - What's new
     - What's fixed

---

## 📖 Reading Order

### If you're NEW to GitHub and WooSync:

**Read in this order:**

1. **[GITHUB_GUIDE.md](GITHUB_GUIDE.md)** - Learn how to download
2. **[DEPLOYMENT_GUIDE.md](DEPLOYMENT_GUIDE.md)** - Follow step-by-step
3. **[QUICK_START.md](QUICK_START.md)** - Keep as reference

**Time needed:** ~30 minutes total

### If you're TECHNICAL and want details:

**Read in this order:**

1. **[README.md](README.md)** - Understand architecture
2. **[SECURITY.md](SECURITY.md)** - Review security
3. **[DEPLOYMENT_GUIDE.md](DEPLOYMENT_GUIDE.md)** - Install
4. **[UI_OVERVIEW.md](UI_OVERVIEW.md)** - Learn interface

**Time needed:** ~45 minutes total

### If you've ALREADY INSTALLED it:

**Just reference:**

1. **[QUICK_START.md](QUICK_START.md)** - Daily operations
2. **[DEPLOYMENT_GUIDE.md](DEPLOYMENT_GUIDE.md)** - Troubleshooting section

**Time needed:** 2 minutes as needed

---

## 🎯 Common Questions

### "Where do I start?"

→ Start with [DEPLOYMENT_GUIDE.md](DEPLOYMENT_GUIDE.md)

It includes everything you need from download to first sync.

### "I'm stuck downloading from GitHub"

→ Read [GITHUB_GUIDE.md](GITHUB_GUIDE.md)

It explains GitHub step-by-step with pictures descriptions.

### "I can't get it to connect"

→ See [DEPLOYMENT_GUIDE.md](DEPLOYMENT_GUIDE.md) → Troubleshooting section

Common connection problems are explained with solutions.

### "How do I use it daily?"

→ See [QUICK_START.md](QUICK_START.md) → Daily Usage

Quick reference for ongoing operations.

### "What data gets synced?"

→ See [README.md](README.md) → Data Mapping section

Complete tables showing what syncs and how.

### "Is it secure?"

→ See [SECURITY.md](SECURITY.md)

Full security review with measures implemented.

### "How does the interface work?"

→ See [UI_OVERVIEW.md](UI_OVERVIEW.md)

Detailed description of the admin interface.

---

## 🔧 What This Plugin Does

**Syncs from WooCommerce to FacturaScripts:**
- ✅ Products (name, SKU, price, description, stock)
- ✅ Customers (name, email, addresses, phone)
- ✅ Orders (order details, line items, customer info)
- ✅ Stock (quantities for products)
- ✅ Taxes (tax rates and classes)

**Important Notes:**
- ⚠️ **One-way sync only** (WooCommerce → FacturaScripts)
- ⚠️ **Manual sync** (no automatic scheduling on shared hosting)
- ⚠️ **Same server required** (WooCommerce and FacturaScripts together)
- ✅ **No CLI needed** (100% web-based)
- ✅ **Safe to run multiple times** (won't create duplicates)

---

## 💻 System Requirements

**Server:**
- PHP 7.4 or higher
- MySQL/MariaDB 5.7+ or 10.2+
- cURL enabled
- JSON enabled

**Software:**
- FacturaScripts 2025.71 or higher
- WooCommerce 10.4.3 or higher
- WordPress 6.9 or higher

**Hosting:**
- Shared hosting compatible
- No CLI/SSH access required
- Both WooCommerce and FacturaScripts on same server

---

## ⏱️ Time Estimates

**Installation:**
- Download from GitHub: 2 minutes
- Upload to server: 3 minutes
- Enable plugin: 1 minute
- Configure WooCommerce API: 4 minutes
- Configure WooSync: 2 minutes
- First sync: 1-2 minutes
- Verification: 2 minutes

**Total:** 15-20 minutes

**Daily use:**
- Open admin page: 10 seconds
- Click sync button: 5 seconds
- Wait for completion: 10-30 seconds
- Check results: 10 seconds

**Total:** ~1 minute per sync

---

## 📞 Getting Help

**Documentation not clear?**
- Read the specific guide for your question (see above)
- Check the Troubleshooting sections
- Read the logs in FacturaScripts: Admin → Logs

**Found a bug?**
- Go to: https://github.com/yevea/WooSync
- Click "Issues"
- Click "New Issue"
- Describe the problem with details

**Need technical support?**
- Check [README.md](README.md) first
- Check [SECURITY.md](SECURITY.md) for security questions
- Review [DEPLOYMENT_GUIDE.md](DEPLOYMENT_GUIDE.md) troubleshooting

---

## 📦 What's Included

**Code Files:**
- 12 PHP files (~1,640 lines)
- 1 Twig template (HTML)
- 1 XML database schema
- 1 INI configuration
- 1 Composer file

**Documentation:**
- 7 markdown guides (48KB total)
- Covers installation, usage, security, technical details

**Database Tables:**
- `woosync_settings` - Configuration storage
- `woosync_logs` - Activity logging

---

## 🎉 You're Ready!

**Next Steps:**

1. Read [GITHUB_GUIDE.md](GITHUB_GUIDE.md) if you're new to GitHub
2. Follow [DEPLOYMENT_GUIDE.md](DEPLOYMENT_GUIDE.md) to install
3. Use [QUICK_START.md](QUICK_START.md) as daily reference
4. Consult [README.md](README.md) for technical details

**Good luck with your WooSync installation!**

---

## 📝 Document Summary

| File | Size | Purpose | Audience |
|------|------|---------|----------|
| GITHUB_GUIDE.md | 6KB | GitHub basics | GitHub beginners |
| DEPLOYMENT_GUIDE.md | 20KB | Installation | Everyone (start here) |
| QUICK_START.md | 2KB | Quick reference | Daily users |
| README.md | 9KB | Technical docs | Technical users |
| UI_OVERVIEW.md | 4KB | Interface guide | All users |
| SECURITY.md | 5KB | Security info | Admins/IT |
| CHANGELOG.md | 2KB | Version history | All users |

**Total documentation:** 48KB across 7 guides

---

*WooSync v2.0 - Complete rebuild for reliability and ease of use*
*Compatible with FacturaScripts 2025.71+, WooCommerce 10.4.3+, WordPress 6.9+*
