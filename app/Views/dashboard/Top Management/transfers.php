<?= view('dashboard/Top Management/_shell_start', ['title' => $title ?? 'Transfers', 'active' => $active ?? 'top-management/transfers']) ?>

<div class="alerts-grid">
    <div class="alert-card">
        <h3>Filters</h3>
        <div class="row g-2">
            <div class="col-12 col-md-6">
                <label class="form-label small text-muted">Status</label>
                <select id="statusSelect" class="form-select form-select-sm">
                    <option value="">All</option>
                    <option value="pending">Pending</option>
                    <option value="approved">Approved</option>
                    <option value="completed">Completed</option>
                    <option value="rejected">Rejected</option>
                </select>
            </div>
            <div class="col-12 col-md-6 d-flex align-items-end">
                <div class="small text-muted" id="transferMeta"></div>
            </div>
        </div>
    </div>

    <div class="alert-card">
        <h3>Notes</h3>
        <div class="status-message">Use the warehouse selector to filter transfers for a specific warehouse, or select All.</div>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:90px">ID</th>
                        <th>Item</th>
                        <th style="width:160px">From</th>
                        <th style="width:160px">To</th>
                        <th style="width:100px" class="text-end">Qty</th>
                        <th style="width:120px">Status</th>
                        <th style="width:170px">Created</th>
                    </tr>
                </thead>
                <tbody id="transfersBody">
                    <tr><td colspan="7" class="text-center text-muted p-4">Loading...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    function escapeHtml(str) {
        return String(str ?? '').replace(/[&<>"']/g, (m) => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;','\'':'&#039;'}[m]));
    }

    function formatAbsoluteTime(s) {
        if (!s) return '';
        const d = new Date(String(s).replace(' ', 'T'));
        if (isNaN(d.getTime())) return String(s);
        const pad = (n) => String(n).padStart(2, '0');
        return `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())} ${pad(d.getHours())}:${pad(d.getMinutes())}:${pad(d.getSeconds())}`;
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

    async function loadTransfers() {
        const qp = warehouseQueryParam();
        const status = document.getElementById('statusSelect')?.value || '';
        const params = [];
        if (qp) params.push(qp);
        if (status) params.push(`status=${encodeURIComponent(status)}`);
        params.push('limit=100');

        const url = `<?= site_url('api/top/transfers/history') ?>?${params.join('&')}`;
        const payload = await fetchJson(url);
        const rows = (payload && Array.isArray(payload.transfers)) ? payload.transfers : [];

        const meta = document.getElementById('transferMeta');
        if (meta) meta.textContent = rows.length ? `${rows.length} records` : '';

        const body = document.getElementById('transfersBody');
        body.innerHTML = '';
        if (!rows.length) {
            body.innerHTML = '<tr><td colspan="7" class="text-center text-muted p-4">No transfers found</td></tr>';
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
                <td>${escapeHtml(t.status || '')}</td>
                <td>${escapeHtml(formatAbsoluteTime(t.created_at || ''))}</td>
            `;
            body.appendChild(tr);
        });
    }

    function attachHandlers() {
        const select = document.getElementById('warehouseSelect');
        const btn = document.getElementById('btnRefresh');
        const status = document.getElementById('statusSelect');
        if (select) select.addEventListener('change', () => loadTransfers());
        if (btn) btn.addEventListener('click', () => loadTransfers());
        if (status) status.addEventListener('change', () => loadTransfers());
    }

    (async function init() {
        try {
            await loadWarehouses();
            attachHandlers();
            await loadTransfers();
        } catch (e) {
            alert(e && e.message ? e.message : String(e));
        }
    })();
</script>

<?= view('dashboard/Top Management/_shell_end') ?>
