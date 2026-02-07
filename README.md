# WooSync - WooCommerce to FacturaScripts Synchronization Plugin

![Version](https://img.shields.io/badge/version-2.0-blue)
![FacturaScripts](https://img.shields.io/badge/FacturaScripts-2025.71-green)
![WooCommerce](https://img.shields.io/badge/WooCommerce-10.4.3-purple)
![WordPress](https://img.shields.io/badge/WordPress-6.9-blue)

## Overview

WooSync is a FacturaScripts plugin that enables **one-way synchronization** from WooCommerce to FacturaScripts. It's designed specifically for shared hosting environments where CLI access is not available.

### Key Features

- ✅ **One-way sync**: WooCommerce → FacturaScripts
- ✅ **No CLI required**: Works entirely through the web interface
- ✅ **Shared server compatible**: Designed for hosting environments without command-line access
- ✅ **Comprehensive sync**: Products, Customers, Orders, Stock, and Tax Rates
- ✅ **Reliable settings storage**: Custom database table for configuration
- ✅ **REST API integration**: Uses WooCommerce REST API v3
- ✅ **Detailed logging**: Track all synchronization activities

## What Gets Synchronized

| Entity | WooCommerce → FacturaScripts | Details |
|--------|------------------------------|---------|
| **Taxes** | ✅ | Tax rates and classes |
| **Products** | ✅ | Name, SKU, price, description, stock status |
| **Customers** | ✅ | Name, email, billing/shipping addresses |
| **Orders** | ✅ | Order details, line items, customer data |
| **Stock** | ✅ | Stock quantities for products |

## System Requirements

### Server Environment
- **PHP**: 7.4 or higher
- **MySQL/MariaDB**: 5.7+ / 10.2+
- **FacturaScripts**: 2025.71 or higher
- **WooCommerce**: 10.4.3+ (WordPress 6.9+)
- **cURL**: PHP extension enabled
- **JSON**: PHP extension enabled

### Hosting Constraints
- ✅ Works on **shared hosting** without CLI access
- ✅ No cronjob or command-line tools required
- ✅ All operations via web browser interface

## Installation

### Step 1: Install the Plugin

1. Download the WooSync plugin files
2. Upload to your FacturaScripts installation:
   ```
   /Plugins/WooSync/
   ```
3. Log in to FacturaScripts as administrator
4. Navigate to **Admin → Plugins**
5. Find "WooSync" and click **Enable**
6. The plugin will automatically create the required database tables

### Step 2: Configure WooCommerce REST API

1. Log in to your WordPress/WooCommerce admin panel
2. Navigate to **WooCommerce → Settings → Advanced → REST API**
3. Click **Add key** or **Create an API key**
4. Configure the API key:
   - **Description**: FacturaScripts Sync
   - **User**: Select an administrator user
   - **Permissions**: Read/Write or Read
5. Click **Generate API Key**
6. **Important**: Copy the **Consumer Key** and **Consumer Secret** immediately (they won't be shown again)

### Step 3: Configure WooSync in FacturaScripts

1. In FacturaScripts, navigate to **Admin → WooSync Configuration**
2. Enter your WooCommerce credentials:
   - **WooCommerce URL**: Your store URL (e.g., `https://yourstore.com`)
   - **Consumer Key**: From WooCommerce REST API settings
   - **Consumer Secret**: From WooCommerce REST API settings
3. Click **Save Settings**
4. Click **Test Connection** to verify the configuration

## Usage

### Initial Synchronization

For the first sync, it's recommended to use **Sync All** to import all data in the correct order:

1. Navigate to **Admin → WooSync Configuration**
2. Click **Sync All**
3. This will sync in order:
   - Taxes
   - Products
   - Customers
   - Orders
   - Stock levels

### Individual Sync Operations

After initial setup, you can sync individual entities:

- **Sync Taxes**: Import/update tax rates
- **Sync Products**: Import/update products and prices
- **Sync Customers**: Import/update customer data
- **Sync Orders**: Import new orders
- **Sync Stock**: Update stock quantities

### Sync Frequency

Since there's no CLI access, synchronization is **manual**:
- Click sync buttons in the WooSync Configuration page when needed
- Recommended: Sync orders daily
- Recommended: Sync products/stock when inventory changes
- Customers and taxes can be synced less frequently

## Data Mapping

### Products
| WooCommerce | FacturaScripts | Notes |
|-------------|----------------|-------|
| SKU | referencia | Primary identifier |
| Name | descripcion | Product name |
| Regular Price | precio | Base price |
| Stock Quantity | stockfis | Physical stock |
| Stock Status | bloqueado | Blocked if out of stock |
| Short Description | observaciones | Additional info |

### Customers
| WooCommerce | FacturaScripts | Notes |
|-------------|----------------|-------|
| Email | email | Primary identifier |
| First + Last Name | nombre | Full name |
| Billing Address | direccion | Street address |
| City | ciudad | City |
| State | provincia | State/Province |
| Postcode | codpostal | Postal code |
| Phone | telefono1 | Contact phone |
| Company | razonsocial | Business name |

### Orders
| WooCommerce | FacturaScripts | Notes |
|-------------|----------------|-------|
| Order Number | observaciones | Stored in notes |
| Customer | codcliente | Linked to customer |
| Line Items | LineaPedido | Order lines |
| Total | total | Order total |
| Status | editable | Based on WC status |

## Troubleshooting

### Connection Issues

**Error**: "Connection failed"
- Verify WooCommerce URL is correct (use `https://`)
- Check that REST API is enabled in WooCommerce
- Verify Consumer Key and Secret are correct
- Check SSL certificate is valid

**Error**: "Invalid JSON response"
- WooCommerce URL might be incorrect
- REST API might be disabled
- Check WooCommerce permalinks are set correctly

### Sync Issues

**Products not syncing**
- Products must have a SKU in WooCommerce
- Products without SKU are skipped

**Customers not syncing**
- Customers must have an email address
- Customers without email are skipped

**Orders not syncing**
- Customer must exist or be created first
- Orders without customer email are skipped
- Already synced orders are skipped (checked by WooCommerce ID)

### Viewing Logs

All sync operations are logged:
1. Check FacturaScripts logs: **Admin → Logs**
2. WooSync logs are stored in the `woosync_logs` table
3. Filter by type: `product`, `customer`, `order`, `stock`, `tax`

### Common Solutions

1. **Settings not saving**
   - The new version uses a reliable key-value storage system
   - Settings are stored in the `woosync_settings` table
   - If issues persist, check database permissions

2. **Slow sync on shared hosting**
   - This is normal for shared hosting limitations
   - Sync smaller batches by running individual sync operations
   - Consider syncing during low-traffic hours

3. **Timeout errors**
   - Increase PHP max_execution_time (ask hosting provider)
   - Sync entities individually instead of "Sync All"
   - Reduce batch sizes (products default: 50 per page)

## Technical Details

### Database Tables

**woosync_settings**
```sql
- id: INT (Primary Key)
- setting_key: VARCHAR(255) UNIQUE
- setting_value: TEXT
- updated_at: TIMESTAMP
```

**woosync_logs**
```sql
- id: INT (Primary Key)
- message: TEXT
- level: VARCHAR(10) [INFO, WARNING, ERROR, DEBUG]
- date: TIMESTAMP
- type: VARCHAR(50) [product, customer, order, stock, tax]
- reference: VARCHAR(100)
```

### API Integration

- Uses WooCommerce REST API v3
- Endpoints: `/wp-json/wc/v3/`
- Authentication: OAuth 1.0a (query string)
- HTTP method: GET (read-only operations)

### Sync Logic

1. **Products**: Match by SKU, create if new, update if exists
2. **Customers**: Match by email, generate code if new
3. **Orders**: Check if already synced by WooCommerce ID
4. **Stock**: Update quantities for existing products only
5. **Taxes**: Match by rate and class, create/update

## Limitations

### By Design
- **One-way sync only**: FacturaScripts changes are NOT synced back to WooCommerce
- **Manual sync**: No automatic scheduling (shared hosting limitation)
- **No deletion sync**: Deleted items in WooCommerce are not deleted in FacturaScripts

### Technical Constraints
- Shared hosting may have timeout limits (typically 30-60 seconds)
- Large catalogs may need to be synced in batches
- No CLI means no background processing or cron jobs

## Support and Development

### Repository
GitHub: [yevea/WooSync](https://github.com/yevea/WooSync)

### Version History
- **v2.0** (2024): Complete rebuild with reliable settings storage and comprehensive sync
- **v1.1** (2024): Initial version with basic functionality

### Contributing
This is an open-source project. Contributions, issues, and feature requests are welcome.

## License

This plugin is provided as-is for use with FacturaScripts and WooCommerce.

## Credits

Developed for shared hosting environments where FacturaScripts and WooCommerce run on the same server without CLI access.

---

**Questions?** Check the logs first, then review this documentation. Most issues can be resolved by:
1. Verifying WooCommerce REST API credentials
2. Checking server PHP error logs
3. Reviewing WooSync logs in the database
