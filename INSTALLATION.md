# WooSync Plugin Installation Guide

## Quick Start

### 1. Installation
Copy the WooSync plugin folder to your FacturaScripts installation:
```
FacturaScripts/
  Plugins/
    WooSync/           ← Copy here
      XMLView/
        Presupuesto.xml
      Controller/
      Lib/
      Model/
      View/
      ...
```

### 2. Enable Plugin
1. Log into FacturaScripts admin panel
2. Go to: Admin Panel → Plugins
3. Find "WooSync" in the list
4. Click "Enable" button
5. Wait for plugin activation to complete

### 3. Verify PDF Template
The custom Presupuesto PDF template is automatically active once the plugin is enabled.

To test:
1. Go to Sales → Presupuestos (Budgets/Quotes)
2. Create a new Presupuesto or open existing one
3. Add at least 2-3 articles
4. Click "PDF" button to generate
5. Verify the layout:
   - ✅ Totals appear directly below article table
   - ✅ No empty space between articles and totals
   - ✅ Neto, Impuestos, Total shown as table rows

## Custom PDF Template Details

### What Changed
The Presupuesto PDF now uses a compact layout where:
- **Neto** (Net amount) appears as a row in the table
- **Impuestos** (Taxes) appears as a row in the table  
- **Total** appears as a row in the table

This eliminates the empty space that previously appeared between the article listing and the totals section.

### Template Location
`Plugins/WooSync/XMLView/Presupuesto.xml`

### Compatibility
- FacturaScripts 2025.71 or higher
- PHP 7.4 or higher
- Works with all standard Presupuesto features
- Compatible with discounts and multiple tax rates

## WooCommerce Sync Configuration

### 1. Configure WooCommerce Connection
1. Go to: Admin Panel → WooSync Configuration
2. Enter your WooCommerce details:
   - URL: Your WooCommerce store URL (e.g., https://mystore.com)
   - Consumer Key: From WooCommerce → Settings → Advanced → REST API
   - Consumer Secret: From WooCommerce → Settings → Advanced → REST API
3. Click "Save Settings"

### 2. Test Connection
1. Click "Test Connection" button
2. Verify you see "✅ Connection successful"

### 3. Sync Data
1. Click "Sync All" to synchronize:
   - Products
   - Customers
   - Orders
   - Stock (optional)

## Troubleshooting

### PDF Template Not Applying
1. Clear FacturaScripts cache:
   - Admin Panel → Tools → Clear Cache
2. Disable and re-enable WooSync plugin
3. Verify file exists: `Plugins/WooSync/XMLView/Presupuesto.xml`
4. Check FacturaScripts version is 2025.71+

### WooCommerce Connection Issues
1. Verify WooCommerce REST API is enabled
2. Check Consumer Key and Secret are correct
3. Ensure your server can make HTTPS requests
4. Check WooCommerce permissions for API user

### General Issues
1. Check PHP error logs
2. Verify FacturaScripts log files
3. Ensure plugin files are properly uploaded
4. Check file permissions (should be readable by web server)

## Support

For issues or questions:
1. Check the documentation files:
   - `README.md` - Main plugin documentation
   - `PRESUPUESTO_LAYOUT_GUIDE.md` - PDF layout details
   - `XMLView/README_PRESUPUESTO.md` - Technical details
2. Review FacturaScripts logs for errors
3. Open an issue on GitHub repository

## Updates

To update the plugin:
1. Backup your current installation
2. Download new version
3. Replace plugin files
4. Clear FacturaScripts cache
5. Test functionality

---

**Plugin Version**: 1.1
**FacturaScripts Required**: 2025.71+
**PHP Required**: 7.4+
