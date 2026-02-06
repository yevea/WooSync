# Changelog

All notable changes to the WooSync plugin will be documented in this file.

## [2.0.0] - 2024

### Complete Rebuild
This version is a complete rebuild of the WooSync plugin with a focus on reliability, completeness, and shared hosting compatibility.

### Added
- **Reliable Settings Storage**: New key-value database table (`woosync_settings`) for storing configuration
- **Comprehensive Sync Services**: 
  - ProductSyncService - Sync products with SKU matching
  - CustomerSyncService - Sync customers with email matching
  - OrderSyncService - Sync orders with duplicate prevention
  - StockSyncService - Update stock quantities
  - TaxSyncService - Import tax rates and classes
- **Enhanced Admin UI**: 
  - Individual sync buttons for each entity type
  - Clear status messages and error handling
  - Connection test functionality
  - Sync All button for full synchronization
- **Comprehensive Documentation**: 
  - Detailed README with setup instructions
  - Troubleshooting guide
  - Data mapping documentation
  - Shared hosting constraints documented
- **Logging System**: 
  - All sync operations logged to database
  - Filterable by type (product, customer, order, stock, tax)
  - Multiple log levels (INFO, WARNING, ERROR, DEBUG)

### Changed
- Settings now use a reliable key-value storage system instead of Tools::settings()
- WooCommerceAPI now uses WooSyncConfig model for credentials
- All sync operations return detailed results (synced, errors, skipped counts)
- Models now properly implement install() methods for table creation
- Enhanced error handling throughout the codebase

### Fixed
- Settings persistence issues (writes and reads now work reliably)
- Database table creation during plugin installation
- API authentication handling
- Order duplicate prevention

### Technical Details
- Compatible with FacturaScripts 2025.71+
- Compatible with WooCommerce 10.4.3+ / WordPress 6.9+
- Works on shared hosting without CLI access
- PHP 7.4+ required
- Uses WooCommerce REST API v3

## [1.1.0] - 2024

### Initial Version
- Basic WooCommerce to FacturaScripts sync functionality
- Simple product and order logging
- Configuration page for API credentials

### Known Issues (Fixed in 2.0)
- Settings not persisting properly
- Limited sync functionality
- No comprehensive error handling
- Missing documentation
