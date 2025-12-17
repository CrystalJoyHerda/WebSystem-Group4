<?= view('dashboard/Top Management/_shell_start', ['title' => $title ?? 'Inventory Oversight', 'active' => $active ?? 'top-management/inventory']) ?>

<div class="systems-grid">
    <div class="system-card">
        <h3>Total Items</h3>
        <div class="alert-number" id="invTotalItems">0</div>
        <div class="status-message success">Tracked</div>
    </div>
    <div class="system-card">
        <h3>Total Quantity</h3>
        <div class="alert-number" id="invTotalQty">0</div>
        <div class="status-message success">Tracked</div>
    </div>
    <div class="system-card">
        <h3>Low Stock Items</h3>
        <div class="alert-number" id="invLowCount">0</div>
        <div class="status-message warning" id="invThresholdLabel">Needs attention</div>
    </div>
</div>

<div class="alerts-grid">
    <div class="alert-card">
        <h3>Inventory by Category</h3>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Category</th>
                        <th style="width:110px" class="text-end">Items</th>
                        <th style="width:110px" class="text-end">Qty</th>
                    </tr>
                </thead>
                <tbody id="catBody">
                    <tr><td colspan="3" class="text-center text-muted p-3">Loading...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="alert-card">
        <h3>Low Stock List</h3>
        <div class="small text-muted" id="lowStockMeta" style="margin-bottom:10px;"></div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:90px">ID</th>
                        <th>Item</th>
                        <th style="width:140px">Warehouse</th>
                        <th style="width:90px" class="text-end">Qty</th>
                        <th style="width:90px">Status</th>
                    </tr>
                </thead>
                <tbody id="lowBody">
                    <tr><td colspan="5" class="text-center text-muted p-3">Loading...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    function escapeHtml(str) {
        return String(str ?? '').replace(/[&<>"']/g, (m) => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;','\'':'&#039;'}[m]));
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

    async function loadInventory() {
        const qp = warehouseQueryParam();
        const url = qp ? `<?= site_url('api/top/inventory/overview') ?>?${qp}` : `<?= site_url('api/top/inventory/overview') ?>`;
        const payload = await fetchJson(url);

        const m = payload && payload.metrics ? payload.metrics : {};
        document.getElementById('invTotalItems').textContent = String(m.total_items ?? 0);
        document.getElementById('invTotalQty').textContent = String(m.total_quantity ?? 0);
        document.getElementById('invLowCount').textContent = String(m.low_stock_count ?? 0);

        const threshold = payload && payload.threshold ? payload.threshold : 10;
        const thEl = document.getElementById('invThresholdLabel');
        if (thEl) thEl.textContent = `Threshold ≤ ${threshold}`;

        const byCat = Array.isArray(payload.by_category) ? payload.by_category : [];
        const catBody = document.getElementById('catBody');
        catBody.innerHTML = '';
        if (!byCat.length) {
            catBody.innerHTML = '<tr><td colspan="3" class="text-center text-muted p-3">No data</td></tr>';
        } else {
            byCat.forEach(r => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${escapeHtml(r.category || 'Uncategorized')}</td>
                    <td class="text-end">${escapeHtml(r.items || 0)}</td>
                    <td class="text-end">${escapeHtml(r.qty || 0)}</td>
                `;
                catBody.appendChild(tr);
            });
        }
    }

    async function loadLowStock() {
        const qp = warehouseQueryParam();
        const url = qp ? `<?= site_url('api/top/inventory/low-stock') ?>?${qp}&limit=50` : `<?= site_url('api/top/inventory/low-stock') ?>?limit=50`;
        const payload = await fetchJson(url);

        const threshold = payload && payload.threshold ? payload.threshold : 10;
        const items = (payload && Array.isArray(payload.items)) ? payload.items : [];

        const meta = document.getElementById('lowStockMeta');
        if (meta) meta.textContent = `Showing items with quantity ≤ ${threshold}`;

        const body = document.getElementById('lowBody');
        body.innerHTML = '';
        if (!items.length) {
            body.innerHTML = '<tr><td colspan="5" class="text-center text-muted p-3">No low stock items</td></tr>';
            return;
        }

        items.forEach(i => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${escapeHtml(i.id)}</td>
                <td>
                    <div class="fw-semibold">${escapeHtml(i.name || '')}</div>
                    <div class="small text-muted">${escapeHtml(i.sku || '')}</div>
                </td>
                <td>${escapeHtml(i.warehouse_name || i.warehouse_id || '')}</td>
                <td class="text-end">${escapeHtml(i.quantity || 0)}</td>
                <td>${escapeHtml(i.status || '')}</td>
            `;
            body.appendChild(tr);
        });
    }

    function attachHandlers() {
        const select = document.getElementById('warehouseSelect');
        const btn = document.getElementById('btnRefresh');
        if (select) select.addEventListener('change', () => reloadAll());
        if (btn) btn.addEventListener('click', () => reloadAll());
    }

    async function reloadAll() {
        try {
            await Promise.all([loadInventory(), loadLowStock()]);
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
