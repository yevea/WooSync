# Quick Start Guide - WooSync

## Installation (5 minutes)

### 1. Install Plugin
1. Upload WooSync folder to: `/Plugins/WooSync/`
2. Login to FacturaScripts admin
3. Go to **Admin → Plugins**
4. Enable "WooSync"
5. Plugin creates database tables automatically

### 2. Get WooCommerce API Credentials
1. Login to WordPress admin
2. Go to **WooCommerce → Settings → Advanced → REST API**
3. Click **Add key**
4. Set permissions: **Read** (or Read/Write)
5. Click **Generate API Key**
6. **Copy** Consumer Key and Consumer Secret

### 3. Configure WooSync
1. In FacturaScripts, go to **Admin → WooSync Configuration**
2. Enter:
   - **URL**: `https://yourstore.com` (your WooCommerce URL)
   - **Consumer Key**: (from step 2)
   - **Consumer Secret**: (from step 2)
3. Click **Save Settings**
4. Click **Test Connection** to verify

### 4. First Sync
1. Click **Sync All** button
2. Wait for completion (may take 30-60 seconds)
3. Check results in success message
4. Verify data in FacturaScripts:
   - **Sales → Products** (productos)
   - **Sales → Customers** (clientes)
   - **Sales → Orders** (pedidos)

## Daily Usage

- **New Orders**: Click **Sync Orders**
- **Stock Updates**: Click **Sync Stock**
- **Product Changes**: Click **Sync Products**
- **New Customers**: Click **Sync Customers**

## Troubleshooting

### Connection Failed
- Check URL is `https://` not `http://`
- Verify REST API is enabled in WooCommerce
- Check credentials are correct

### Nothing Synced
- Products need SKU in WooCommerce
- Customers need email address
- Check logs: **Admin → Logs** filter by "WooSync"

### Timeout Errors
- Normal on shared hosting
- Use individual sync buttons instead of "Sync All"
- Sync during low-traffic hours

## Need Help?

See [README.md](README.md) for complete documentation.
