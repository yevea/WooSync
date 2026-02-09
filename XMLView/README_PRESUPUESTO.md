# Presupuesto PDF Template - Layout Guide

## Overview
This custom PDF template for FacturaScripts 2025.71 creates a compact layout for Presupuesto (Budget/Quote) documents by displaying the totals directly at the bottom of the article table instead of at the page bottom.

## Layout Structure

```
+---------------------------------------------------------------------+
| PRESUPUESTO #XXXX                                                   |
| Company Info & Customer Details                                     |
+---------------------------------------------------------------------+
| Ref.    | Description    | Cant. | Precio | Dto.% | IVA% | Total   |
+---------------------------------------------------------------------+
| PROD001 | Product 1      | 2     | 10.00  | 0     | 21   | 20.00   |
| PROD002 | Product 2      | 1     | 50.00  | 10    | 21   | 45.00   |
+---------------------------------------------------------------------+
|                                            Neto:        | 65.00     |
|                                            Impuestos:   | 13.65     |
|                                            Total:       | 78.65     |
+---------------------------------------------------------------------+
| Notes and observations...                                           |
+---------------------------------------------------------------------+
```

## Key Differences from Default Layout

### Before (Default FacturaScripts):
- Article table ends
- Large empty space
- Totals at page bottom (sometimes on next page)

### After (Custom Template):
- Article table ends
- Totals immediately follow as table rows
- No empty space
- All content compact and together

## Technical Details

### File Location
`XMLView/Presupuesto.xml`

### Row Types Used
- `header`: Column headers (Referencia, Descripción, etc.)
- `body`: Individual article lines
- `line-thin`: Separator line
- `subtotal`: Neto and Impuestos rows
- `total`: Final total row (bold)

### Field Mappings
- `neto`: Net amount before taxes
- `totaliva`: Total tax amount
- `total`: Grand total with taxes

## Installation

1. Copy the plugin to your FacturaScripts `Plugins` directory
2. Enable the WooSync plugin in FacturaScripts
3. The custom PDF template will automatically apply to all new Presupuesto PDFs

## Compatibility
- FacturaScripts 2025.71 or higher
- Works with all standard Presupuesto fields
- Compatible with existing customizations

## Testing Checklist

- [ ] Generate a Presupuesto PDF
- [ ] Verify totals appear directly below article table
- [ ] Confirm no empty space between articles and totals
- [ ] Check that all values (Neto, Impuestos, Total) display correctly
- [ ] Test with multiple articles (3+)
- [ ] Test with single article
- [ ] Verify with different tax rates
- [ ] Check with discounts applied
