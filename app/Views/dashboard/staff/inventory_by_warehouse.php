<?php $session = session(); ?>
<?= view('partials/sidebar') ?>
<div class="container-fluid" style="margin-left:220px;padding:28px">
    <h2>Inventory by Warehouse</h2>
    <div class="card p-3">
        <div>
            <label>Select Warehouse</label>
            <select id="warehouseSelect" class="form-control">
                <option value="">All Warehouses</option>
            </select>
        </div>
        <div id="itemsList" class="mt-3"></div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', async ()=>{
    const sel = document.getElementById('warehouseSelect');
    const res = await fetch('<?= site_url('dashboard/manager/warehouses') ?>');
    // This endpoint returns HTML view; use API route instead if available. For now, call /api/warehouse/list
    const api = await fetch('<?= site_url('api/warehouse/list') ?>');
    if (api.ok) {
        const list = await api.json();
        list.forEach(w => {
            const o = document.createElement('option'); o.value = w.id; o.textContent = w.name; sel.appendChild(o);
        });
    }

    sel.addEventListener('change', async ()=>{
        const wid = sel.value;
        const url = '<?= site_url('inventory') ?>' + (wid ? ('?warehouse_id=' + wid) : '');
        const r = await fetch(url);
        // Not ideal: inventory page returns HTML. For production, create API to fetch items by warehouse.
        location.href = url; // fallback - redirect to inventory listing filtered server-side
    });
});
</script>
