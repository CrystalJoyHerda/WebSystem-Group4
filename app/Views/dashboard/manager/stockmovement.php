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
                                <h5 class="card-title">Outbound Shipments</h5>
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

                tbody.innerHTML = movements.slice(0, 10).map(movement => `
                    <tr>
                        <td>${formatDate(movement.created_at || new Date())}</td>
                        <td>${movement.movement_type === 'in' ? 'Inbound' : 'Outbound'}</td>
                        <td>${movement.item_sku || 'N/A'}</td>
                        <td class="text-end">${movement.quantity}</td>
                        <td>${movement.order_number}</td>
                    </tr>
                `).join('');
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

            // Load initial data
            document.addEventListener('DOMContentLoaded', function() {
                loadMovementHistory();
                loadPendingInboundReceipts();
                loadPendingOutboundReceipts();
            });
        })();
    </script>
</body>
</html>