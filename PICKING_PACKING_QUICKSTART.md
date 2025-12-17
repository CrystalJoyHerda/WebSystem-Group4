# PICKING & PACKING WORKFLOW - QUICK START GUIDE

## 🎯 Overview
Staff-driven picking and packing system for warehouse order fulfillment.

---

## 📋 PICKING WORKFLOW

### Step 1: View Tasks
```
Staff Dashboard → 📦 Picking Tasks Section
```
Shows all approved orders waiting to be picked.

### Step 2: Start Picking
```
Click "Start Picking" button
↓
Task Status: Pending → In Progress
↓
Task locked to your user ID
```

### Step 3: Pick Items
```
Click "Continue" button
↓
Modal opens with:
  - Order Reference
  - Item Name & SKU
  - Storage Location
  - Required Quantity
  - Available Stock
↓
Enter actual picked quantity
↓
System validates:
  ✓ Quantity > 0
  ✓ Quantity ≤ Required
  ✓ Quantity ≤ Available Stock
```

### Step 4: Complete Picking
```
Click "Complete Picking"
↓
System automatically:
  1. Deducts stock from inventory
  2. Creates stock movement record
  3. Marks picking task as "Picked"
  4. Generates packing task
↓
Success message displayed
```

---

## 📮 PACKING WORKFLOW

### Step 1: View Tasks
```
Staff Dashboard → 📮 Packing Tasks Section
```
Shows items that have been picked (ready to pack).

### Step 2: Pack Items
```
Click "Pack Items" button
↓
Modal opens with:
  - Order Reference
  - Item Name & SKU
  - Picked Quantity (read-only)
↓
Packed quantity auto-filled (must match picked)
Enter number of boxes (optional)
```

### Step 3: Complete Packing
```
Click "Complete Packing"
↓
System validates:
  ✓ Packed Quantity = Picked Quantity
↓
System automatically:
  1. Marks packing task as "Packed"
  2. If all items packed → Order status: "Ready for Shipment"
↓
Success message displayed
```

---

## 🎨 STATUS BADGES

| Status | Badge Color | Meaning |
|--------|------------|---------|
| Pending | Gray | Not started yet |
| In Progress | Yellow | Currently being picked |
| Picked | Green | Picking completed, ready to pack |
| Packed | Green | Packing completed, ready to ship |

---

## ⚠️ COMMON SCENARIOS

### Short Pick (Less than required)
```
Required: 100 units
Available: 80 units
↓
Pick only 80 units
↓
⚠️ Warning: "Short pick: 20 units missing"
↓
Can still complete (business decision)
```

### Insufficient Stock
```
Required: 50 units
Available: 30 units
↓
Try to pick 50 units
↓
❌ Error: "Insufficient stock available"
↓
Cannot complete picking
```

### Out of Stock
```
Required: 100 units
Available: 0 units
↓
❌ Error: "Insufficient stock available"
↓
Order cannot be fulfilled
```

---

## 🔒 BUSINESS RULES

1. **Cannot pick unpicked items**
2. **Cannot pick more than available stock**
3. **Cannot pick more than required quantity**
4. **Cannot pack unpicked items**
5. **Packed quantity MUST match picked quantity**
6. **All operations are atomic (transaction-safe)**
7. **Stock automatically deducted on picking completion**

---

## 📊 DATA FLOW

```
Approved Order
    ↓
Picking Task (Pending)
    ↓
Staff starts picking
    ↓
Picking Task (In Progress)
    ↓
Staff completes picking
    ↓
Stock deducted
Stock movement recorded
    ↓
Picking Task (Picked)
Packing Task (Pending) ← AUTO-GENERATED
    ↓
Staff completes packing
    ↓
Packing Task (Packed)
    ↓
Order Status: Ready for Shipment
```

---

## 🖥️ UI ELEMENTS

### Picking Tasks Table
| Column | Description |
|--------|-------------|
| Order Ref | Order reference number |
| Item | Item name |
| SKU | Stock keeping unit code |
| Location | Storage location (rack/bin) |
| Required | Required quantity |
| Status | Current task status |
| Actions | Start/Continue/Completed |

### Packing Tasks Table
| Column | Description |
|--------|-------------|
| Order Ref | Order reference number |
| Customer | Customer name |
| Item | Item name |
| SKU | Stock keeping unit code |
| Picked Qty | Quantity that was picked |
| Status | Current task status |
| Actions | Pack/Completed |

---

## 🚀 QUICK ACTIONS

### Refresh Tasks
```
Click "Refresh" button → Reload latest tasks
```

### Cancel Operation
```
Click "Cancel" in modal → Close without saving
```

### View Details
```
Hover over item → See full information
```

---

## ✅ SUCCESS INDICATORS

### Picking Complete
- ✓ Green badge shows "Picked"
- ✓ "Completed" text appears
- ✓ Task disappears from In Progress
- ✓ Packing task appears in Packing section

### Packing Complete
- ✓ Green badge shows "Packed"
- ✓ "Completed" text appears
- ✓ Order status updated

---

## 🔍 TROUBLESHOOTING

### Tasks not showing?
- Refresh the page
- Check if orders are "Approved" status
- Verify you're assigned to correct warehouse

### Cannot complete picking?
- Verify stock availability
- Check quantity is valid
- Ensure you have permission

### Cannot complete packing?
- Ensure picking is completed first
- Verify packed quantity matches picked quantity
- Check all required fields

---

## 📞 SUPPORT

For issues or questions:
1. Check [PICKING_PACKING_TESTING_GUIDE.md](PICKING_PACKING_TESTING_GUIDE.md)
2. Check [PICKING_PACKING_IMPLEMENTATION.md](PICKING_PACKING_IMPLEMENTATION.md)
3. Review error messages carefully
4. Contact system administrator

---

**Version:** 1.0  
**Last Updated:** December 2025  
**Status:** Production Ready ✅
