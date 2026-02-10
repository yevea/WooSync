# WooSync - WooCommerce Synchronization Plugin for FacturaScripts

## 🎯 What is WooSync?

**WooSync is a WooCommerce synchronization plugin for FacturaScripts**, NOT just a PDF customization plugin.

### Primary Purpose: WooCommerce Sync
This plugin's main objective is to synchronize data between WooCommerce and FacturaScripts:
- **Products** (productos)
- **Customers** (clientes)  
- **Orders** (pedidos)
- **Stock** (optional)

### Bonus Feature: PDF Customization
As an added benefit, this plugin also includes a custom Presupuesto PDF template with a compact layout.

---

## 📋 Core Functionality: WooCommerce Synchronization

Hi I am Martin, carpenter, noob coder. This is my project in its early stage, i.e. not finished. 
The final objective is to create a functional plugin for FacturaScripts (facturascripts.com) 
which syncs products, stock, clients, and orders from WooCommerce into FacturaScripts.

### Current Status
- **WooCommerce Version**: 10.4.3
- **WordPress Version**: 6.9
- **FacturaScripts Version**: 2025.71
- **Environment**: Shared server (no CLI access)
- **Development Status**: In progress - sync functionality being implemented

### Configuration Required
- **WooCommerce URL**: Your WooCommerce store URL
- **Consumer Key**: From WooCommerce REST API settings
- **Consumer Secret**: From WooCommerce REST API settings

### Manual Sync Button
"Sincronizar ahora" (Synchronize now)

### What Gets Synchronized
```
WooCommerce              →  FacturaScripts
─────────────────────────────────────────
productos (products)     →  productos
clientes (customers)     →  clientes
pedidos (orders)         →  pedidos
stock                    →  stock (optional)
```

### Synchronization Flow
```
FacturaScripts
   ↓ (PHP)
Calls WooCommerce API
   ↓
Receives JSON data
   ↓
Creates / Updates:
   - clientes (customers)
   - productos (products + stock)
   - pedidos (orders)
```

---

## 🎁 Bonus Feature: PDF Customizations

In addition to WooCommerce synchronization, this plugin includes a custom PDF template.

### Presupuesto PDF Layout
A custom PDF template for Presupuesto (Budget/Quote) documents with a more compact layout.

**Changes:**
- Neto (Net), Impuestos (Taxes), and Total are now displayed as rows at the bottom of the article table
- Eliminates empty space between the article listing and totals section
- Creates a more professional and compact document layout

**File:** `XMLView/Presupuesto.xml`

The custom template automatically applies when generating Presupuesto PDFs in FacturaScripts 2025.71+.

**Documentation:**
- See [PRESUPUESTO_LAYOUT_GUIDE.md](PRESUPUESTO_LAYOUT_GUIDE.md) for visual comparison
- See [INSTALLATION.md](INSTALLATION.md) for setup instructions

---

## 📦 Plugin Summary

| Feature | Status | Description |
|---------|--------|-------------|
| **WooCommerce Sync** | 🚧 In Development | Primary feature - sync products, customers, orders |
| **OrderSyncService** | ✅ Implemented | Service to sync orders from WooCommerce |
| **CustomerSyncService** | ✅ Integrated | Auto-create customers from WooCommerce orders |
| **Presupuesto PDF** | ✅ Complete | Bonus - compact PDF layout for quotes |

---

## �� Installation

See [INSTALLATION.md](INSTALLATION.md) for complete installation and configuration instructions.
