# WooSync - Frequently Asked Questions (FAQ)

## General Questions

### ❓ Is this just a PDF plugin?

**No.** WooSync is **primarily a WooCommerce synchronization plugin** for FacturaScripts. The Presupuesto PDF customization is a bonus feature added to enhance the plugin.

### ❓ What is the main purpose of WooSync?

The main purpose is to **synchronize data between WooCommerce and FacturaScripts**:
- Products (productos)
- Customers (clientes)
- Orders (pedidos)
- Stock (optional)

### ❓ What does the PDF customization do?

As a bonus feature, the plugin includes a custom PDF template for Presupuesto (Budget/Quote) documents that creates a more compact layout by displaying totals as rows at the bottom of the article table instead of at the page bottom.

### ❓ Can I use this plugin just for the PDF customization?

Yes, you can. Even if you don't use the WooCommerce sync features, the Presupuesto PDF customization will work. However, the plugin was designed primarily for WooCommerce synchronization.

---

## Feature Questions

### ❓ What data gets synchronized from WooCommerce?

The plugin syncs:
- **Products**: Product information, SKUs, prices
- **Customers**: Customer details from orders (name, email, address)
- **Orders**: Order data including line items
- **Stock**: Optional inventory synchronization

### ❓ Is the synchronization automatic?

The plugin includes:
- **Manual sync**: "Sincronizar ahora" button in the admin panel
- **Auto-sync**: Can be configured to run periodically (every 15 minutes via cron)

### ❓ What happens to the PDF customization if I disable the plugin?

If you disable the WooSync plugin, the custom Presupuesto PDF template will no longer be active, and FacturaScripts will revert to the default PDF layout.

---

## Technical Questions

### ❓ What versions are required?

- **FacturaScripts**: 2025.71 or higher
- **PHP**: 7.4 or higher
- **WooCommerce**: 10.4.3 (tested, other versions should work)
- **WordPress**: 6.9 (tested, other versions should work)

### ❓ Do I need CLI access?

No, the plugin works on shared hosting without CLI access.

### ❓ What WooCommerce API credentials do I need?

You need:
1. WooCommerce REST API Consumer Key
2. WooCommerce REST API Consumer Secret

These can be generated in: WooCommerce → Settings → Advanced → REST API

### ❓ Does it work with other FacturaScripts plugins?

Yes, WooSync is designed to work alongside other FacturaScripts plugins. The PDF customization only affects Presupuesto documents.

---

## Development Questions

### ❓ What is the development status?

- **WooCommerce Sync**: 🚧 In Development (core functionality being implemented)
- **Order Sync**: ✅ Implemented
- **Customer Sync**: ✅ Implemented
- **Presupuesto PDF**: ✅ Complete

### ❓ Can I customize the PDF template further?

Yes! The PDF template is located in `XMLView/Presupuesto.xml`. You can modify it to suit your needs. See `XMLView/README_PRESUPUESTO.md` for technical details.

### ❓ Can I contribute to the project?

This is an early-stage project by Martin (carpenter, noob coder). Contributions and suggestions are welcome!

---

## Troubleshooting

### ❓ The PDF customization isn't working

1. Clear FacturaScripts cache: Admin Panel → Tools → Clear Cache
2. Disable and re-enable the WooSync plugin
3. Verify `XMLView/Presupuesto.xml` exists in the plugin folder
4. Check that FacturaScripts version is 2025.71+

### ❓ WooCommerce sync isn't working

1. Verify WooCommerce REST API is enabled
2. Check Consumer Key and Secret are correct
3. Ensure your server can make HTTPS requests to WooCommerce
4. Check FacturaScripts logs for error messages

### ❓ Where can I find more help?

Documentation:
- `README.md` - Main plugin overview
- `INSTALLATION.md` - Installation instructions
- `PRESUPUESTO_LAYOUT_GUIDE.md` - PDF template details
- `XMLView/README_PRESUPUESTO.md` - Technical PDF documentation

---

## Summary

**WooSync is a dual-purpose plugin:**

| Purpose | Description | Status |
|---------|-------------|--------|
| **Primary** | WooCommerce → FacturaScripts synchronization | 🚧 In Development |
| **Bonus** | Custom compact Presupuesto PDF layout | ✅ Complete |

The plugin is designed to solve the problem of keeping WooCommerce and FacturaScripts data in sync, with a nice PDF customization included as an extra feature.
