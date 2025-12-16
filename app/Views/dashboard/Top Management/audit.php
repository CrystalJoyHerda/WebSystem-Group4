<?= view('dashboard/Top Management/_shell_start', ['title' => $title ?? 'Audit & Compliance', 'active' => $active ?? 'top-management/audit']) ?>

<div class="alert-card" style="margin-bottom:20px;">
    <h3>Audit Logs (Read-only)</h3>
    <div class="row g-2">
        <div class="col-12 col-md-6">
            <input id="auditSearch" class="form-control form-control-sm" placeholder="Search logs (actor/action/entity)" />
        </div>
        <div class="col-6 col-md-3">
            <input id="auditFrom" type="date" class="form-control form-control-sm" />
        </div>
        <div class="col-6 col-md-3">
            <input id="auditTo" type="date" class="form-control form-control-sm" />
        </div>
        <div class="col-12 col-md-6 d-flex align-items-center">
            <div class="small text-muted" id="auditMeta"></div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:90px">#</th>
                        <th style="width:170px">When</th>
                        <th style="width:220px">Actor</th>
                        <th style="width:140px">Action</th>
                        <th style="width:140px">Entity</th>
                        <th style="width:120px">Entity ID</th>
                        <th>Summary</th>
                    </tr>
                </thead>
                <tbody id="auditBody">
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

    async function loadAuditLogs() {
        const qp = warehouseQueryParam();
        const q = document.getElementById('auditSearch')?.value?.trim() || '';
        const from = document.getElementById('auditFrom')?.value || '';
        const to = document.getElementById('auditTo')?.value || '';

        const params = [];
        if (qp) params.push(qp);
        if (q) params.push(`q=${encodeURIComponent(q)}`);
        if (from) params.push(`from=${encodeURIComponent(from)}`);
        if (to) params.push(`to=${encodeURIComponent(to)}`);
        params.push('limit=100');

        const url = `<?= site_url('api/top/audit-logs') ?>?${params.join('&')}`;
        const payload = await fetchJson(url);
        const rows = (payload && Array.isArray(payload.logs)) ? payload.logs : [];

        const meta = document.getElementById('auditMeta');
        if (meta) meta.textContent = rows.length ? `${rows.length} records` : '0 records';

        const body = document.getElementById('auditBody');
        body.innerHTML = '';
        if (!rows.length) {
            body.innerHTML = '<tr><td colspan="7" class="text-center text-muted p-4">No logs found</td></tr>';
            return;
        }

        rows.forEach(r => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${escapeHtml(r.id)}</td>
                <td>${escapeHtml(formatAbsoluteTime(r.created_at || ''))}</td>
                <td>
                    <div class="fw-semibold">${escapeHtml(r.actor_name || '')}</div>
                    <div class="small text-muted">${escapeHtml(r.actor_email || '')}</div>
                </td>
                <td>${escapeHtml(r.action || '')}</td>
                <td>${escapeHtml(r.entity_type || '')}</td>
                <td>${escapeHtml(r.entity_id || '')}</td>
                <td>${escapeHtml(r.summary || '')}</td>
            `;
            body.appendChild(tr);
        });
    }

    function debounce(fn, ms) {
        let t;
        return (...args) => {
            clearTimeout(t);
            t = setTimeout(() => fn(...args), ms);
        };
    }

    function attachHandlers() {
        const select = document.getElementById('warehouseSelect');
        const btn = document.getElementById('btnRefresh');
        const search = document.getElementById('auditSearch');
        const from = document.getElementById('auditFrom');
        const to = document.getElementById('auditTo');

        if (select) select.addEventListener('change', () => loadAuditLogs());
        if (btn) btn.addEventListener('click', () => loadAuditLogs());
        if (search) search.addEventListener('input', debounce(() => loadAuditLogs(), 250));
        if (from) from.addEventListener('change', () => loadAuditLogs());
        if (to) to.addEventListener('change', () => loadAuditLogs());
    }

    (async function init() {
        try {
            await loadWarehouses();
            attachHandlers();
            await loadAuditLogs();
        } catch (e) {
            alert(e && e.message ? e.message : String(e));
        }
    })();
</script>

<?= view('dashboard/Top Management/_shell_end') ?>
