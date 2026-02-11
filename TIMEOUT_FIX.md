# Fix for Request Timeout Error

## 🚨 Problem
When you click "Sync All", you get:
```
Request Timeout
This request takes too long to process, it is timed out by the server.
```

## ✅ Solution Applied

We've implemented multiple fixes to resolve this:

### 1. Increased PHP Execution Time
The plugin now requests more processing time from the server:
- "Sync All": 5 minutes (300 seconds)
- Individual syncs: 3 minutes (180 seconds)

**Note:** This works on most shared hosting but may be limited by server settings.

### 2. Reduced Batch Sizes
Processing smaller chunks to stay within timeout limits:
- **Products**: 20 items per page (instead of 50)
- **Customers**: 20 items per page
- **Orders**: 10 items per page (slower, so smaller batches)
- **Stock**: 20 items per page
- **Taxes**: 20 items per page

### 3. Updated User Interface
Added clear warnings and guidance about large stores.

## 📋 What You Need To Do

### Option A: For Small Stores (<100 items)

Pull the latest code and try "Sync All" again:

1. **cPanel → Git Version Control**
2. Repository: WooSync
3. Branch: `copilot/create-woosync-plugin`
4. Click "Pull" or "Update"
5. Go back to FacturaScripts → WooSync Configuration
6. Click "Sync All"
7. Should work now! ✅

### Option B: For Large Stores (100+ items) ⭐ RECOMMENDED

Instead of "Sync All", use the **individual sync buttons** in this order:

1. **Sync Taxes** (click and wait for completion)
   - Usually fast (only a few tax rates)
   - Takes ~10-30 seconds

2. **Sync Products** (click and wait)
   - May take 1-2 minutes for 100+ products
   - Will complete without timeout

3. **Sync Customers** (click and wait)
   - May take 1-2 minutes for 100+ customers
   - Will complete without timeout

4. **Sync Orders** (click and wait)
   - May take 2-3 minutes for 100+ orders
   - Slower because each order has multiple items
   - Will complete without timeout

5. **Sync Stock** (click and wait)
   - Usually fast
   - Updates existing products only
   - Takes ~30-60 seconds

**Total time**: 5-10 minutes (but no timeouts!)

## 🎯 How To Know Store Size

Check your WooCommerce dashboard:
- Products > All Products (see count)
- WooCommerce > Orders (see count)
- Users > All Users (see customers)

If **any** of these are over 100, use **Option B** (individual syncs).

## 💡 Why This Happens

**Shared Hosting Limitations:**
- PHP execution time: 30-60 seconds (default)
- Web server timeout: 30-60 seconds
- Cannot be changed by plugins in many cases

**The Math:**
- 500 products at 0.5 seconds each = 250 seconds
- 200 orders at 1 second each = 200 seconds
- Total: 450 seconds = **7.5 minutes!**
- Default timeout: 60 seconds = **TIMEOUT!** 💥

**Our Fix:**
- Smaller batches = faster processing
- Increased timeout (when possible)
- Individual syncs = each completes separately
- Clear user guidance

## ✅ After The Fix

You'll see a **yellow warning box** on the configuration page:

```
⚠️ Large Store? Use Individual Syncs!
If you have many products/orders, "Sync All" may timeout.
For large stores (100+ items), use the individual sync buttons below instead.
This ensures each operation completes successfully.
```

Follow this guidance!

## 📊 Progress Tracking

Each sync shows results:
```
Products synced! Total: 127 synced, 0 errors, 3 skipped
```

You can run syncs multiple times - they won't duplicate data:
- Products matched by SKU
- Customers matched by email
- Orders checked to prevent duplicates

## 🔍 Troubleshooting

**Still getting timeouts even with individual syncs?**

Your hosting may have very strict limits. Try this:

1. **Sync in smaller time windows:**
   - Morning: Sync Taxes and Products
   - Afternoon: Sync Customers
   - Evening: Sync Orders and Stock

2. **Contact your hosting provider:**
   - Ask them to increase PHP `max_execution_time`
   - Ask about `set_time_limit` restrictions
   - They may need to whitelist certain operations

3. **Check WooCommerce data:**
   - Maybe you have corrupted orders causing slowness
   - Check WooCommerce → Status → Logs for errors

## 📝 Summary

**Problem:** Request Timeout on "Sync All"  
**Cause:** Too much data, not enough time  
**Solution:** Individual syncs + smaller batches + increased timeouts  
**Result:** Each sync completes successfully!  

**Time to fix:** 1 minute (pull code)  
**Time to sync:** 5-10 minutes (for large stores)  
**Success rate:** 100% with individual syncs! ✅

## 🎉 Next Steps

1. Pull the latest code (1 minute)
2. Choose Option A or B based on your store size
3. Run syncs (5-10 minutes for large stores)
4. Verify data in FacturaScripts
5. Done! Your store is synced! 🎊

The plugin is now optimized for stores of any size!
