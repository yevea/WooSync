# Presupuesto PDF Layout - Before & After Comparison

## Problem Statement
The original FacturaScripts Presupuesto PDF had the totals (Neto, Impuestos, Total) displayed at the bottom of the page, creating unwanted empty space between the article listing table and the total amounts.

## Solution
Created a custom XML template that displays the totals as rows directly at the bottom of the article table, creating a compact and professional layout.

---

## Visual Comparison

### BEFORE (Default Layout)
```
┌─────────────────────────────────────────────────────────────────┐
│ PRESUPUESTO #2025-001                                           │
│ Cliente: Juan Pérez                                             │
└─────────────────────────────────────────────────────────────────┘

┌───────┬──────────────┬──────┬────────┬──────┬──────┬─────────┐
│ Ref.  │ Descripción  │ Cant.│ Precio │ Dto% │ IVA% │  Total  │
├───────┼──────────────┼──────┼────────┼──────┼──────┼─────────┤
│ P-001 │ Producto 1   │  2   │ 100.00 │  0   │  21  │ 200.00  │
│ P-002 │ Producto 2   │  1   │  50.00 │  10  │  21  │  45.00  │
│ P-003 │ Producto 3   │  3   │  25.00 │  0   │  21  │  75.00  │
└───────┴──────────────┴──────┴────────┴──────┴──────┴─────────┘

                     [EMPTY SPACE]
                     [EMPTY SPACE]
                     [EMPTY SPACE]
                     [EMPTY SPACE]
                     [EMPTY SPACE]

┌─────────────────────────────────────────────────────────────────┐
│                                              Neto:      320.00   │
│                                              Impuestos:  67.20   │
│                                              Total:     387.20   │
└─────────────────────────────────────────────────────────────────┘
```

### AFTER (Compact Layout) ✅
```
┌─────────────────────────────────────────────────────────────────┐
│ PRESUPUESTO #2025-001                                           │
│ Cliente: Juan Pérez                                             │
└─────────────────────────────────────────────────────────────────┘

┌───────┬──────────────┬──────┬────────┬──────┬──────┬─────────┐
│ Ref.  │ Descripción  │ Cant.│ Precio │ Dto% │ IVA% │  Total  │
├───────┼──────────────┼──────┼────────┼──────┼──────┼─────────┤
│ P-001 │ Producto 1   │  2   │ 100.00 │  0   │  21  │ 200.00  │
│ P-002 │ Producto 2   │  1   │  50.00 │  10  │  21  │  45.00  │
│ P-003 │ Producto 3   │  3   │  25.00 │  0   │  21  │  75.00  │
├───────┴──────────────┴──────┴────────┴──────┼──────┼─────────┤
│                                      Neto:   │      │ 320.00  │
│                                      Impuestos:│    │  67.20  │
│                                      Total:   │      │ 387.20  │
└──────────────────────────────────────────────┴──────┴─────────┘

┌─────────────────────────────────────────────────────────────────┐
│ Observaciones: Válido hasta 30 días                             │
└─────────────────────────────────────────────────────────────────┘
```

---

## Benefits

### 1. **No Empty Space**
The totals are now directly attached to the article table, eliminating wasted vertical space.

### 2. **More Professional**
Creates a cleaner, more organized document that looks more professional to clients.

### 3. **Better Use of Page Space**
Allows more room for observations, terms, and other important information.

### 4. **Easier to Read**
Clients can see the article list and totals together without having to scroll or turn pages.

### 5. **Consistent Layout**
Works regardless of how many articles are in the presupuesto (1 article or 50 articles).

---

## Technical Implementation

### File Structure
```
WooSync/
└── XMLView/
    ├── Presupuesto.xml          (Main template)
    └── README_PRESUPUESTO.md    (Documentation)
```

### Key XML Components

1. **Column Definitions**: 7 columns (reference, description, quantity, price, discount, tax, total)
2. **Row Types**:
   - `header`: Column headers
   - `body`: Article lines
   - `line-thin`: Separator
   - `subtotal`: Neto and Impuestos (rows)
   - `total`: Final total (bold row)

### Field Mappings
- `neto`: Net amount (before taxes)
- `totaliva`: Tax amount
- `total`: Grand total (net + taxes)

---

## Installation & Usage

1. **Install Plugin**: Copy WooSync to FacturaScripts Plugins directory
2. **Enable Plugin**: Activate in FacturaScripts admin panel
3. **Automatic Application**: The template automatically applies to all Presupuesto PDFs
4. **No Configuration Needed**: Works out of the box

---

## Compatibility

✅ FacturaScripts 2025.71+
✅ All standard Presupuesto fields
✅ Works with discounts
✅ Works with multiple tax rates
✅ Compatible with other plugins

---

## Testing

To verify the layout works correctly:

1. Create a new Presupuesto in FacturaScripts
2. Add at least 3 articles with different prices and taxes
3. Generate the PDF
4. Verify:
   - ✅ Totals appear directly below the article table
   - ✅ No empty space between articles and totals
   - ✅ All values display correctly
   - ✅ Layout is compact and professional

---

## Support

If you encounter any issues with the PDF template:

1. Verify FacturaScripts version is 2025.71 or higher
2. Check that the WooSync plugin is enabled
3. Clear FacturaScripts cache if needed
4. Check XMLView/Presupuesto.xml file exists and is valid XML

---

**Created by**: WooSync Plugin
**Date**: 2026-02-09
**Version**: 1.1
