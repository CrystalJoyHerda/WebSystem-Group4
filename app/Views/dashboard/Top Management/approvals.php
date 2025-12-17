<?= view('dashboard/Top Management/_shell_start', ['title' => $title ?? 'Approvals Center', 'active' => $active ?? 'top-management/approvals']) ?>

<div class="alerts-grid">
    <div class="alert-card">
        <h3>Pending Transfer Approvals</h3>
        <div class="small text-muted" id="transferPendingMeta" style="margin-bottom:10px;"></div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:90px">ID</th>
                        <th>Item</th>
                        <th style="width:160px">From</th>
                        <th style="width:160px">To</th>
                        <th style="width:90px" class="text-end">Qty</th>
                        <th style="width:170px">Action</th>
                    </tr>
                </thead>
                <tbody id="transferPendingBody">
                    <tr><td colspan="6" class="text-center text-muted p-3">Loading...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="alert-card">
        <h3>Pending Purchase Orders</h3>
        <div class="small text-muted" id="poPendingMeta" style="margin-bottom:10px;"></div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:110px">PO #</th>
                        <th>Item</th>
                        <th style="width:140px">Vendor</th>
                        <th style="width:90px" class="text-end">Qty</th>
                        <th style="width:110px" class="text-end">Total</th>
                        <th style="width:170px">Action</th>
                    </tr>
                </thead>
                <tbody id="poPendingBody">
                    <tr><td colspan="6" class="text-center text-muted p-3">Loading...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    function escapeHtml(str) {
        return String(str ?? '').replace(/[&<>"']/g, (m) => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;','\'':'&#039;'}[m]));
    }

    function money(n) {
        const v = Number(n || 0);
        return v.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function warehouseQueryParam() {
        const wid = document.getElementById('warehouseSelect')?.value || '';
        return wid ? `warehouse_id=${encodeURIComponent(wid)}` : '';
    }

    async function fetchJson(url) {
        const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
        const data = await res.json().catch(() => null);
        if (!res.ok) {
            const msg = data && data.error ? data.error : `Request failed (${res.status})`;
            throw new Error(msg);
        }
        return data;
    }

    async function loadWarehouses() {
        const select = document.getElementById('warehouseSelect');
        if (!select) return;
        const current = select.value;

        const payload = await fetchJson('<?= site_url('api/top/warehouses') ?>');
        const warehouses = (payload && Array.isArray(payload.warehouses)) ? payload.warehouses : [];

        select.innerHTML = '<option value="">All Warehouses</option>';
        warehouses.forEach(w => {
            const opt = document.createElement('option');
            opt.value = w.id;
            opt.textContent = w.location ? `${w.name} (${w.location})` : (w.name || `Warehouse #${w.id}`);
            select.appendChild(opt);
        });
        if (current) select.value = current;
    }

    async function decideTransfer(id, action) {
        const notes = prompt('Optional notes:', '') ?? '';
        const res = await fetch(`<?= site_url('api/top/transfers') ?>/${encodeURIComponent(id)}/decide`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify({ action, notes })
        });
        const data = await res.json().catch(() => null);
        if (!res.ok) {
            throw new Error(data && data.error ? data.error : `Request failed (${res.status})`);
        }
        return data;
    }

    async function decidePO(id, action) {
        const res = await fetch(`<?= site_url('api/top/purchase-orders') ?>/${encodeURIComponent(id)}/decide`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify({ action })
        });
        const data = await res.json().catch(() => null);
        if (!res.ok) {
            throw new Error(data && data.error ? data.error : `Request failed (${res.status})`);
        }
        return data;
    }

    async function loadPendingTransfers() {
        const qp = warehouseQueryParam();
        const url = qp ? `<?= site_url('api/top/pending-transfers') ?>?${qp}` : `<?= site_url('api/top/pending-transfers') ?>`;
        const payload = await fetchJson(url);
        const rows = (payload && Array.isArray(payload.transfers)) ? payload.transfers : [];

        const meta = document.getElementById('transferPendingMeta');
        if (meta) meta.textContent = rows.length ? `${rows.length} pending` : '0 pending';

        const body = document.getElementById('transferPendingBody');
        body.innerHTML = '';
        if (!rows.length) {
            body.innerHTML = '<tr><td colspan="6" class="text-center text-muted p-3">No pending transfers</td></tr>';
            return;
        }

        rows.forEach(t => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${escapeHtml(t.id)}</td>
                <td>
                    <div class="fw-semibold">${escapeHtml(t.item_name || 'Item')}</div>
                    <div class="small text-muted">${escapeHtml(t.item_sku || '')}</div>
                </td>
                <td>${escapeHtml(t.from_warehouse_name || t.from_warehouse_id || '')}</td>
                <td>${escapeHtml(t.to_warehouse_name || t.to_warehouse_id || '')}</td>
                <td class="text-end">${escapeHtml(t.quantity || 0)}</td>
                <td>
                    <div class="d-flex gap-1">
                        <button class="btn btn-sm btn-outline-success" data-t-action="approve" data-t-id="${escapeHtml(t.id)}">Approve</button>
                        <button class="btn btn-sm btn-outline-danger" data-t-action="reject" data-t-id="${escapeHtml(t.id)}">Reject</button>
                    </div>
                </td>
            `;
            body.appendChild(tr);
        });
    }

    async function loadPendingPOs() {
        const qp = warehouseQueryParam();
        const url = qp ? `<?= site_url('api/top/purchase-orders/pending') ?>?${qp}` : `<?= site_url('api/top/purchase-orders/pending') ?>`;
        const payload = await fetchJson(url);
        const rows = (payload && Array.isArray(payload.purchase_orders)) ? payload.purchase_orders : [];

        const meta = document.getElementById('poPendingMeta');
        if (meta) meta.textContent = rows.length ? `${rows.length} pending` : '0 pending';

        const body = document.getElementById('poPendingBody');
        body.innerHTML = '';
        if (!rows.length) {
            body.innerHTML = '<tr><td colspan="6" class="text-center text-muted p-3">No pending purchase orders</td></tr>';
            return;
        }

        rows.forEach(po => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${escapeHtml(po.order_number || po.purchase_order_id)}</td>
                <td>
                    <div class="fw-semibold">${escapeHtml(po.item_name || 'Item')}</div>
                    <div class="small text-muted">${escapeHtml(po.warehouse_name || '')}</div>
                </td>
                <td>${escapeHtml(po.vendor || '')}</td>
                <td class="text-end">${escapeHtml(po.quantity || 0)}</td>
                <td class="text-end">${escapeHtml(money(po.total_amount || 0))}</td>
                <td>
                    <div class="d-flex gap-1">
                        <button class="btn btn-sm btn-outline-success" data-po-action="approve" data-po-id="${escapeHtml(po.purchase_order_id)}">Approve</button>
                        <button class="btn btn-sm btn-outline-danger" data-po-action="reject" data-po-id="${escapeHtml(po.purchase_order_id)}">Reject</button>
                    </div>
                </td>
            `;
            body.appendChild(tr);
        });
    }

    function attachHandlers() {
        const select = document.getElementById('warehouseSelect');
        const btn = document.getElementById('btnRefresh');
        if (select) select.addEventListener('change', () => reloadAll());
        if (btn) btn.addEventListener('click', () => reloadAll());

        document.getElementById('transferPendingBody').addEventListener('click', async (e) => {
            const t = e.target;
            const id = t?.dataset?.tId;
            const action = t?.dataset?.tAction;
            if (!id || !action) return;
            if (!confirm(`${action === 'approve' ? 'Approve' : 'Reject'} transfer #${id}?`)) return;
            try {
                t.disabled = true;
                await decideTransfer(id, action);
                await loadPendingTransfers();
            } catch (err) {
                alert(err && err.message ? err.message : String(err));
            } finally {
                t.disabled = false;
            }
        });

        document.getElementById('poPendingBody').addEventListener('click', async (e) => {
            const t = e.target;
            const id = t?.dataset?.poId;
            const action = t?.dataset?.poAction;
            if (!id || !action) return;
            if (!confirm(`${action === 'approve' ? 'Approve' : 'Reject'} PO #${id}?`)) return;
            try {
                t.disabled = true;
                await decidePO(id, action);
                await loadPendingPOs();
            } catch (err) {
                alert(err && err.message ? err.message : String(err));
            } finally {
                t.disabled = false;
            }
        });
    }

    async function reloadAll() {
        try {
            await Promise.all([loadPendingTransfers(), loadPendingPOs()]);
        } catch (e) {
            alert(e && e.message ? e.message : String(e));
        }
    }

    (async function init() {
        try {
            await loadWarehouses();
            attachHandlers();
            await reloadAll();
        } catch (e) {
            alert(e && e.message ? e.message : String(e));
        }
    })();
</script>

<?= view('dashboard/Top Management/_shell_end') ?>
