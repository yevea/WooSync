# WooSync Admin Interface Overview

## Main Configuration Page

Location: **Admin → WooSync Configuration** in FacturaScripts

### Configuration Form

The form includes:
- **WooCommerce URL**: Your WooCommerce store URL (e.g., https://yourstore.com)
- **Consumer Key**: From WooCommerce REST API settings
- **Consumer Secret**: From WooCommerce REST API settings
- **Save Settings Button**: Stores credentials in database

### Quick Actions (shown when configured)

Two main action buttons:
1. **Test Connection**: Verifies credentials work with WooCommerce API
2. **Sync All**: Runs complete sync (taxes → products → customers → orders → stock)

### Individual Sync Operations

Five sync cards displayed in a grid:

#### 1. Taxes Card
- Icon: Percentage symbol
- Description: Import tax rates from WooCommerce
- Action: "Sync Taxes" button

#### 2. Products Card
- Icon: Cube
- Description: Sync products and prices
- Action: "Sync Products" button

#### 3. Customers Card
- Icon: Users
- Description: Import customer data
- Action: "Sync Customers" button

#### 4. Orders Card
- Icon: Shopping cart
- Description: Import WooCommerce orders
- Action: "Sync Orders" button

#### 5. Stock Card
- Icon: Boxes
- Description: Update stock levels
- Action: "Sync Stock" button

### Status Indicators

- **Badge**: Shows "Configured ✓" (green) or "Not configured" (yellow)
- **Success Messages**: Green alert with checkmark icon
- **Error Messages**: Red alert with exclamation icon

### Synchronization Notes

Info box at the bottom explains:
- One-way sync direction (WooCommerce → FacturaScripts)
- Recommended sequence for initial setup
- Individual sync for updates
- All operations are logged

## User Experience Flow

### First Time Setup
1. User opens WooSync Configuration
2. Sees "Not configured" status
3. Fills in WooCommerce credentials
4. Clicks "Save Settings"
5. Sees "Configured ✓" badge
6. Clicks "Test Connection"
7. Sees success message
8. Clicks "Sync All"
9. Waits 30-60 seconds
10. Sees success with counts

### Daily Usage
1. User opens WooSync Configuration
2. Sees "Configured ✓" status
3. Clicks individual sync button (e.g., "Sync Orders")
4. Waits a few seconds
5. Sees success message with results

## Technical Details

### Form Validation
- JavaScript validates URL format (must start with https://)
- Checks all fields are filled
- Shows loading spinner during save

### Button Behavior
- Sync All has confirmation dialog
- Individual sync buttons redirect to same page with results
- Test Connection shows immediate feedback

### URL Parameters
- `?saved=1` - Shows "Settings saved successfully"
- `?success=...` - Shows custom success message
- `?error=...` - Shows custom error message

### Responsive Design
- Works on desktop and tablet
- Cards stack on mobile devices
- Bootstrap 4 grid system

## Color Scheme

- Primary: Blue (#007bff) - main buttons and headings
- Success: Green (#28a745) - success messages and badges
- Danger: Red (#dc3545) - error messages
- Info: Light blue (#17a2b8) - info messages
- Warning: Yellow (#ffc107) - warning messages

## Icons (Font Awesome)

- Sync: `fa-sync-alt`
- Test: `fa-plug`
- Products: `fa-cube`
- Customers: `fa-users`
- Orders: `fa-shopping-cart`
- Stock: `fa-boxes`
- Taxes: `fa-percentage`
- Success: `fa-check-circle`
- Error: `fa-exclamation-circle`
- Info: `fa-info-circle`

## Accessibility

- Form labels properly associated with inputs
- Required fields marked with asterisk
- Error messages clearly visible
- Success messages dismissible
- Buttons have descriptive text

## Browser Compatibility

- Chrome/Edge: Full support
- Firefox: Full support
- Safari: Full support
- IE11: Basic support (no longer recommended)
