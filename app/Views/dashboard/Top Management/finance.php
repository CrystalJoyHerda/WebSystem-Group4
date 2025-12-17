<?= view('dashboard/Top Management/_shell_start', ['title' => $title ?? 'Finance Overview', 'active' => $active ?? 'top-management/finance']) ?>

<div class="systems-grid">
    <div class="system-card">
        <h3>Accounts Payable</h3>
        <div class="alert-number" id="apTotal">0.00</div>
        <div class="status-message">Total Amount (AP)</div>
    </div>
    <div class="system-card">
        <h3>Accounts Receivable</h3>
        <div class="alert-number" id="arTotal">0.00</div>
        <div class="status-message">Total Amount Due (AR)</div>
    </div>
    <div class="system-card">
        <h3>Purchase Orders</h3>
        <div class="alert-number" id="poTotal">0.00</div>
        <div class="status-message">Total Amount (PO)</div>
    </div>
</div>

<div class="alerts-grid">
    <div class="alert-card">
        <h3>AP Status Counts</h3>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Status</th>
                        <th style="width:120px" class="text-end">Count</th>
                    </tr>
                </thead>
                <tbody id="apBody">
                    <tr><td colspan="2" class="text-center text-muted p-3">Loading...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="alert-card">
        <h3>AR Status Counts</h3>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Status</th>
                        <th style="width:120px" class="text-end">Count</th>
                    </tr>
                </thead>
                <tbody id="arBody">
                    <tr><td colspan="2" class="text-center text-muted p-3">Loading...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <h3 class="mb-3" style="font-size:18px;font-weight:600;">Purchase Order Status Counts</h3>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Status</th>
                        <th style="width:120px" class="text-end">Count</th>
                    </tr>
                </thead>
                <tbody id="poBody">
                    <tr><td colspan="2" class="text-center text-muted p-3">Loading...</td></tr>
                </tbody>
            </table>
        </div>
        <div class="status-message" id="financeNote"></div>
    </div>
</div>

<script>
    function money(n) {
        const v = Number(n || 0);
        return v.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
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

    async function loadFinance() {
        const payload = await fetchJson('<?= site_url('api/top/finance/summary') ?>');
        const ap = payload && payload.ap ? payload.ap : {};
        const ar = payload && payload.ar ? payload.ar : {};
        const po = payload && payload.po ? payload.po : {};

        document.getElementById('apTotal').textContent = money(ap.total_amount || 0);
        document.getElementById('arTotal').textContent = money(ar.total_amount_due || 0);
        document.getElementById('poTotal').textContent = money(po.total_amount || 0);

        const apBody = document.getElementById('apBody');
        const arBody = document.getElementById('arBody');
        const poBody = document.getElementById('poBody');
        apBody.innerHTML = '';
        arBody.innerHTML = '';
        poBody.innerHTML = '';

        ['pending','approved','paid','overdue'].forEach(k => {
            const tr = document.createElement('tr');
            tr.innerHTML = `<td>${k}</td><td class="text-end">${Number(ap[k] || 0)}</td>`;
            apBody.appendChild(tr);
        });

        ['unpaid','paid','overdue'].forEach(k => {
            const tr = document.createElement('tr');
            tr.innerHTML = `<td>${k}</td><td class="text-end">${Number(ar[k] || 0)}</td>`;
            arBody.appendChild(tr);
        });

        ['pending','approved','received','canceled'].forEach(k => {
            const tr = document.createElement('tr');
            tr.innerHTML = `<td>${k}</td><td class="text-end">${Number(po[k] || 0)}</td>`;
            poBody.appendChild(tr);
        });

        const note = document.getElementById('financeNote');
        if (note) note.textContent = 'This page shows high-level counts and totals. Detailed invoice encoding remains under finance roles.';
    }

    function attachHandlers() {
        const btn = document.getElementById('btnRefresh');
        if (btn) btn.addEventListener('click', () => loadFinance());
        const select = document.getElementById('warehouseSelect');
        if (select) select.addEventListener('change', () => loadFinance());
    }

    (async function init() {
        try {
            await loadWarehouses();
            attachHandlers();
            await loadFinance();
        } catch (e) {
            alert(e && e.message ? e.message : String(e));
        }
    })();
</script>

<?= view('dashboard/Top Management/_shell_end') ?>
