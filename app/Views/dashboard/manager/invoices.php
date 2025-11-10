<?php $session = session(); ?>
<?= view('partials/sidebar') ?>
<div class="container-fluid" style="margin-left:220px;padding:28px">
    <h2>Invoices (AP/AR)</h2>
    <div class="card p-3">
        <div id="invoicesList"></div>
    </div>
</div>
<script>
(async ()=>{
    const res = await fetch('<?= site_url('api/invoice/list') ?>');
    if (res.ok) {
        const list = await res.json();
        const el = document.getElementById('invoicesList');
        el.innerHTML = '<ul>' + list.map(i=>`<li>${i.reference} - ${i.amount} - ${i.status}</li>`).join('') + '</ul>';
    }
})();
</script>
