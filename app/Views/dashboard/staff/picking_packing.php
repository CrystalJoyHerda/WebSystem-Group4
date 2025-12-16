<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Picking & Packing</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= base_url('css/site.css') ?>" rel="stylesheet">
    <style>
        body { font-family: 'Times New Roman', serif; background: #fff; }
        .app-shell { display: flex; min-height: 100vh; }
        .main { flex: 1; padding: 24px 32px; margin-left: 220px; }
        .page-title { text-align:center; font-size: 34px; margin-top: 6px; margin-bottom: 14px; }
        .task-card { border-radius: 8px; border: 1px solid #dcdcdc; margin-bottom: 20px; }
        .task-card-header { background: #f8f9fa; padding: 15px; border-bottom: 1px solid #dcdcdc; }
        .brand { font-family: 'Georgia', serif; font-size: 28px; }
        @media (max-width: 991px) {
            .sidebar { position: relative; width: 100%; }
            .main { margin-left: 0; padding: 16px; }
        }
    </style>
</head>
<body>
    <div class="app-shell">
        <?= view('partials/sidebar') ?>

        <main class="main">
            <div class="header d-flex align-items-center justify-content-between mb-3">
                <div class="brand">WeBuild</div>
            </div>

            <div class="page-title">Picking & Packing</div>

            <!-- Picking Tasks Section -->
            <div class="container-fluid mt-4 mb-4">
                <div class="task-card">
                    <div class="task-card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">📦 Picking Tasks</h5>
                        <button class="btn btn-sm btn-outline-primary" onclick="loadPickingTasks()">
                            <span class="spinner-border spinner-border-sm d-none" id="pickingLoader"></span>
                            Refresh
                        </button>
                    </div>
                    <div class="card-body">
                        <div id="pickingTasksList">
                            <div class="text-center py-3">
                                <div class="spinner-border spinner-border-sm"></div>
                                <span class="ms-2">Loading picking tasks...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Packing Tasks Section -->
            <div class="container-fluid mb-4">
                <div class="task-card">
                    <div class="task-card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">📮 Packing Tasks</h5>
                        <button class="btn btn-sm btn-outline-primary" onclick="loadPackingTasks()">
                            <span class="spinner-border spinner-border-sm d-none" id="packingLoader"></span>
                            Refresh
                        </button>
                    </div>
                    <div class="card-body">
                        <div id="packingTasksList">
                            <div class="text-center py-3">
                                <div class="spinner-border spinner-border-sm"></div>
                                <span class="ms-2">Loading packing tasks...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Picking Modal -->
    <div class="modal fade" id="pickingModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Complete Picking Task</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <strong>Order:</strong> <span id="pickingOrderRef"></span><br>
                        <strong>Item:</strong> <span id="pickingItemName"></span><br>
                        <strong>SKU:</strong> <code id="pickingItemSku"></code><br>
                        <strong>Location:</strong> <span id="pickingLocation"></span><br>
                        <strong>Required Quantity:</strong> <span id="pickingRequired"></span><br>
                        <strong>Available Stock:</strong> <span id="pickingAvailable"></span>
                    </div>
                    <div class="mb-3">
                        <label for="pickedQuantity" class="form-label">Picked Quantity *</label>
                        <input type="number" class="form-control" id="pickedQuantity" min="1" required>
                        <div class="form-text">Enter the actual quantity you picked</div>
                    </div>
                    <div class="alert alert-warning d-none" id="pickingWarning">
                        <i class="bi bi-exclamation-triangle"></i> <span id="pickingWarningText"></span>
                    </div>
                    <input type="hidden" id="pickingTaskId">
                    <input type="hidden" id="pickingReceiptId">
                    <input type="hidden" id="pickingItemId">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="completePicking()">Complete Picking</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Packing Modal -->
    <div class="modal fade" id="packingModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Complete Packing Task</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <strong>Order:</strong> <span id="packingOrderRef"></span><br>
                        <strong>Item:</strong> <span id="packingItemName"></span><br>
                        <strong>SKU:</strong> <code id="packingItemSku"></code><br>
                        <strong>Picked Quantity:</strong> <span id="packingPickedQty"></span>
                    </div>
                    <div class="mb-3">
                        <label for="packedQuantity" class="form-label">Packed Quantity *</label>
                        <input type="number" class="form-control" id="packedQuantity" min="1" required readonly>
                        <div class="form-text">Must match picked quantity</div>
                    </div>
                    <div class="mb-3">
                        <label for="boxCount" class="form-label">Number of Boxes</label>
                        <input type="number" class="form-control" id="boxCount" min="1" value="1">
                    </div>
                    <input type="hidden" id="packingTaskId">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success" onclick="completePacking()">Complete Packing</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Load picking tasks on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadPickingTasks();
            loadPackingTasks();
        });

        // Load picking tasks
        async function loadPickingTasks() {
            const loader = document.getElementById('pickingLoader');
            const container = document.getElementById('pickingTasksList');
            
            if (loader) loader.classList.remove('d-none');
            
            try {
                const response = await fetch('<?= site_url('api/picking/tasks') ?>');
                const data = await response.json();
                
                console.log('Picking tasks loaded:', data);
                
                if (data.tasks && data.tasks.length > 0) {
                    let html = '<div class="table-responsive"><table class="table table-hover"><thead><tr>' +
                        '<th>Order Ref</th><th>Item</th><th>SKU</th><th>Location</th>' +
                        '<th class="text-end">Required</th><th>Status</th><th>Actions</th></tr></thead><tbody>';
                    
                    data.tasks.forEach(task => {
                        console.log('Task status:', task.picking_status, 'for', task.item_name);
                        const statusBadge = getStatusBadge(task.picking_status || 'Pending');
                        const actionBtn = task.picking_status === 'Picked' ? 
                            '<span class="text-success">✓ Completed</span>' :
                            task.picking_status === 'In Progress' ?
                            `<button class="btn btn-sm btn-success" onclick='openPickingModal(${JSON.stringify(task).replace(/'/g, "\\'")})''>Finish Picking</button>` :
                            `<button class="btn btn-sm btn-primary" onclick="startPicking(${task.receipt_id}, ${task.inventory_item_id})">Start Picking</button>`;
                        
                        html += `<tr>
                            <td><strong>${task.reference_no}</strong></td>
                            <td>${task.item_name}</td>
                            <td><code>${task.item_sku}</code></td>
                            <td>${task.storage_location || 'N/A'}</td>
                            <td class="text-end">${task.required_quantity}</td>
                            <td>${statusBadge}</td>
                            <td>${actionBtn}</td>
                        </tr>`;
                    });
                    
                    html += '</tbody></table></div>';
                    container.innerHTML = html;
                } else {
                    container.innerHTML = '<div class="alert alert-info">No picking tasks available</div>';
                }
            } catch (error) {
                console.error('Error loading picking tasks:', error);
                container.innerHTML = '<div class="alert alert-danger">Failed to load picking tasks</div>';
            } finally {
                if (loader) loader.classList.add('d-none');
            }
        }

        // Load packing tasks
        async function loadPackingTasks() {
            const loader = document.getElementById('packingLoader');
            const container = document.getElementById('packingTasksList');
            
            if (loader) loader.classList.remove('d-none');
            
            try {
                const response = await fetch('<?= site_url('api/packing/tasks') ?>');
                const data = await response.json();
                
                if (data.tasks && data.tasks.length > 0) {
                    let html = '<div class="table-responsive"><table class="table table-hover"><thead><tr>' +
                        '<th>Order Ref</th><th>Customer</th><th>Item</th><th>SKU</th>' +
                        '<th class="text-end">Picked Qty</th><th>Status</th><th>Actions</th></tr></thead><tbody>';
                    
                    data.tasks.forEach(task => {
                        const statusBadge = getStatusBadge(task.status);
                        const actionBtn = task.status === 'Packed' ? 
                            '<span class="text-success">✓ Completed</span>' :
                            `<button class="btn btn-sm btn-success" onclick='openPackingModal(${JSON.stringify(task).replace(/'/g, "\\'")})''>Pack Items</button>`;
                        
                        html += `<tr>
                            <td><strong>${task.reference_no}</strong></td>
                            <td>${task.customer_name}</td>
                            <td>${task.item_name}</td>
                            <td><code>${task.item_sku}</code></td>
                            <td class="text-end">${task.picked_quantity}</td>
                            <td>${statusBadge}</td>
                            <td>${actionBtn}</td>
                        </tr>`;
                    });
                    
                    html += '</tbody></table></div>';
                    container.innerHTML = html;
                } else {
                    container.innerHTML = '<div class="alert alert-info">No packing tasks available. Complete picking tasks first.</div>';
                }
            } catch (error) {
                console.error('Error loading packing tasks:', error);
                container.innerHTML = '<div class="alert alert-danger">Failed to load packing tasks</div>';
            } finally {
                if (loader) loader.classList.add('d-none');
            }
        }

        // Get status badge
        function getStatusBadge(status) {
            const badges = {
                'Pending': '<span class="badge bg-secondary">Pending</span>',
                'In Progress': '<span class="badge bg-warning text-dark">In Progress</span>',
                'Picked': '<span class="badge bg-info">Ready for Packing</span>',
                'Packed': '<span class="badge bg-success">Packed</span>'
            };
            return badges[status] || badges['Pending'];
        }

        // Start picking
        async function startPicking(receiptId, itemId) {
            if (!confirm('Start picking this item?')) {
                return;
            }
            
            try {
                const formData = new FormData();
                formData.append('receipt_id', receiptId);
                formData.append('item_id', itemId);
                
                const response = await fetch('<?= site_url('api/picking/start') ?>', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                console.log('Start picking response:', data);
                
                if (data.success) {
                    // Wait a moment for database to update
                    setTimeout(async () => {
                        await loadPickingTasks();
                        alert('Picking started! Now click "Finish Picking" to complete.');
                    }, 500);
                } else {
                    alert('Error: ' + (data.error || 'Failed to start picking'));
                }
            } catch (error) {
                console.error('Error starting picking:', error);
                alert('Failed to start picking task');
            }
        }

        // Open picking modal
        function openPickingModal(task) {
            document.getElementById('pickingOrderRef').textContent = task.reference_no;
            document.getElementById('pickingItemName').textContent = task.item_name;
            document.getElementById('pickingItemSku').textContent = task.item_sku;
            document.getElementById('pickingLocation').textContent = task.storage_location || 'N/A';
            document.getElementById('pickingRequired').textContent = task.required_quantity;
            document.getElementById('pickingAvailable').textContent = task.available_stock;
            document.getElementById('pickingTaskId').value = task.picking_id || '';
            document.getElementById('pickingReceiptId').value = task.receipt_id || task.id;
            document.getElementById('pickingItemId').value = task.inventory_item_id;
            document.getElementById('pickedQuantity').value = task.required_quantity;
            document.getElementById('pickedQuantity').max = task.required_quantity;
            
            // Validate on input
            document.getElementById('pickedQuantity').oninput = function() {
                const picked = parseInt(this.value);
                const required = parseInt(task.required_quantity);
                const warning = document.getElementById('pickingWarning');
                const warningText = document.getElementById('pickingWarningText');
                
                if (picked < required) {
                    warning.classList.remove('d-none');
                    warningText.textContent = `Short pick: ${required - picked} units missing`;
                } else {
                    warning.classList.add('d-none');
                }
            };
            
            new bootstrap.Modal(document.getElementById('pickingModal')).show();
        }

        // Complete picking
        async function completePicking() {
            const taskId = document.getElementById('pickingTaskId').value;
            const receiptId = document.getElementById('pickingReceiptId').value;
            const itemId = document.getElementById('pickingItemId').value;
            const pickedQty = document.getElementById('pickedQuantity').value;
            
            if (!pickedQty || pickedQty <= 0) {
                alert('Please enter a valid picked quantity');
                return;
            }
            
            try {
                const formData = new FormData();
                formData.append('task_id', taskId);
                formData.append('receipt_id', receiptId);
                formData.append('item_id', itemId);
                formData.append('picked_quantity', pickedQty);
                
                const response = await fetch('<?= site_url('api/picking/complete') ?>', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    alert(data.message || 'Picking completed successfully');
                    bootstrap.Modal.getInstance(document.getElementById('pickingModal')).hide();
                    loadPickingTasks();
                    loadPackingTasks();
                } else {
                    alert('Error: ' + (data.error || 'Failed to complete picking'));
                }
            } catch (error) {
                console.error('Error completing picking:', error);
                alert('Failed to complete picking task');
            }
        }

        // Open packing modal
        function openPackingModal(task) {
            document.getElementById('packingOrderRef').textContent = task.reference_no;
            document.getElementById('packingItemName').textContent = task.item_name;
            document.getElementById('packingItemSku').textContent = task.item_sku;
            document.getElementById('packingPickedQty').textContent = task.picked_quantity;
            document.getElementById('packingTaskId').value = task.id;
            document.getElementById('packedQuantity').value = task.picked_quantity;
            
            new bootstrap.Modal(document.getElementById('packingModal')).show();
        }

        // Complete packing
        async function completePacking() {
            const taskId = document.getElementById('packingTaskId').value;
            const packedQty = document.getElementById('packedQuantity').value;
            const boxCount = document.getElementById('boxCount').value || 1;
            
            try {
                const formData = new FormData();
                formData.append('task_id', taskId);
                formData.append('packed_quantity', packedQty);
                formData.append('box_count', boxCount);
                
                const response = await fetch('<?= site_url('api/packing/complete') ?>', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    alert(data.message || 'Packing completed successfully');
                    bootstrap.Modal.getInstance(document.getElementById('packingModal')).hide();
                    loadPackingTasks();
                } else {
                    alert('Error: ' + (data.error || 'Failed to complete packing'));
                }
            } catch (error) {
                console.error('Error completing packing:', error);
                alert('Failed to complete packing task');
            }
        }
    </script>
</body>
</html>
