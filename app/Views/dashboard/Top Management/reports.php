<?= view('dashboard/Top Management/_shell_start', ['title' => $title ?? 'Reports', 'active' => $active ?? 'top-management/reports']) ?>

<div class="alert-card" style="margin-bottom:30px;">
    <h3>Download Reports (CSV)</h3>
    <div class="status-message">Use the warehouse selector to download per-warehouse reports, or select All Warehouses for consolidated.</div>
    <div class="d-flex flex-column gap-2" style="margin-top:12px; max-width: 380px;">
        <button class="btn btn-sm btn-outline-dark" data-report="inventory"><i class="fa-solid fa-file-csv me-1"></i>Inventory Report</button>
        <button class="btn btn-sm btn-outline-dark" data-report="transfers"><i class="fa-solid fa-file-csv me-1"></i>Transfers Report</button>
        <button class="btn btn-sm btn-outline-dark" data-report="approvals"><i class="fa-solid fa-file-csv me-1"></i>Pending Approvals Report</button>
        <button class="btn btn-sm btn-outline-dark" data-report="audit-logs"><i class="fa-solid fa-file-csv me-1"></i>Audit Logs Report</button>
    </div>
</div>

<div class="alert-card">
    <h3>Notes</h3>
    <div class="status-message">CSV reports open in Excel/Google Sheets and can be submitted as weekly/monthly summaries.</div>
</div>

<script>
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

    function reportUrl(kind) {
        const qp = warehouseQueryParam();
        const base = `<?= site_url('api/top/reports') ?>/${kind}`;
        return qp ? `${base}?${qp}` : base;
    }

    function attachHandlers() {
        const btnRefresh = document.getElementById('btnRefresh');
        if (btnRefresh) btnRefresh.addEventListener('click', () => {});

        document.querySelectorAll('[data-report]').forEach(btn => {
            btn.addEventListener('click', () => {
                const kind = btn.getAttribute('data-report');
                window.location.href = reportUrl(kind);
            });
        });
    }

    (async function init() {
        try {
            await loadWarehouses();
            attachHandlers();
        } catch (e) {
            alert(e && e.message ? e.message : String(e));
        }
    })();
</script>

<?= view('dashboard/Top Management/_shell_end') ?>
