<?php /**
 * Shared Create Outbound modal + JS
 * Used by the global manager page and Warehouse2 manager page.
 */ ?>

<!-- Create Outbound Modal (shared) -->
<div class="modal fade" id="createOutboundModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create Outbound Shipment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="createOutboundForm">
                    <div class="mb-3">
                        <label class="form-label">Product Name</label>
                        <select id="outboundProduct" class="form-select">
                            <option value="">Select product...</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Product Code</label>
                        <input type="text" id="outboundProductCode" class="form-control" readonly />
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Quantity to outbound</label>
                        <input type="number" id="outboundQuantity" class="form-control" min="1" value="1" />
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Destination</label>
                        <input type="text" id="outboundDestination" class="form-control" placeholder="Warehouse / Customer / Location" />
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Date</label>
                        <input type="date" id="outboundDate" class="form-control" />
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Remarks / Purpose</label>
                        <textarea id="outboundRemarks" class="form-control" rows="3" placeholder="Optional remarks"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="btnSubmitOutbound">Submit Outbound</button>
            </div>
        </div>
    </div>
</div>

<script>
(function(){
    // Ensure the modal DOM node is appended to <body> so native select dropdowns
    // are not clipped by transformed/overflowed parent containers.
    const createOutboundModalEl = document.getElementById('createOutboundModal');
    if (!createOutboundModalEl) return;
    try {
        if (createOutboundModalEl.parentNode !== document.body) {
            document.body.appendChild(createOutboundModalEl);
        }
    } catch (e) {
        // ignore if move fails
    }

    const createOutboundModal = new bootstrap.Modal(createOutboundModalEl);
    const outboundProductEl = document.getElementById('outboundProduct');
    const outboundProductCodeEl = document.getElementById('outboundProductCode');
    const outboundQuantityEl = document.getElementById('outboundQuantity');
    const outboundDestinationEl = document.getElementById('outboundDestination');
    const outboundDateEl = document.getElementById('outboundDate');
    const outboundRemarksEl = document.getElementById('outboundRemarks');
    const btnSubmitOutbound = document.getElementById('btnSubmitOutbound');

    async function loadProducts() {
        try {
            const resp = await fetch('<?= site_url('api/inventory/all-with-warehouse') ?>');
            if (resp.ok) {
                const items = await resp.json();
                const products = (items || []).map(it => ({ id: it.id || it.item_id, name: it.name || it.item_name || it.item_sku || ('Item ' + (it.id||'')), sku: it.sku || it.item_sku || '' }));
                populateProductSelect(products);
                return;
            }
        } catch (e) {
            // ignore and fallback
        }

        // Fallback: no products loaded
    }

    function populateProductSelect(products) {
        if (!Array.isArray(products) || !outboundProductEl) return;
        const seen = new Set();
        const opts = [];
        products.forEach(p => {
            if (!p || !p.id) return;
            if (seen.has(String(p.id))) return;
            seen.add(String(p.id));
            const name = p.name || (p.sku ? p.sku : 'Unnamed Product');
            const sku = p.sku || '';
            opts.push(`\n                <option value="${p.id}" data-sku="${sku}">${name}</option>`);
        });
        outboundProductEl.innerHTML = '<option value="">Select product...</option>' + opts.join('');
    }

    // Auto-fill product code when product selected
    if (outboundProductEl) {
        outboundProductEl.addEventListener('change', function() {
            const opt = this.options[this.selectedIndex];
            const sku = opt ? opt.dataset.sku || '' : '';
            if (outboundProductCodeEl) outboundProductCodeEl.value = sku;
        });
    }

    // Wire up open buttons (if present)
    document.querySelectorAll('#btnCreateOutbound').forEach(btn => {
        btn.addEventListener('click', function() {
            if (outboundProductEl) outboundProductEl.value = '';
            if (outboundProductCodeEl) outboundProductCodeEl.value = '';
            if (outboundQuantityEl) outboundQuantityEl.value = '1';
            if (outboundDestinationEl) outboundDestinationEl.value = '';
            if (outboundDateEl) outboundDateEl.value = new Date().toISOString().slice(0,10);
            if (outboundRemarksEl) outboundRemarksEl.value = '';
            createOutboundModal.show();
        });
    });

    if (btnSubmitOutbound) {
        btnSubmitOutbound.addEventListener('click', async function() {
            const productId = outboundProductEl ? outboundProductEl.value : null;
            const opt = outboundProductEl ? outboundProductEl.options[outboundProductEl.selectedIndex] : null;
            const productCode = opt ? (opt.dataset.sku || '') : '';
            const qty = outboundQuantityEl ? parseInt(outboundQuantityEl.value || '0', 10) : 0;
            const destination = outboundDestinationEl ? outboundDestinationEl.value.trim() : '';
            const date = outboundDateEl ? outboundDateEl.value : '';
            const remarks = outboundRemarksEl ? outboundRemarksEl.value.trim() : '';

            if (!productId) { alert('Please select a product'); return; }
            if (!qty || qty <= 0) { alert('Please enter a valid quantity'); return; }
            if (!destination) { alert('Please enter a destination'); return; }

            this.disabled = true; this.textContent = 'Submitting...';
            try {
                const resp = await fetch('<?= site_url('stockmovements/createOutbound') ?>', {
                    method: 'POST', headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ product_id: productId, product_code: productCode, quantity: qty, destination: destination, date: date, remarks: remarks })
                });
                const result = await resp.json().catch(()=>({}));
                if (resp.ok && result.success) {
                    createOutboundModal.hide();
                    setTimeout(function(){ location.reload(); }, 700);
                } else {
                    alert(result.error || result.message || 'Failed to create outbound');
                }
            } catch (err) {
                console.error(err); alert('Failed to create outbound');
            } finally { this.disabled = false; this.textContent = 'Submit Outbound'; }
        });
    }

    // Load products on ready
    document.addEventListener('DOMContentLoaded', loadProducts);
})();
</script>
