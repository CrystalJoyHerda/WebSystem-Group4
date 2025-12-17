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
    <link href="<?= base_url('public/assets/theme.css') ?>" rel="stylesheet">
    <script src="<?= base_url('public/assets/theme.js') ?>" defer></script>
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
        <div class="header-right" style="text-align:right;margin-bottom:20px">
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
    let currentTaskId = null;
    let videoStream = null;
    let barcodeDetector = null;
    let scanningActive = false;

    const scannerModal = new bootstrap.Modal(document.getElementById('scannerModal'));

    // Load warehouses and tasks on page load
    document.addEventListener('DOMContentLoaded', function() {
        loadWarehouses();
        loadPendingTasks();
        loadRecentScans();
        
        // Set up refresh button
        document.getElementById('refreshTasks').addEventListener('click', loadPendingTasks);
        // Poll for new tasks every 5 seconds so manager-created tasks appear promptly
        setInterval(() => {
            if (document.visibilityState === 'visible') {
                loadPendingTasks();
            }
        }, 5000);
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
            // Status handling: mark RED STOCK items prominently
            const statusLabel = (task.status && task.status.toUpperCase() === 'RED STOCK') ? 'RED STOCK' : (task.status || 'Pending');
            const statusClass = (task.status && task.status.toUpperCase() === 'RED STOCK') ? 'text-danger' : 'text-muted';

            return `
                <div class="task-item border rounded p-3 mb-2 ${task.status && task.status.toUpperCase() === 'RED STOCK' ? 'border-danger' : ''}" data-task-id="${task.id}">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center mb-2">
                                <span class="me-2">${icon}</span>
                                <strong>${task.reference_no}</strong>
                                <span class="badge ${badgeClass} ms-2">${task.movement_type}</span>
                                ${task.status && task.status.toUpperCase() === 'RED STOCK' ? '<span class="badge bg-danger ms-2">RED STOCK</span>' : ''}
                            </div>
                            <div class="task-details">
                                <div><strong>${task.item_name}</strong></div>
                                <div class="${statusClass} small">
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

        // Add event listeners to scan buttons (show modal, pre-fill, do NOT auto-submit)
        container.querySelectorAll('.scan-task-btn').forEach(button => {
            button.addEventListener('click', function() {
                const taskId = this.dataset.taskId;
                const itemSku = this.dataset.itemSku;
                const warehouseId = this.dataset.warehouseId;

                // Set warehouse for context
                selectedWarehouse = warehouseId ? parseInt(warehouseId) : selectedWarehouse;
                const warehouseSel = document.getElementById('warehouseSelect');
                if (warehouseSel) warehouseSel.value = warehouseId;
                updateScanStatus();

                // Store current task id so Confirm will include it
                currentTaskId = taskId || null;

                // Pre-fill the manual barcode input with SKU so user can confirm or edit
                document.getElementById('manualBarcodeInput').value = itemSku || '';

                // Set the scan type to match the task movement, if provided
                const select = document.getElementById('scanType');
                if (select && this.closest('.task-item')) {
                    const movement = this.closest('.task-item').querySelector('.badge')?.textContent || '';
                    if (movement && movement.indexOf('IN') !== -1) select.value = 'inbound';
                    if (movement && movement.indexOf('OUT') !== -1) select.value = 'outbound';
                    currentScanType = select.value;
                    document.getElementById('scanTypeBtn').textContent = select.options[select.selectedIndex].text + ' ▼';
                }

                // Show scanner modal (user must click Confirm to submit)
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

    // Start scanning button - shows modal and starts camera/scanner (if available)
    document.getElementById('btnStartScan').addEventListener('click', function() {
        if (!selectedWarehouse) {
            showAlert('Please select a warehouse first', 'warning');
            return;
        }

        scannerModal.show();
        // start our scanner helper which requests camera and attempts detection
        startScanner();
    });

    // Manual barcode entry: do NOT auto-submit on Enter; user must click Confirm
    // (This prevents accidental updates and follows the requirement that a Submit/Scan click triggers updates.)

    // Confirm scan button - only on explicit click will we process the barcode
    document.getElementById('btnConfirmScan').addEventListener('click', function() {
        const barcode = document.getElementById('manualBarcodeInput').value.trim();
        if (barcode) {
            processBarcodeScan(barcode);
        } else {
            showAlert('Please enter a barcode or scan one.', 'warning');
        }
    });

    // Helper: start camera and barcode detector scanning (fills input but DOES NOT submit)
    async function startScanner() {
        if (scanningActive) return;
        scanningActive = true;

        const video = document.getElementById('videoElement');
        try {
            videoStream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } });
            video.srcObject = videoStream;
            await video.play();

            // Prefer native BarcodeDetector if available
            const supportedFormats = (window.BarcodeDetector && BarcodeDetector.getSupportedFormats) ? await BarcodeDetector.getSupportedFormats() : [];
            if (window.BarcodeDetector && supportedFormats.length > 0) {
                barcodeDetector = new BarcodeDetector({formats: supportedFormats});
            } else {
                barcodeDetector = null; // fallback: no automatic decode
            }

            // If detector available, run detection loop that fills the manual input when decoded
            if (barcodeDetector) {
                const canvas = document.createElement('canvas');
                const ctx = canvas.getContext('2d');

                const loop = async () => {
                    if (!scanningActive) return;
                    try {
                        canvas.width = video.videoWidth;
                        canvas.height = video.videoHeight;
                        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
                        const imageBitmap = await createImageBitmap(canvas);
                        const results = await barcodeDetector.detect(imageBitmap);
                        if (results && results.length > 0) {
                            // Fill input but do not submit; user must press Confirm
                            document.getElementById('manualBarcodeInput').value = results[0].rawValue;
                            showAlert('Barcode detected. Review and press Confirm to submit.', 'info');
                            // stop scanning further to conserve resources
                            stopScanner();
                            return;
                        }
                    } catch (err) {
                        // detection can throw on invalid frames; ignore
                    }
                    requestAnimationFrame(loop);
                };
                requestAnimationFrame(loop);
            } else {
                // No automatic detection available; inform user to use manual input
                showAlert('Camera ready. Manual entry supported if detection not available.', 'info');
            }
        } catch (err) {
            console.warn('Camera start failed:', err);
            showAlert('Unable to access camera. Please use manual entry.', 'warning');
            scanningActive = false;
        }
    }

    function stopScanner() {
        scanningActive = false;
        try {
            const video = document.getElementById('videoElement');
            if (video && video.srcObject) {
                const tracks = video.srcObject.getTracks ? video.srcObject.getTracks() : [];
                tracks.forEach(t => t.stop && t.stop());
            }
            if (video) video.srcObject = null;
        } catch (e) {
            console.warn('Error stopping scanner', e);
        }
        videoStream = null;
        barcodeDetector = null;
    }

    // Ensure scanner stops when modal hides
    document.getElementById('scannerModal').addEventListener('hidden.bs.modal', function() {
        stopScanner();
        // Clear currentTaskId when modal closed without confirm
        currentTaskId = null;
    });

    // Start scanner when modal shown (covers cases where modal is opened by task button)
    document.getElementById('scannerModal').addEventListener('shown.bs.modal', function() {
        // begin camera/scanning if not already active
        startScanner();
    });

    async function processBarcodeScan(barcode) {
        if (isProcessingScans) return;
        // Basic client-side validation
        if (!barcode || barcode.length < 3) {
            showAlert('Invalid barcode. Please verify the code before submitting.', 'warning');
            return;
        }
        if (!selectedWarehouse) {
            showAlert('Please select a warehouse before submitting scans.', 'warning');
            return;
        }
        
        isProcessingScans = true;
        showAlert('Processing scan...', 'info');
        
        try {
            // Use the new scan-item endpoint that handles the complete workflow
            const response = await fetch('<?= site_url('api/staff-tasks/scan-item') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    barcode: barcode,
                    warehouse_id: selectedWarehouse,
                    movement_type: currentScanType === 'inbound' ? 'IN' : 'OUT',
                    quantity: 1,
                    task_id: currentTaskId || null
                })
            });

            let result = null;
            try { result = await response.json(); } catch (e) { /* ignore parse errors */ }

            if (response.ok && result && result.success) {
                if (result.type === 'task_scan') {
                    // Task was found and moved to Recent Scans
                    showAlert(`✓ Moved from To-Do to Recent Scans: ${result.scan.item_name}`, 'success');
                    
                    // Refresh both lists
                    await loadPendingTasks();
                    await loadRecentScans();
                    
                } else if (result.type === 'manual_scan') {
                    // No task found, added as manual scan
                    showAlert(`✓ Added to Recent Scans: ${result.scan.item_name} (Manual)`, 'info');
                    await loadRecentScans();
                }

                // Clear input and close modal
                document.getElementById('manualBarcodeInput').value = '';
                // reset current task id after a successful submit
                currentTaskId = null;
                scannerModal.hide();
                return;
            }

            // Handle insufficient stock (server returns 409 and marks RED STOCK on task)
            if (response.status === 409) {
                const msg = (result && result.error) ? result.error : 'Insufficient stock for this export.';
                showAlert(msg, 'danger');
                // Stop scanner and refresh tasks so RED STOCK status appears
                stopScanner();
                currentTaskId = null;
                scannerModal.hide();
                await loadPendingTasks();
                return;
            }

            // Generic error
            const errMsg = (result && result.error) ? result.error : 'Item not found or server error';
            showAlert(`Error: ${errMsg}`, 'danger');
            // Clear input and close modal
            document.getElementById('manualBarcodeInput').value = '';
            currentTaskId = null;
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

    // Save & Update button (process recent scans persisted in DB)
    document.getElementById('btnSaveUpdate').addEventListener('click', async function() {
        const button = this;
        
        // Confirm action with user
        if (!confirm('This will update inventory and complete associated tasks. Continue?')) {
            return;
        }
        
        button.disabled = true;
        button.textContent = 'Processing...';
        button.classList.remove('btn-save-update');
        button.classList.add('btn-secondary');

        try {
            const response = await fetch('<?= site_url('api/recent-scans/save') ?>', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({})
            });

            const result = await response.json();
            if (response.ok && result.success) {
                // Show detailed success message
                let message = `✅ Successfully processed ${result.processed.length} item(s)!`;
                if (result.task_updates && result.task_updates.length > 0) {
                    message += `\n📋 Completed ${result.task_updates.length} task(s)`;
                }
                
                showAlert(message, 'success');
                
                // Refresh all relevant lists
                await loadRecentScans();
                await loadPendingTasks();
                
                // Show summary of what was processed
                if (result.processed.length > 0) {
                    console.log('Processed items:', result.processed);
                    const summary = result.processed.map(p => 
                        `${p.item_name}: ${p.movement} ${p.qty} (${p.old_stock}→${p.new_stock})`
                    ).join('\n');
                    
                    // Could show a detailed modal here if desired
                    setTimeout(() => {
                        showAlert('Inventory updated successfully!', 'info');
                    }, 2000);
                }
                
            } else {
                throw new Error(result.error || 'Failed to process recent scans');
            }
        } catch (err) {
            console.error('Save & Update error:', err);
            showAlert('❌ Failed to save & update: ' + (err.message || err), 'danger');
        } finally {
            button.disabled = false;
            button.textContent = 'Save & Update';
            button.classList.add('btn-save-update');
            button.classList.remove('btn-secondary');
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

    /* Recent Scans helpers */
    async function addScanToRecent(payload) {
        try {
            const response = await fetch('<?= site_url('api/recent-scans/add') ?>', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            return await response.json();
        } catch (e) {
            console.error('Failed to add recent scan', e);
            return { success: false };
        }
    }

    async function loadRecentScans() {
        try {
            const response = await fetch('<?= site_url('api/recent-scans/list') ?>');
            if (!response.ok) return;
            const data = await response.json();
            if (data.success) {
                renderRecentScans(data.scans || []);
            }
        } catch (e) {
            console.error('Failed to load recent scans', e);
        }
    }

    function renderRecentScans(scans) {
        const list = document.getElementById('recentItemsList');
        if (!scans || scans.length === 0) {
            list.innerHTML = `
                <div class="recent-item">
                    <div class="recent-item-header">Recently Scanned Items</div>
                    <div class="recent-item-status">No recent scans - items will appear here after scanning</div>
                </div>
            `;
            return;
        }

        // Build a Bootstrap table with enhanced information
        let html = `
            <div class="table-responsive">
            <table class="table table-sm table-striped">
                <thead>
                    <tr>
                        <th>Item Name</th>
                        <th>Barcode</th>
                        <th>Quantity</th>
                        <th>Movement Type</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
        `;

        scans.forEach(s => {
            const statusBadge = s.status === 'Pending' ? 
                '<span class="badge bg-warning">Pending</span>' : 
                '<span class="badge bg-success">Processed</span>';
            
            const movementBadge = s.movement_type === 'IN' ? 
                '<span class="badge bg-success">📥 IN</span>' : 
                '<span class="badge bg-warning">📤 OUT</span>';

            html += `
                <tr data-id="${s.id}" class="${s.status === 'Pending' ? '' : 'table-success'}">
                    <td>
                        <strong>${escapeHtml(s.item_name)}</strong>
                        <br><small class="text-muted">ID: ${s.item_id || 'N/A'}</small>
                    </td>
                    <td><code>${escapeHtml(s.item_sku)}</code></td>
                    <td>
                        <span class="badge bg-primary">${s.quantity}</span>
                    </td>
                    <td>${movementBadge}</td>
                    <td>${statusBadge}</td>
                    <td>
                        ${s.status === 'Pending' ? 
                            `<button class="btn btn-sm btn-outline-danger btn-remove-scan" data-id="${s.id}">Remove</button>` :
                            '<span class="text-muted">Processed</span>'
                        }
                    </td>
                </tr>
            `;
        });

        html += `</tbody></table></div>`;
        
        // Add summary info
        const pendingCount = scans.filter(s => s.status === 'Pending').length;
        const totalQty = scans.reduce((sum, s) => sum + (s.status === 'Pending' ? parseInt(s.quantity) : 0), 0);
        
        html += `
            <div class="mt-2 p-2 bg-light rounded">
                <small class="text-muted">
                    <strong>Summary:</strong> ${pendingCount} pending scan(s), ${totalQty} total items ready to update
                </small>
            </div>
        `;
        
        list.innerHTML = html;

        // Attach remove handlers
        list.querySelectorAll('.btn-remove-scan').forEach(btn => {
            btn.addEventListener('click', async function() {
                const id = this.dataset.id;
                if (!confirm('Remove this scan from Recent Scans?')) return;
                
                try {
                    const resp = await fetch('<?= site_url('api/recent-scans/remove') ?>/' + id, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' }
                    });
                    const result = await resp.json();
                    if (resp.ok && result.success) {
                        showAlert('Removed scan', 'info');
                        await loadRecentScans();
                    } else {
                        showAlert('Failed to remove scan', 'danger');
                    }
                } catch (e) {
                    console.error('Remove scan error', e);
                    showAlert('Error removing scan', 'danger');
                }
            });
        });
    }
})();
</script>
</body>
</html>
