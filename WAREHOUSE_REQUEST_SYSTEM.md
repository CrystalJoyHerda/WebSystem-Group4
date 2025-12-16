# Warehouse-to-Warehouse Request & Delivery System

## Database Setup

Run the following command to create the necessary database tables:

```bash
php spark migrate
```

This will create:
1. `warehouse_requests` table - stores warehouse-to-warehouse item requests
2. `warehouse_request_items` table - stores items in each request
3. Updates to `inbound_receipts` and `outbound_receipts` tables to add new status values

## System Features Implemented

### 1. **Request Creation (Warehouse A Manager)**
- Navigate to: Stock Movements page
- Click "Request Items from Another Warehouse" button
- Fill in the modal:
  - Select your warehouse (Requesting Warehouse)
  - Select the supplying warehouse (e.g., Warehouse B)
  - Add items and quantities
  - Add optional notes
- Submit the request

### 2. **Request Approval (Warehouse B Manager)**
- Navigate to: Stock Movements page
- View "Pending Warehouse Requests" section
- Click "Approve" on any pending request
- System automatically:
  - Creates outbound receipt for Warehouse B
  - Creates staff tasks for outbound picking
  - Creates inbound receipt for Warehouse A (status: PACKING)

### 3. **Staff Scanning (Warehouse B Staff)**
- Navigate to: Barcode Scanning page
- Scan outbound items for the warehouse request
- When all items are scanned:
  - Outbound receipt status → SCANNED
  - Warehouse request status → DELIVERING
  - Inbound receipt status (Warehouse A) → DELIVERING

### 4. **Delivery Confirmation (Warehouse B Staff)**
- Navigate to: Deliveries page (new link in sidebar)
- View list of deliveries ready for transport
- Click "Mark as Delivered" button
- System automatically:
  - Outbound receipt status → DELIVERED
  - Warehouse request status → DELIVERED
  - Inbound receipt status (Warehouse A) → DELIVERED

## Status Flow

| Action | Warehouse B (Supplying) | Warehouse A (Requesting) |
|--------|------------------------|--------------------------|
| Request submitted | Pending request appears | - |
| Manager approves | Outbound: Approved | Inbound: PACKING |
| Staff scans items | Outbound: SCANNED | Inbound: DELIVERING |
| Staff confirms delivery | Outbound: DELIVERED | Inbound: DELIVERED |

## Navigation Changes

**Staff Sidebar** now includes:
- Deliveries (new page)

**Manager Stock Movements** now includes:
- "Request Items from Another Warehouse" button
- "Pending Warehouse Requests" section

## API Endpoints

- `POST /api/warehouse-requests/create` - Create a new warehouse request
- `GET /api/warehouse-requests/pending?warehouse_id=X` - Get pending requests for a warehouse
- `POST /api/warehouse-requests/approve/{id}` - Approve a request
- `GET /api/warehouse-requests/deliveries?warehouse_id=X` - Get deliveries ready for transport
- `POST /api/warehouse-requests/mark-delivered/{id}` - Mark delivery as delivered

## Files Created/Modified

### New Files:
1. `app/Database/Migrations/2025-12-15-000001_CreateWarehouseRequestsTables.php`
2. `app/Database/Migrations/2025-12-15-000002_UpdateReceiptsStatusEnums.php`
3. `app/Models/WarehouseRequestModel.php`
4. `app/Controllers/WarehouseRequestController.php`
5. `app/Views/dashboard/staff/deliveries.php`

### Modified Files:
1. `app/Config/Routes.php` - Added warehouse request routes
2. `app/Views/dashboard/manager/stockmovement.php` - Added request modal and pending requests section
3. `app/Views/partials/sidebar.php` - Added Deliveries link for staff
4. `app/Controllers/StaffTaskController.php` - Updated scanning logic to handle warehouse requests

## Testing the System

1. **As Manager (Warehouse A)**:
   - Go to Stock Movements
   - Click "Request Items from Another Warehouse"
   - Create a request for items from Warehouse B

2. **As Manager (Warehouse B)**:
   - Go to Stock Movements
   - See the pending request in "Pending Warehouse Requests"
   - Click "Approve"

3. **As Staff (Warehouse B)**:
   - Go to Barcode Scanning
   - Scan the outbound items for the request
   - After scanning all items, go to Deliveries page

4. **As Staff (Warehouse B)**:
   - Go to Deliveries
   - See the delivery ready for transport
   - Click "Mark as Delivered"

5. **Verify**:
   - Warehouse A's inbound receipt should now show DELIVERED status
   - Warehouse B's outbound receipt should show DELIVERED status
   - The warehouse request should be marked as DELIVERED

## Notes

- The system uses reference numbers like `WR-YYYYMMDD-XXXX` for warehouse requests
- Outbound receipts are prefixed with `OUT-WR-...`
- Inbound receipts are prefixed with `IN-WR-...`
- Status synchronization happens automatically between warehouses
- All operations are wrapped in database transactions for data integrity
