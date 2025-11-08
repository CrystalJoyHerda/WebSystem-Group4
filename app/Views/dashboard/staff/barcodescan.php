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

            <!-- Right: Recently Scanned Items -->
            <div class="scan-right">
                <div class="scan-card recent-items-card">
                    <h5>Recently Scanned Items</h5>
                    
                    <div id="recentItemsList">
                        <!-- Placeholder items -->
                        <div class="recent-item">
                            <div class="recent-item-header">Recently Scanned Items</div>
                            <div class="recent-item-status">Ready to Scan</div>
                        </div>
                        <div class="recent-item">
                            <div class="recent-item-header">Recently Scanned Items</div>
                            <div class="recent-item-status">Ready to Scan</div>
                        </div>
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

    const scannerModal = new bootstrap.Modal(document.getElementById('scannerModal'));

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
                });
        }
    });

    // Manual barcode entry (Enter key)
    document.getElementById('manualBarcodeInput').addEventListener('keypress', function(e) {
        if (e.key === 'Enter' && this.value.trim()) {
            addScannedItem(this.value.trim());
            this.value = '';
        }
    });

    // Confirm scan button
    document.getElementById('btnConfirmScan').addEventListener('click', function() {
        const barcode = document.getElementById('manualBarcodeInput').value.trim();
        if (barcode) {
            addScannedItem(barcode);
            document.getElementById('manualBarcodeInput').value = '';
            scannerModal.hide();
        } else {
            alert('Please enter a barcode or scan one.');
        }
    });

    function addScannedItem(barcode) {
        const timestamp = new Date().toLocaleString();
        scannedItems.push({
            barcode: barcode,
            type: currentScanType,
            time: timestamp
        });
        renderScannedItems();
    }

    function renderScannedItems() {
        const list = document.getElementById('recentItemsList');
        if (scannedItems.length === 0) {
            list.innerHTML = `
                <div class="recent-item">
                    <div class="recent-item-header">Recently Scanned Items</div>
                    <div class="recent-item-status">Ready to Scan</div>
                </div>
                <div class="recent-item">
                    <div class="recent-item-header">Recently Scanned Items</div>
                    <div class="recent-item-status">Ready to Scan</div>
                </div>
                <div class="recent-item">
                    <div class="recent-item-header">Recently Scanned Items</div>
                    <div class="recent-item-status">Ready to Scan</div>
                </div>
            `;
            return;
        }

        list.innerHTML = '';
        scannedItems.slice().reverse().forEach(item => {
            const div = document.createElement('div');
            div.className = 'recent-item';
            div.innerHTML = `
                <div class="recent-item-header">${escapeHtml(item.barcode)}</div>
                <div class="recent-item-status">${escapeHtml(item.type)} - ${escapeHtml(item.time)}</div>
            `;
            list.appendChild(div);
        });
    }

    // Save & Update button
    document.getElementById('btnSaveUpdate').addEventListener('click', function() {
        if (scannedItems.length === 0) {
            alert('No items scanned yet.');
            return;
        }

        // In production, send scannedItems to server via fetch/AJAX
        console.log('Saving scanned items:', scannedItems);
        
        const msg = document.createElement('div');
        msg.className = 'alert alert-success position-fixed bottom-0 end-0 m-3';
        msg.style.zIndex = 9999;
        msg.textContent = `Saved ${scannedItems.length} scanned item(s)!`;
        document.body.appendChild(msg);
        setTimeout(() => msg.remove(), 2000);

        // Clear after save (optional)
        // scannedItems = [];
        // renderScannedItems();
    });

    function escapeHtml(s) {
        if (!s) return '';
        return String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    }
})();
</script>
</body>
</html>
