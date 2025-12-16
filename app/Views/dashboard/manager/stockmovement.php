<?php
// any preparatory PHP logic here
$role = session() ? session()->get('role') ?? 'User' : 'User';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Stock Movements</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= base_url('css/site.css') ?>" rel="stylesheet">
    <link href="<?= base_url('css/manager.css') ?>" rel="stylesheet">
    <style>
        body { font-family: 'Times New Roman', serif; background: #fff; }
        .app-shell { display:flex; min-height:100vh; }
        /* fixed sidebar */
        .sidebar{
            width:220px;
            background:#ebeaea;
            padding:20px;
            border-right:1px solid #ddd;
            position:fixed;
            top:0;
            left:0;
            height:100vh;
            overflow:auto;
            z-index:10;
        }
        .main {
            margin-left:220px;
            flex:1;
            padding:28px;
        }
        .card-move { border-radius:10px; border:1px solid #e9e9e9; background:#fff; box-shadow:0 1px 2px rgba(0,0,0,0.03); }
        .card-move .card-body { padding:18px; }
        .movement-list { max-height:420px; overflow:auto; }
        .inbound-list, .outbound-list { max-height:200px; overflow:auto; }
        .page-title { font-size:28px; margin-bottom:18px; }
        .accepted-item { opacity: .6; }
        .approved-item { opacity: .6; }
        @media (max-width:991px){
            .sidebar{position:relative;height:auto;width:100%}
            .main{margin-left:0;padding:16px}
        }
    </style>
</head>
<body>
    <div class="app-shell">
        <?= view('partials/sidebar') ?>

        <main class="main">
            <div class="brand">WeBuild</div>
            <div class="page-title">Stock Movements</div>

            <div class="container-fluid">
                <div class="row gx-4 gy-4">
                    <!-- Movement History (big card) -->
                    <div class="col-12 col-lg-7">
                        <div class="card card-move">
                            <div class="card-body">
                                <h5 class="card-title">Movement History</h5>
                                <div class="movement-list mt-3">
                                    <table class="table table-sm mb-0">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Type</th>
                                                <th>SKU</th>
                                                <th class="text-end">Qty</th>
                                                <th>Ref</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr><td>2025-10-14</td><td>Inbound</td><td>PNT-5L-004</td><td class="text-end">50</td><td>PO-1234</td></tr>
                                            <tr><td>2025-10-13</td><td>Outbound</td><td>HB-M10-002</td><td class="text-end">100</td><td>SO-5678</td></tr>
                                            <tr><td>2025-10-12</td><td>Transfer</td><td>RF-SHN-01</td><td class="text-end">30</td><td>TX-4321</td></tr>
                                            <tr><td>2025-10-11</td><td>Inbound</td><td>FLR-VNL-01</td><td class="text-end">200</td><td>PO-1210</td></tr>
                                            <tr><td>2025-10-10</td><td>Outbound</td><td>ELC-WR-01</td><td class="text-end">40</td><td>SO-5599</td></tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right column: inbound + outbound -->
                    <div class="col-12 col-lg-5">
                        <div class="card card-move mb-4">
                            <div class="card-body">
                                <h5 class="card-title">Inbound Receipts</h5>
                                <div class="inbound-list mt-3">
                                    <ul class="list-unstyled mb-0" id="inbound-receipts-list">
                                        <li class="text-muted text-center py-3">Loading inbound receipts...</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="card card-move">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h5 class="card-title mb-0">Outbound Shipments</h5>
                                    <div>
                                        <button id="btnCreateOutbound" class="btn btn-sm btn-primary">Create Outbound</button>
                                    </div>
                                </div>
                                <div class="outbound-list mt-3">
                                    <ul class="list-unstyled mb-0" id="outbound-receipts-list">
                                        <li class="text-muted text-center py-3">Loading outbound receipts...</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div> <!-- end right column -->
                </div> <!-- end row -->
            </div> <!-- end container -->
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        (function(){
            // Sample data for demonstrations - in production this would come from backend
            const sampleReceiptData = {
                'PO-1234': {
                    item_data: [{
                        item_id: 3,
                        item_name: 'Portland Cement 50kg',
                        item_sku: 'CEM-50-001',
                        quantity: 50,
                        warehouse_id: 1,
                        warehouse_name: 'Main Warehouse',
                        supplier: 'Construction Materials Ltd'
                    }]
                },
                'PO-1210': {
                    item_data: [{
                        item_id: 8,
                        item_name: 'Exterior Paint 5L',
                        item_sku: 'PNT-5L-004',
                        quantity: 200,
                        warehouse_id: 1,
                        warehouse_name: 'Main Warehouse',
                        supplier: 'Paint Solutions Inc'
                    }]
                },
                'PO-1205': {
                    item_data: [{
                        item_id: 7,
                        item_name: 'PVC Pipe 2in',
                        item_sku: 'PLB-PVC-2',
                        quantity: 200,
                        warehouse_id: 1,
                        warehouse_name: 'Main Warehouse',
                        supplier: 'Pipe & Plumbing Co'
                    }]
                },
                'SO-5678': {
                    item_data: [{
                        item_id: 11,
                        item_name: 'Hex Bolts M10',
                        item_sku: 'HB-M10-002',
                        quantity: 100,
                        warehouse_id: 1,
                        warehouse_name: 'Main Warehouse',
                        customer: 'ABC Construction Corp'
                    }]
                },
                'SO-5599': {
                    item_data: [{
                        item_id: 6,
                        item_name: 'Copper Wire Roll',
                        item_sku: 'ELC-WR-01',
                        quantity: 40,
                        warehouse_id: 1,
                        warehouse_name: 'Main Warehouse',
                        customer: 'Electrical Works Ltd'
                    }]
                },
                'SO-5588': {
                    item_data: [{
                        item_id: 4,
                        item_name: 'Timber Plank 2x4',
                        item_sku: 'TMR-24-003',
                        quantity: 60,
                        warehouse_id: 1,
                        warehouse_name: 'Main Warehouse',
                        customer: 'Woodwork Construction Ltd'
                    }]
                }
            };

            // Handle accept button clicks (inbound receipts)
            document.addEventListener('click', async function(e) {
                if (e.target.classList.contains('accept-btn')) {
                    e.preventDefault();
                    
                    const btn = e.target;
                    const receiptId = btn.getAttribute('data-receipt-id');
                    const ref = btn.getAttribute('data-ref') || '';
                    const originalText = btn.textContent;
                    
                    // Update UI immediately
                    btn.disabled = true;
                    btn.textContent = 'Processing...';
                    
                    try {
                        // Call approval API with receipt ID
                        const response = await fetch('<?= site_url('stockmovements/approveInboundReceipt') ?>', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({
                                receipt_id: receiptId
                            })
                        });

                        const result = await response.json();

                        if (response.ok && result.success) {
                            // Success - remove item from pending list
                            const li = btn.closest('li');
                            if(li) {
                                li.style.opacity = '0.5';
                                setTimeout(() => li.remove(), 500);
                            }

                            showMessage('success', `${result.message}. Receipt ${ref} approved and staff tasks created.`);
                            
                            // Load updated movement history
                            setTimeout(loadMovementHistory, 1000);
                            
                        } else {
                            throw new Error(result.error || 'Approval failed');
                        }

                    } catch (error) {
                        console.error('Inbound approval error:', error);
                        btn.disabled = false;
                        btn.textContent = originalText;
                        showMessage('danger', 'Failed to approve inbound receipt: ' + error.message);
                    }
                }
            });

                    // Handle approve button clicks (outbound shipments)
            document.addEventListener('click', async function(e) {
                if (e.target.classList.contains('approve-btn')) {
                    e.preventDefault();
                    
                    const btn = e.target;
                    const receiptId = btn.getAttribute('data-receipt-id');
                    const ref = btn.getAttribute('data-ref') || '';
                    const originalText = btn.textContent;
                    
                    // Update UI immediately
                    btn.disabled = true;
                    btn.textContent = 'Processing...';
                    
                    try {
                        // Call approval API with receipt ID
                        const response = await fetch('<?= site_url('stockmovements/approveOutboundReceipt') ?>', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({
                                receipt_id: receiptId
                            })
                        });

                        const result = await response.json();

                        if (response.ok && result.success) {
                            // Success - remove item from pending list
                            const li = btn.closest('li');
                            if(li) {
                                li.style.opacity = '0.5';
                                setTimeout(() => li.remove(), 500);
                            }

                            showMessage('success', `${result.message}. Receipt ${ref} approved and stock updated.`);
                            
                            // Load updated movement history
                            setTimeout(loadMovementHistory, 1000);
                            
                        } else {
                            throw new Error(result.error || 'Approval failed');
                        }

                    } catch (error) {
                        console.error('Outbound approval error:', error);
                        btn.disabled = false;
                        btn.textContent = originalText;
                        showMessage('danger', 'Failed to approve outbound shipment: ' + error.message);
                    }
                }
            });

            // Utility function to show messages
            function showMessage(type, message) {
                const alertDiv = document.createElement('div');
                alertDiv.className = `alert alert-${type} position-fixed bottom-0 end-0 m-3`;
                alertDiv.style.zIndex = 9999;
                alertDiv.style.maxWidth = '400px';
                alertDiv.innerHTML = `
                    <strong>${type === 'success' ? 'Success!' : 'Error!'}</strong> ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;
                document.body.appendChild(alertDiv);
                
                // Auto-remove after 5 seconds
                setTimeout(() => {
                    if (alertDiv.parentNode) {
                        alertDiv.remove();
                    }
                }, 5000);
            }

            // Load movement history from API
            async function loadMovementHistory() {
                try {
                    const response = await fetch('<?= site_url('stockmovements/getMovementHistory') ?>');
                    if (response.ok) {
                        const movements = await response.json();
                        updateMovementHistoryTable(movements);
                    }
                } catch (error) {
                    console.error('Failed to load movement history:', error);
                }
            }

            // Update movement history table
            function updateMovementHistoryTable(movements) {
                const tbody = document.querySelector('.movement-list tbody');
                if (!tbody || !movements.length) return;

                tbody.innerHTML = movements.slice(0, 10).map(movement => {
                    const isRed = movement.status === 'red_stock' || (movement.status && movement.status.toLowerCase() === 'red_stock');
                    const rowClass = isRed ? 'table-danger' : '';
                    const statusBadge = isRed ? ' <span class="badge bg-danger ms-1">RED STOCK</span>' : '';

                    return `
                    <tr class="${rowClass}">
                        <td>${formatDate(movement.created_at || new Date())}</td>
                        <td>${movement.movement_type === 'in' ? 'Inbound' : 'Outbound'}${statusBadge}</td>
                        <td>${movement.item_sku || 'N/A'}</td>
                        <td class="text-end">${movement.quantity}</td>
                        <td>${movement.order_number}</td>
                    </tr>
                `;
                }).join('');
            }

            // Utility function to format date
            function formatDate(dateString) {
                const date = new Date(dateString);
                return date.toLocaleDateString();
            }

            // Load pending inbound receipts
            async function loadPendingInboundReceipts() {
                try {
                    const response = await fetch('<?= site_url('stockmovements/getPendingInboundReceipts') ?>');
                    if (response.ok) {
                        const receipts = await response.json();
                        updateInboundReceiptsList(receipts);
                    } else {
                        throw new Error('Failed to load inbound receipts');
                    }
                } catch (error) {
                    console.error('Failed to load inbound receipts:', error);
                    document.getElementById('inbound-receipts-list').innerHTML = 
                        '<li class="text-danger text-center py-3">Failed to load inbound receipts</li>';
                }
            }

            // Load pending outbound receipts
            async function loadPendingOutboundReceipts() {
                try {
                    const response = await fetch('<?= site_url('stockmovements/getPendingOutboundReceipts') ?>');
                    if (response.ok) {
                        const receipts = await response.json();
                        updateOutboundReceiptsList(receipts);
                    } else {
                        throw new Error('Failed to load outbound receipts');
                    }
                } catch (error) {
                    console.error('Failed to load outbound receipts:', error);
                    document.getElementById('outbound-receipts-list').innerHTML = 
                        '<li class="text-danger text-center py-3">Failed to load outbound receipts</li>';
                }
            }

            // Update inbound receipts list
            function updateInboundReceiptsList(receipts) {
                const listEl = document.getElementById('inbound-receipts-list');
                
                if (!receipts || receipts.length === 0) {
                    listEl.innerHTML = '<li class="text-muted text-center py-3">No pending inbound receipts</li>';
                    return;
                }

                listEl.innerHTML = receipts.map(receipt => `
                    <li class="mb-3 d-flex align-items-start justify-content-between" data-ref="${receipt.reference_no}">
                        <div>
                            <strong>${receipt.reference_no}</strong><br>
                            <small>${receipt.supplier_name} — ${receipt.total_items} item(s) — ${formatDate(receipt.created_at)}</small>
                        </div>
                        <div class="ms-3">
                            <button type="button" class="btn btn-sm btn-primary accept-btn" 
                                    data-receipt-id="${receipt.id}" data-ref="${receipt.reference_no}">Accept</button>
                        </div>
                    </li>
                `).join('');
            }

            // Update outbound receipts list
            function updateOutboundReceiptsList(receipts) {
                const listEl = document.getElementById('outbound-receipts-list');
                
                if (!receipts || receipts.length === 0) {
                    listEl.innerHTML = '<li class="text-muted text-center py-3">No pending outbound receipts</li>';
                    return;
                }

                listEl.innerHTML = receipts.map(receipt => `
                    <li class="mb-3 d-flex align-items-start justify-content-between" data-ref="${receipt.reference_no}">
                        <div>
                            <strong>${receipt.reference_no}</strong><br>
                            <small>${receipt.customer_name} — ${receipt.total_items} item(s) — ${formatDate(receipt.created_at)}</small>
                        </div>
                        <div class="ms-3">
                            <button type="button" class="btn btn-sm btn-outline-success approve-btn" 
                                    data-receipt-id="${receipt.id}" data-ref="${receipt.reference_no}">Approve</button>
                        </div>
                    </li>
                `).join('');
            }

            // Load pending warehouse requests
            async function loadPendingWarehouseRequests() {
                try {
                    // Get current warehouse ID (you may need to set this based on logged-in user's warehouse)
                    const warehouseId = 2; // TODO: Get from session or user data
                    
                    const response = await fetch(`<?= site_url('api/warehouse-requests/pending') ?>?warehouse_id=${warehouseId}`);
                    if (response.ok) {
                        const requests = await response.json();
                        updateWarehouseRequestsList(requests);
                    } else {
                        throw new Error('Failed to load warehouse requests');
                    }
                } catch (error) {
                    console.error('Failed to load warehouse requests:', error);
                    document.getElementById('warehouse-requests-list').innerHTML = 
                        '<li class="text-danger text-center py-3">Failed to load requests</li>';
                }
            }

            // Update warehouse requests list display
            function updateWarehouseRequestsList(requests) {
                const container = document.getElementById('warehouse-requests-list');
                
                if (!requests || requests.length === 0) {
                    container.innerHTML = '<li class="text-muted text-center py-3">No pending requests</li>';
                    return;
                }

                container.innerHTML = requests.map(request => `
                    <li class="d-flex justify-content-between align-items-start border-bottom pb-2 mb-2">
                        <div>
                            <strong>${request.reference_no}</strong><br>
                            <small>From: ${request.requesting_warehouse_name}</small><br>
                            <small>${request.items?.length || 0} item(s) — ${formatDate(request.created_at)}</small>
                        </div>
                        <div class="ms-3">
                            <button type="button" class="btn btn-sm btn-outline-success approve-request-btn" 
                                    data-request-id="${request.id}" data-ref="${request.reference_no}">Approve</button>
                        </div>
                    </li>
                `).join('');
            }

            // Handle approve warehouse request
            document.addEventListener('click', async function(e) {
                if (e.target.classList.contains('approve-request-btn')) {
                    e.preventDefault();
                    
                    const btn = e.target;
                    const requestId = btn.getAttribute('data-request-id');
                    const ref = btn.getAttribute('data-ref');

                    if (!confirm(`Approve warehouse request ${ref}?`)) {
                        return;
                    }

                    try {
                        btn.disabled = true;
                        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Approving...';

                        const response = await fetch(`<?= site_url('api/warehouse-requests/approve') ?>/${requestId}`, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' }
                        });

                        const result = await response.json();

                        if (response.ok && result.success) {
                            showMessage('Warehouse request approved successfully!', 'success');
                            loadPendingWarehouseRequests();
                            loadPendingOutboundReceipts(); // Reload outbound as new outbound was created
                        } else {
                            throw new Error(result.error || 'Failed to approve request');
                        }
                    } catch (error) {
                        console.error('Error approving warehouse request:', error);
                        showMessage('Error approving request: ' + error.message, 'error');
                        btn.disabled = false;
                        btn.innerHTML = 'Approve';
                    }
                }
            });

            // Load initial data
            document.addEventListener('DOMContentLoaded', function() {
                loadMovementHistory();
                loadPendingInboundReceipts();
                loadPendingOutboundReceipts();
                loadPendingWarehouseRequests();
            });

            // Expose helper functions to global scope so other scripts (modal) can call them
            window.loadMovementHistory = loadMovementHistory;
            window.loadPendingOutboundReceipts = loadPendingOutboundReceipts;
            window.showMessage = showMessage;
        })();
    </script>

    <!-- Warehouse Request Modal -->
    <div class="modal fade" id="warehouseRequestModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Request Items from Another Warehouse</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="warehouseRequestForm">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Requesting Warehouse (Your Warehouse)</label>
                                <select class="form-select" id="requestingWarehouse" name="requesting_warehouse_id" required>
                                    <option value="">Select Warehouse</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Supplying Warehouse</label>
                                <select class="form-select" id="supplyingWarehouse" name="supplying_warehouse_id" required>
                                    <option value="">Select Warehouse</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Items to Request</label>
                            <div id="requestItemsContainer">
                                <div class="row mb-2 request-item">
                                    <div class="col-md-7">
                                        <select class="form-select item-select" name="items[0][item_id]" required>
                                            <option value="">Select Item</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <input type="number" class="form-control" name="items[0][quantity]" 
                                               placeholder="Quantity" min="1" required>
                                    </div>
                                    <div class="col-md-2">
                                        <button type="button" class="btn btn-sm btn-danger remove-item-btn" disabled>Remove</button>
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-secondary mt-2" id="addItemBtn">
                                <i class="fas fa-plus"></i> Add Item
                            </button>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Notes (Optional)</label>
                            <textarea class="form-control" name="notes" rows="3"></textarea>
                        </div>

                        <div class="alert alert-info">
                            <strong>Note:</strong> Once submitted, this request will be sent to the supplying warehouse manager for approval.
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="submitRequestBtn">Submit Request</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Warehouse Request Modal Logic
        let itemCounter = 1;
        let warehouses = [];
        let inventoryItems = [];

        // Load warehouses and inventory on page load
        async function loadWarehouseRequestData() {
            try {
                // Load warehouses
                const warehouseRes = await fetch('<?= site_url('api/warehouse/list') ?>');
                warehouses = await warehouseRes.json();
                
                const requestingSelect = document.getElementById('requestingWarehouse');
                const supplyingSelect = document.getElementById('supplyingWarehouse');
                
                warehouses.forEach(w => {
                    requestingSelect.innerHTML += `<option value="${w.id}">${w.name}</option>`;
                    supplyingSelect.innerHTML += `<option value="${w.id}">${w.name}</option>`;
                });

                // Load all inventory items
                const invRes = await fetch('<?= site_url('api/inventory/all-with-warehouse') ?>');
                inventoryItems = await invRes.json();
                
                updateItemSelects();
            } catch (error) {
                console.error('Error loading warehouse request data:', error);
            }
        }

        // Update all item select dropdowns
        function updateItemSelects() {
            const itemSelects = document.querySelectorAll('.item-select');
            itemSelects.forEach(select => {
                const currentValue = select.value;
                select.innerHTML = '<option value="">Select Item</option>';
                
                inventoryItems.forEach(item => {
                    select.innerHTML += `<option value="${item.id}">${item.name} (${item.sku})</option>`;
                });
                
                if (currentValue) {
                    select.value = currentValue;
                }
            });
        }

        // Add item row
        document.getElementById('addItemBtn').addEventListener('click', function() {
            const container = document.getElementById('requestItemsContainer');
            const newRow = document.createElement('div');
            newRow.className = 'row mb-2 request-item';
            newRow.innerHTML = `
                <div class="col-md-7">
                    <select class="form-select item-select" name="items[${itemCounter}][item_id]" required>
                        <option value="">Select Item</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="number" class="form-control" name="items[${itemCounter}][quantity]" 
                           placeholder="Quantity" min="1" required>
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-sm btn-danger remove-item-btn">Remove</button>
                </div>
            `;
            container.appendChild(newRow);
            itemCounter++;
            updateItemSelects();
            updateRemoveButtons();
        });

        // Remove item row
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-item-btn')) {
                e.target.closest('.request-item').remove();
                updateRemoveButtons();
            }
        });

        // Update remove buttons (disable if only one item)
        function updateRemoveButtons() {
            const items = document.querySelectorAll('.request-item');
            const removeButtons = document.querySelectorAll('.remove-item-btn');
            removeButtons.forEach(btn => {
                btn.disabled = items.length === 1;
            });
        }

        // Submit warehouse request
        document.getElementById('submitRequestBtn').addEventListener('click', async function() {
            const form = document.getElementById('warehouseRequestForm');
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            const formData = new FormData(form);
            const data = {
                requesting_warehouse_id: formData.get('requesting_warehouse_id'),
                supplying_warehouse_id: formData.get('supplying_warehouse_id'),
                notes: formData.get('notes'),
                items: []
            };

            // Collect items
            const itemRows = document.querySelectorAll('.request-item');
            itemRows.forEach((row, index) => {
                const itemId = formData.get(`items[${index}][item_id]`);
                const quantity = formData.get(`items[${index}][quantity]`);
                if (itemId && quantity) {
                    data.items.push({
                        item_id: parseInt(itemId),
                        quantity: parseInt(quantity)
                    });
                }
            });

            if (data.items.length === 0) {
                alert('Please add at least one item');
                return;
            }

            try {
                this.disabled = true;
                this.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Submitting...';

                const response = await fetch('<?= site_url('api/warehouse-requests/create') ?>', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (response.ok && result.success) {
                    alert('Warehouse request submitted successfully!');
                    bootstrap.Modal.getInstance(document.getElementById('warehouseRequestModal')).hide();
                    form.reset();
                    itemCounter = 1;
                    document.getElementById('requestItemsContainer').innerHTML = `
                        <div class="row mb-2 request-item">
                            <div class="col-md-7">
                                <select class="form-select item-select" name="items[0][item_id]" required>
                                    <option value="">Select Item</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <input type="number" class="form-control" name="items[0][quantity]" 
                                       placeholder="Quantity" min="1" required>
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-sm btn-danger remove-item-btn" disabled>Remove</button>
                            </div>
                        </div>
                    `;
                    updateItemSelects();
                } else {
                    alert('Error: ' + (result.error || 'Failed to submit request'));
                }
            } catch (error) {
                console.error('Error submitting request:', error);
                alert('Error submitting request');
            } finally {
                this.disabled = false;
                this.innerHTML = 'Submit Request';
            }
        });

        // Load data when modal is opened
        document.getElementById('warehouseRequestModal').addEventListener('shown.bs.modal', function() {
            if (warehouses.length === 0) {
                loadWarehouseRequestData();
            }
        });
    </script>

    <?= view('partials/create_outbound_modal') ?>
</body>
</html>