<?php $session = session(); ?>
<?= view('partials/sidebar') ?>
<div class="container-fluid" style="margin-left:220px;padding:28px">
    <h2>Transfers</h2>
    <div class="card p-3">
        <div id="transferForm">
            <label>Item ID</label>
            <input id="itemId" class="form-control" />
            <label>From Warehouse ID</label>
            <input id="fromWarehouse" class="form-control" />
            <label>To Warehouse ID</label>
            <input id="toWarehouse" class="form-control" />
            <label>Quantity</label>
            <input id="qty" class="form-control" />
            <button id="doTransfer" class="btn btn-primary mt-2">Transfer</button>
        </div>
        <div id="transferResult" class="mt-3"></div>
    </div>
</div>
<script>
document.getElementById('doTransfer').addEventListener('click', async ()=>{
    const payload = { item_id: document.getElementById('itemId').value, from_warehouse_id: document.getElementById('fromWarehouse').value, to_warehouse_id: document.getElementById('toWarehouse').value, quantity: document.getElementById('qty').value };
    const res = await fetch('<?= site_url('api/transfer/create') ?>', { method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify(payload)});
    const data = await res.json();
    document.getElementById('transferResult').textContent = JSON.stringify(data);
});
</script>
