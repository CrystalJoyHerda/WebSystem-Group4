# Picking & Packing Workflow - Testing Guide

## Overview
The Picking and Packing workflow has been successfully implemented in the Staff Dashboard.

## Database Setup

### Table Created:
```sql
picking_packing_tasks
- id (primary key)
- receipt_id (FK to outbound_receipts)
- item_id (FK to inventory)
- task_type (PICKING or PACKING)
- status (Pending, In Progress, Picked, Packed)
- assigned_to (staff user ID)
- picked_quantity
- packed_quantity
- box_count
- started_at, completed_by, completed_at
```

## Test Data Available

### Approved Outbound Receipts Ready for Picking:

1. **SO-5678** - ABC Construction Corp
   - Item: Hex Bolts M10 (SKU: HB-M10-002)
   - Required: 100 units
   - Available Stock: 200 units
   - ✅ Ready for picking

2. **SO-5599** - Electrical Works Ltd
   - Item: Copper Wire Roll (SKU: ELC-WR-01)
   - Required: 40 units
   - Available Stock: 20 units
   - ⚠️ Insufficient stock (will show short pick warning)

3. **SO-5588** - Woodwork Construction Ltd
   - Item: Timber Plank 2x4 (SKU: TMR-24-003)
   - Required: 60 units
   - Available Stock: 0 units
   - ❌ Out of stock

## Testing Workflow

### Step 1: View Picking Tasks
1. Login as staff user
2. Navigate to Staff Dashboard
3. See "📦 Picking Tasks" section
4. Should display approved orders ready for picking

### Step 2: Start Picking
1. Click "Start Picking" button on a task
2. Task status changes to "In Progress"
3. Task is locked to the current staff member
4. "Continue" button appears instead

### Step 3: Complete Picking
1. Click "Continue" on an in-progress task
2. Modal opens showing:
   - Order Reference
   - Item details (name, SKU, location)
   - Required quantity
   - Available stock
3. Enter picked quantity
4. If quantity < required, warning shows "Short pick: X units missing"
5. Click "Complete Picking"
6. System validates:
   - Quantity > 0
   - Quantity <= required quantity
   - Sufficient stock available
7. On success:
   - Stock is deducted from inventory
   - Stock movement record created (OUTBOUND)
   - Picking task marked as "Picked"
   - Packing task auto-generated

### Step 4: View Packing Tasks
1. After picking is completed
2. Check "📮 Packing Tasks" section
3. Should show items that have been picked
4. Cannot pack items that haven't been picked

### Step 5: Complete Packing
1. Click "Pack Items" button
2. Modal shows:
   - Order reference
   - Item details
   - Picked quantity (read-only)
3. Packed quantity auto-filled (must match picked)
4. Optionally enter number of boxes
5. Click "Complete Packing"
6. System validates packed quantity == picked quantity
7. On success:
   - Packing task marked as "Packed"
   - If all items packed, order status → "SCANNED" (Ready for Shipment)

## API Endpoints

```
GET  /api/picking/tasks        - Get picking tasks for staff
POST /api/picking/start        - Start a picking task
POST /api/picking/complete     - Complete picking task
GET  /api/packing/tasks        - Get packing tasks for staff
POST /api/packing/complete     - Complete packing task
```

## Business Rules Enforced

✅ Staff can only see tasks for their warehouse
✅ Cannot pick more than required quantity
✅ Cannot pick more than available stock
✅ Stock automatically deducted on picking completion
✅ Packing tasks only appear after picking is complete
✅ Packed quantity must match picked quantity
✅ Order status updates when all items are packed
✅ All operations logged in stock_movements table

## UI Features

- Real-time task loading with AJAX
- Status badges (color-coded)
  - Pending (gray)
  - In Progress (yellow)
  - Picked (green)
  - Packed (green)
- Modal dialogs for data entry
- Input validation with warnings
- Refresh buttons for each section
- Responsive design with Bootstrap 5

## Testing Recommendations

### Test Case 1: Normal Flow
- Use SO-5678 (Hex Bolts)
- Full quantity picking (100 units)
- Complete packing
- Verify stock deduction
- Verify order status change

### Test Case 2: Short Pick
- Use SO-5599 (Copper Wire)
- Try to pick 40 (only 20 available)
- System should prevent picking
- Or pick less than required (show warning)

### Test Case 3: Out of Stock
- Use SO-5588 (Timber Plank)
- Should show error on picking attempt
- Verify proper error message display

## Success Criteria

✅ Picking tasks display correctly
✅ Start picking updates status
✅ Complete picking deducts stock
✅ Stock movement records created
✅ Packing tasks auto-generated
✅ Cannot pack unpicked items
✅ Packed quantity validation works
✅ Order status updates correctly
✅ All transactions are atomic (rollback on error)
✅ Clean, professional UI

## Notes

- No database migrations needed (table created via SQL)
- Uses existing outbound_receipts and inventory tables
- No admin/manager approval required (staff executes only)
- All actions are staff-driven and self-contained
- Error handling with proper HTTP status codes
- Transaction safety with rollback on failures
