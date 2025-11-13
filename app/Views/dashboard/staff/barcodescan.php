<?php
$role = session() ? session()->get('role') ?? 'User' : 'User';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Barcode Scanning</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= base_url('css/site.css') ?>" rel="stylesheet">
    <link href="<?= base_url('css/staff.css') ?>" rel="stylesheet">
    <style>
        body { font-family: 'Times New Roman', serif; background: #fff; margin: 0; }
        .app-shell { display: flex; min-height: 100vh; }
        .sidebar { width: 220px; background: #ebeaea; padding: 20px; border-right: 1px solid #ddd; position: fixed; top: 0; left: 0; height: 100vh; overflow: auto; z-index: 10; }
        .main { margin-left: 220px; flex: 1; padding: 28px; }
        .page-title { text-align: center; font-size: 34px; margin-bottom: 28px; letter-spacing: 1px; }
        
        .scan-container { display: flex; gap: 24px; }
        .scan-left { flex: 0 0 420px; }
        .scan-right { flex: 1; }
        
        .scan-card { border-radius: 12px; border: 1px solid #e0e0e0; padding: 24px; background: #fff; box-shadow: 0 1px 3px rgba(0,0,0,0.08); }
        .scan-card h5 { margin-bottom: 18px; font-size: 18px; font-weight: 600; }
        
        .scan-icon-box { border: 2px dashed #ddd; border-radius: 12px; padding: 40px; text-align: center; background: #fafafa; margin-bottom: 20px; }
        .scan-icon { width: 80px; height: 80px; background: #f0f0f0; border-radius: 8px; margin: 0 auto 12px; display: flex; align-items: center; justify-content: center; }
        .scan-icon svg { width: 48px; height: 48px; opacity: 0.4; }
        .scan-status { color: #666; font-size: 15px; margin-top: 8px; }
        
        .btn-scan { width: 100%; padding: 12px; border-radius: 8px; font-size: 16px; font-weight: 600; margin-bottom: 12px; }
        .scan-type-select { width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #0b69ff; color: #0b69ff; background: #fff; font-size: 15px; cursor: pointer; }
        .scan-type-select:hover { background: #f0f7ff; }
        
        .recent-items-card { min-height: 500px; }
        .recent-item { border: 1px solid #e8e8e8; border-radius: 10px; padding: 16px; margin-bottom: 12px; background: #fff; }
        .recent-item-header { font-weight: 600; font-size: 16px; margin-bottom: 8px; }
        .recent-item-status { color: #666; font-size: 14px; }
        
        .btn-save-update { background: #28a745; color: #fff; border: none; padding: 12px 32px; border-radius: 8px; font-size: 16px; font-weight: 600; float: right; margin-top: 20px; }
        .btn-save-update:hover { background: #218838; }
        
        @media (max-width: 991px) {
            .sidebar { position: relative; height: auto; width: 100%; }
            .main { margin-left: 0; padding: 16px; }
            .scan-container { flex-direction: column; }
            .scan-left { flex: 1; }
        }
    </style>
</head>
<body>
<div class="app-shell">
    <?= view('partials/sidebar') ?>

    <main class="main">
        <div style="text-align:right;margin-bottom:20px">
            <button class="btn btn-sm" style="border:1px solid #ddd;padding:8px 16px;border-radius:6px;background:#fff">⚙️</button>
        </div>

        <h2 class="page-title">BARCODE SCANNING</h2>

        <div class="scan-container">
            <!-- Left: Scan Items Card -->
            <div class="scan-left">
                <div class="scan-card">
                    <h5>📦 Scan Items</h5>
                    
                    <div class="scan-icon-box">
                        <div class="scan-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="3" width="7" height="7"/>
                                <rect x="14" y="3" width="7" height="7"/>
                                <rect x="14" y="14" width="7" height="7"/>
                                <rect x="3" y="14" width="7" height="7"/>
                            </svg>
                        </div>
                        <div class="scan-status">Ready to Scan</div>
                    </div>

                    <button id="btnStartScan" class="btn btn-primary btn-scan">
                        📷 Start Scanning
                    </button>

                    <button class="scan-type-select" id="scanTypeBtn">
                        Inbound scan ▼
                    </button>

                    <!-- Hidden dropdown for scan types -->
                    <select id="scanType" style="display:none">
                        <option value="inbound">Inbound scan</option>
                        <option value="outbound">Outbound scan</option>
                        <option value="transfer">Transfer scan</option>
                    </select>
                </div>
            </div>

            <!-- Right: To-Do Tasks and Recently Scanned Items -->
            <div class="scan-right">
                <!-- To-Do Tasks Section -->
                <div class="scan-card mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">📋 To-Do Tasks</h5>
                        <button id="refreshTasks" class="btn btn-sm btn-outline-primary">Refresh</button>
                    </div>
                    
                    <div id="todoTasksList">
                        <div class="text-center py-3">
                            <div class="spinner-border spinner-border-sm" role="status"></div>
                            <span class="ms-2">Loading tasks...</span>
                        </div>
                    </div>
                </div>

                <!-- Recently Scanned Items Section -->
                <div class="scan-card recent-items-card">
                    <h5>📦 Recently Scanned Items</h5>
                    
                    <div id="recentItemsList">
                        <!-- Placeholder items -->
                        <div class="recent-item">
                            <div class="recent-item-header">Recently Scanned Items</div>
                            <div class="recent-item-status">Ready to Scan</div>
                        </div>
                    </div>

                    <button class="btn-save-update" id="btnSaveUpdate">
                        Save & Update
                    </button>
                    <div style="clear:both"></div>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- Scanner Modal -->
<div class="modal fade" id="scannerModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Barcode Scanner</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="scannerVideo" style="width:100%;height:400px;background:#000;border-radius:8px;position:relative">
            <video id="videoElement" style="width:100%;height:100%;object-fit:cover"></video>
            <div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);color:#fff;font-size:18px;text-align:center">
                Camera access required<br>
                <small>Or enter barcode manually below</small>
            </div>
        </div>
        <div class="mt-3">
            <label class="form-label">Manual Barcode Entry</label>
            <input type="text" id="manualBarcodeInput" class="form-control" placeholder="Enter barcode manually or scan">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="btnConfirmScan">Confirm Scan</button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
(() => {
    let scannedItems = [];
    let currentScanType = 'inbound';
    let selectedWarehouse = null;
    let warehouses = [];
    let isProcessingScans = false;

    const scannerModal = new bootstrap.Modal(document.getElementById('scannerModal'));

    // Load warehouses and tasks on page load
    document.addEventListener('DOMContentLoaded', function() {
        loadWarehouses();
        loadPendingTasks();
        
        // Set up refresh button
        document.getElementById('refreshTasks').addEventListener('click', loadPendingTasks);
    });

    async function loadWarehouses() {
        try {
            const response = await fetch('<?= site_url('api/warehouse/list') ?>');
            if (response.ok) {
                warehouses = await response.json();
                setupWarehouseSelector();
            }
        } catch (error) {
            console.warn('Failed to load warehouses:', error);
        }
    }

    function setupWarehouseSelector() {
        // Add warehouse selector to the scan card
        const scanCard = document.querySelector('.scan-left .scan-card');
        const warehouseSelector = document.createElement('div');
        warehouseSelector.innerHTML = `
            <div class="mb-3">
                <label class="form-label">Select Warehouse</label>
                <select id="warehouseSelect" class="form-select">
                    <option value="">Choose warehouse...</option>
                    ${warehouses.map(w => `<option value="${w.id}">${w.name}</option>`).join('')}
                </select>
            </div>
        `;
        
        // Insert after the scan icon box
        const scanIconBox = scanCard.querySelector('.scan-icon-box');
        scanIconBox.parentNode.insertBefore(warehouseSelector, scanIconBox.nextSibling);
        
        // Add event listener
        document.getElementById('warehouseSelect').addEventListener('change', function() {
            selectedWarehouse = this.value ? parseInt(this.value) : null;
            updateScanStatus();
        });
    }

    function updateScanStatus() {
        const statusElement = document.querySelector('.scan-status');
        if (selectedWarehouse) {
            const warehouse = warehouses.find(w => w.id === selectedWarehouse);
            statusElement.textContent = `Ready to scan - ${warehouse?.name}`;
            statusElement.style.color = '#28a745';
        } else {
            statusElement.textContent = 'Select a warehouse first';
            statusElement.style.color = '#dc3545';
        }
    }

    // Load pending tasks from staff task API
    async function loadPendingTasks() {
        try {
            const response = await fetch('<?= site_url('api/staff-tasks/pending') ?>');
            if (response.ok) {
                const result = await response.json();
                displayPendingTasks(result.tasks || []);
            } else {
                console.error('Failed to load pending tasks');
                displayPendingTasks([]);
            }
        } catch (error) {
            console.error('Error loading pending tasks:', error);
            displayPendingTasks([]);
        }
    }

    // Display pending tasks in the to-do list
    function displayPendingTasks(tasks) {
        const container = document.getElementById('todoTasksList');
        
        if (tasks.length === 0) {
            container.innerHTML = `
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> No pending tasks available.
                    <br><small>Tasks will appear here when manager approves receipts.</small>
                </div>
            `;
            return;
        }

        const tasksHtml = tasks.map(task => {
            const badgeClass = task.movement_type === 'IN' ? 'bg-success' : 'bg-warning';
            const icon = task.movement_type === 'IN' ? '📥' : '📤';
            
            return `
                <div class="task-item border rounded p-3 mb-2" data-task-id="${task.id}">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center mb-2">
                                <span class="me-2">${icon}</span>
                                <strong>${task.reference_no}</strong>
                                <span class="badge ${badgeClass} ms-2">${task.movement_type}</span>
                            </div>
                            <div class="task-details">
                                <div><strong>${task.item_name}</strong></div>
                                <div class="text-muted small">
                                    SKU: ${task.item_sku || 'N/A'} | Qty: ${task.quantity} | 
                                    Warehouse: ${task.warehouse_name || 'Unknown'}
                                </div>
                                <div class="text-muted small">
                                    Created: ${formatTaskDate(task.created_at)}
                                </div>
                            </div>
                        </div>
                        <div class="ms-3">
                            <button class="btn btn-sm btn-primary scan-task-btn" 
                                    data-task-id="${task.id}" 
                                    data-item-sku="${task.item_sku || task.current_sku}"
                                    data-warehouse-id="${task.warehouse_id}">
                                <i class="fas fa-qrcode"></i> Scan
                            </button>
                        </div>
                    </div>
                </div>
            `;
        }).join('');

        container.innerHTML = tasksHtml;

        // Add event listeners to scan buttons
        container.querySelectorAll('.scan-task-btn').forEach(button => {
            button.addEventListener('click', function() {
                const taskId = this.dataset.taskId;
                const itemSku = this.dataset.itemSku;
                const warehouseId = this.dataset.warehouseId;
                
                // Set warehouse and start scanning for this task
                selectedWarehouse = parseInt(warehouseId);
                document.getElementById('warehouseSelect').value = warehouseId;
                updateScanStatus();
                
                // Pre-fill the manual barcode input with SKU
                document.getElementById('manualBarcodeInput').value = itemSku;
                
                // Show scanner modal
                scannerModal.show();
            });
        });
    }

    // Format task creation date
    function formatTaskDate(dateString) {
        if (!dateString) return 'Unknown';
        const date = new Date(dateString);
        return date.toLocaleDateString() + ' ' + date.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
    }

    // Scan type selector
    document.getElementById('scanTypeBtn').addEventListener('click', function() {
        const select = document.getElementById('scanType');
        const currentValue = select.value;
        const newValue = currentValue === 'inbound' ? 'outbound' : currentValue === 'outbound' ? 'transfer' : 'inbound';
        select.value = newValue;
        currentScanType = newValue;
        this.textContent = select.options[select.selectedIndex].text + ' ▼';
    });

    // Start scanning button
    document.getElementById('btnStartScan').addEventListener('click', function() {
        if (!selectedWarehouse) {
            showAlert('Please select a warehouse first', 'warning');
            return;
        }
        
        scannerModal.show();
        // Try to access camera (basic implementation)
        if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
            navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } })
                .then(stream => {
                    const video = document.getElementById('videoElement');
                    video.srcObject = stream;
                    video.play();
                })
                .catch(err => {
                    console.log('Camera access denied or unavailable:', err);
                    showAlert('Camera access denied. Please use manual entry.', 'info');
                });
        }
    });

    // Manual barcode entry (Enter key)
    document.getElementById('manualBarcodeInput').addEventListener('keypress', function(e) {
        if (e.key === 'Enter' && this.value.trim()) {
            processBarcodeScan(this.value.trim());
        }
    });

    // Confirm scan button
    document.getElementById('btnConfirmScan').addEventListener('click', function() {
        const barcode = document.getElementById('manualBarcodeInput').value.trim();
        if (barcode) {
            processBarcodeScan(barcode);
        } else {
            showAlert('Please enter a barcode or scan one.', 'warning');
        }
    });

    async function processBarcodeScan(barcode) {
        if (isProcessingScans) return;
        
        isProcessingScans = true;
        showAlert('Processing scan...', 'info');
        
        try {
            // First, check if there's a pending task for this barcode
            const taskResponse = await fetch('<?= site_url('api/staff-tasks/find-by-barcode') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    barcode: barcode,
                    warehouse_id: selectedWarehouse
                })
            });

            if (taskResponse.ok) {
                // Found a pending task - handle task completion
                const taskResult = await taskResponse.json();
                const task = taskResult.task;
                const item = taskResult.item;
                
                // Confirm task completion with user
                const confirmMessage = `Found pending ${task.movement_type} task for ${item.name}\n` +
                                     `Reference: ${task.reference_no}\n` +
                                     `Quantity: ${task.quantity}\n\n` +
                                     `Complete this task?`;
                
                if (confirm(confirmMessage)) {
                    await completeStaffTask(task.id, item, task);
                } else {
                    showAlert('Task completion cancelled', 'info');
                }
                
            } else {
                // No pending task found - use regular scanning workflow
                const response = await fetch('<?= site_url('api/barcode/scan') ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        code: barcode,
                        warehouse_id: selectedWarehouse
                    })
                });

                if (response.ok) {
                    const result = await response.json();
                    const item = result.item;
                    
                    // Add to scanned items with full item information
                    const scannedItem = {
                        barcode: barcode,
                        type: currentScanType,
                        time: new Date().toLocaleString(),
                        item: item,
                        warehouse_id: selectedWarehouse,
                        processed: false,
                        error: null
                    };
                    
                    scannedItems.push(scannedItem);
                    renderScannedItems();
                    
                    showAlert(`Item found: ${item.name} (No pending tasks)`, 'success');
                } else {
                    const error = await response.json();
                    showAlert(`Item not found: ${error.error}`, 'danger');
                }
            }
            
            // Clear input and close modal
            document.getElementById('manualBarcodeInput').value = '';
            scannerModal.hide();
            
        } catch (error) {
            console.error('Scan processing error:', error);
            showAlert('Error processing scan. Please try again.', 'danger');
        } finally {
            isProcessingScans = false;
        }
    }

    // Complete a staff task
    async function completeStaffTask(taskId, item, task) {
        try {
            showAlert('Completing task...', 'info');
            
            const response = await fetch(`<?= site_url('api/staff-tasks/complete') ?>/${taskId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    notes: `Completed via barcode scan: ${item.sku}`
                })
            });

            const result = await response.json();

            if (response.ok && result.success) {
                showAlert(`Task completed! Stock updated: ${result.old_stock} → ${result.new_stock}`, 'success');
                
                // Add to scanned items as completed
                const scannedItem = {
                    barcode: item.sku,
                    type: task.movement_type.toLowerCase(),
                    time: new Date().toLocaleString(),
                    item: item,
                    warehouse_id: selectedWarehouse,
                    processed: true,
                    error: null,
                    task_completed: true,
                    task_reference: task.reference_no
                };
                
                scannedItems.push(scannedItem);
                renderScannedItems();
                
                // Refresh pending tasks to remove completed task
                setTimeout(loadPendingTasks, 1000);
                
            } else {
                throw new Error(result.error || 'Task completion failed');
            }

        } catch (error) {
            console.error('Task completion error:', error);
            showAlert('Failed to complete task: ' + error.message, 'danger');
        }
    }

    function showAlert(message, type = 'info') {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} position-fixed bottom-0 end-0 m-3`;
        alertDiv.style.zIndex = 9999;
        alertDiv.textContent = message;
        document.body.appendChild(alertDiv);
        setTimeout(() => alertDiv.remove(), 3000);
    }

    function renderScannedItems() {
        const list = document.getElementById('recentItemsList');
        if (scannedItems.length === 0) {
            list.innerHTML = `
                <div class="recent-item">
                    <div class="recent-item-header">Recently Scanned Items</div>
                    <div class="recent-item-status">Ready to Scan</div>
                </div>
            `;
            return;
        }

        list.innerHTML = '';
        scannedItems.slice().reverse().forEach((scanItem, index) => {
            const div = document.createElement('div');
            
            // Enhanced status handling for task completion
            let statusClass, statusText, statusIcon;
            if (scanItem.task_completed) {
                statusClass = 'text-success';
                statusText = 'Task Completed ✓';
                statusIcon = '✅';
            } else if (scanItem.processed) {
                statusClass = 'text-success';
                statusText = 'Processed';
                statusIcon = '✓';
            } else if (scanItem.error) {
                statusClass = 'text-danger';
                statusText = 'Error';
                statusIcon = '❌';
            } else {
                statusClass = 'text-primary';
                statusText = 'Pending';
                statusIcon = '⏳';
            }
            
            div.className = `recent-item ${scanItem.task_completed ? 'border-success' : ''}`;
            div.innerHTML = `
                <div class="recent-item-header">
                    <span class="me-2">${statusIcon}</span>
                    ${scanItem.item ? escapeHtml(scanItem.item.name) : escapeHtml(scanItem.barcode)}
                    <span class="badge bg-secondary ms-2">${escapeHtml(scanItem.type.toUpperCase())}</span>
                    ${scanItem.task_completed ? '<span class="badge bg-success ms-1">TASK</span>' : ''}
                </div>
                <div class="recent-item-status">
                    <div>SKU: ${scanItem.item ? escapeHtml(scanItem.item.sku) : 'Unknown'}</div>
                    <div>Current Stock: ${scanItem.item ? scanItem.item.quantity : 'N/A'}</div>
                    ${scanItem.task_reference ? `<div>Reference: ${escapeHtml(scanItem.task_reference)}</div>` : ''}
                    <div class="${statusClass}"><strong>Status: ${statusText}</strong></div>
                    <small class="text-muted">${escapeHtml(scanItem.time)}</small>
                </div>
                ${!scanItem.processed && !scanItem.error && !scanItem.task_completed ? `
                    <div class="mt-2">
                        <div class="input-group input-group-sm">
                            <input type="number" class="form-control quantity-input" 
                                   placeholder="Quantity" min="1" value="1" 
                                   data-index="${scannedItems.length - 1 - index}">
                            <button class="btn btn-outline-danger btn-sm remove-item" 
                                    data-index="${scannedItems.length - 1 - index}">Remove</button>
                        </div>
                    </div>
                ` : ''}
            `;
            list.appendChild(div);
        });

        // Add event listeners for quantity inputs and remove buttons
        list.querySelectorAll('.quantity-input').forEach(input => {
            input.addEventListener('change', function() {
                const index = parseInt(this.dataset.index);
                scannedItems[index].quantity = parseInt(this.value) || 1;
            });
        });

        list.querySelectorAll('.remove-item').forEach(button => {
            button.addEventListener('click', function() {
                const index = parseInt(this.dataset.index);
                scannedItems.splice(index, 1);
                renderScannedItems();
            });
        });
    }

    // Save & Update button
    document.getElementById('btnSaveUpdate').addEventListener('click', async function() {
        const pendingItems = scannedItems.filter(item => !item.processed && !item.error);
        
        if (pendingItems.length === 0) {
            showAlert('No pending items to process.', 'warning');
            return;
        }

        const button = this;
        button.disabled = true;
        button.textContent = 'Processing...';

        try {
            let successCount = 0;
            let errorCount = 0;

            for (const scanItem of pendingItems) {
                try {
                    await processStockMovement(scanItem);
                    scanItem.processed = true;
                    successCount++;
                } catch (error) {
                    console.error('Error processing item:', error);
                    scanItem.error = error.message;
                    errorCount++;
                }
            }

            renderScannedItems();
            
            if (successCount > 0) {
                showAlert(`Processed ${successCount} item(s) successfully!`, 'success');
            }
            if (errorCount > 0) {
                showAlert(`${errorCount} item(s) failed to process.`, 'warning');
            }

        } catch (error) {
            console.error('Batch processing error:', error);
            showAlert('Error processing items. Please try again.', 'danger');
        } finally {
            button.disabled = false;
            button.textContent = 'Save & Update';
        }
    });

    async function processStockMovement(scanItem) {
        const quantity = scanItem.quantity || 1;
        let newQuantity = scanItem.item.quantity;

        // Calculate new quantity based on scan type
        switch (scanItem.type) {
            case 'inbound':
                newQuantity += quantity;
                break;
            case 'outbound':
                newQuantity -= quantity;
                if (newQuantity < 0) {
                    throw new Error('Insufficient stock for outbound scan');
                }
                break;
            case 'transfer':
                // For transfers, we would need additional logic for destination warehouse
                // For now, just treat as outbound from current warehouse
                newQuantity -= quantity;
                if (newQuantity < 0) {
                    throw new Error('Insufficient stock for transfer');
                }
                break;
        }

        // Update inventory via API
        const response = await fetch('<?= site_url('api/inventory/update-stock') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                item_id: scanItem.item.id,
                new_quantity: newQuantity,
                warehouse_id: scanItem.warehouse_id,
                scan_type: scanItem.type,
                quantity_changed: quantity
            })
        });

        if (!response.ok) {
            const error = await response.json();
            throw new Error(error.error || 'Failed to update stock');
        }

        // Update local item data
        scanItem.item.quantity = newQuantity;
    }

    function escapeHtml(s) {
        if (!s) return '';
        return String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    }
})();
</script>
</body>
</html>
