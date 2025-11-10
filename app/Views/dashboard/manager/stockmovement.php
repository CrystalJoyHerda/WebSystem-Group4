<?php
// any preparatory PHP logic here
$role = session() ? session()->get('role') ?? 'User' : 'User';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Stock Movements</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= base_url('css/site.css') ?>" rel="stylesheet">
    <link href="<?= base_url('css/manager.css') ?>" rel="stylesheet">
    <style>
        body { font-family: 'Times New Roman', serif; background: #fff; }
        .app-shell { display:flex; min-height:100vh; }
        /* fixed sidebar */
        .sidebar{
            width:220px;
            background:#ebeaea;
            padding:20px;
            border-right:1px solid #ddd;
            position:fixed;
            top:0;
            left:0;
            height:100vh;
            overflow:auto;
            z-index:10;
        }
        .main {
            margin-left:220px;
            flex:1;
            padding:28px;
        }
        .card-move { border-radius:10px; border:1px solid #e9e9e9; background:#fff; box-shadow:0 1px 2px rgba(0,0,0,0.03); }
        .card-move .card-body { padding:18px; }
        .movement-list { max-height:420px; overflow:auto; }
        .inbound-list, .outbound-list { max-height:200px; overflow:auto; }
        .page-title { font-size:28px; margin-bottom:18px; }
        .accepted-item { opacity: .6; }
        .approved-item { opacity: .6; }
        @media (max-width:991px){
            .sidebar{position:relative;height:auto;width:100%}
            .main{margin-left:0;padding:16px}
        }
    </style>
</head>
<body>
    <div class="app-shell">
        <?= view('partials/sidebar') ?>

        <main class="main">
            <div class="brand">WeBuild</div>
            <div class="page-title">Stock Movements</div>

            <div class="container-fluid">
                <div class="row gx-4 gy-4">
                    <!-- Movement History (big card) -->
                    <div class="col-12 col-lg-7">
                        <div class="card card-move">
                            <div class="card-body">
                                <h5 class="card-title">Movement History</h5>
                                <div class="movement-list mt-3">
                                    <table class="table table-sm mb-0">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Type</th>
                                                <th>SKU</th>
                                                <th class="text-end">Qty</th>
                                                <th>Ref</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr><td>2025-10-14</td><td>Inbound</td><td>PNT-5L-004</td><td class="text-end">50</td><td>PO-1234</td></tr>
                                            <tr><td>2025-10-13</td><td>Outbound</td><td>HB-M10-002</td><td class="text-end">100</td><td>SO-5678</td></tr>
                                            <tr><td>2025-10-12</td><td>Transfer</td><td>RF-SHN-01</td><td class="text-end">30</td><td>TX-4321</td></tr>
                                            <tr><td>2025-10-11</td><td>Inbound</td><td>FLR-VNL-01</td><td class="text-end">200</td><td>PO-1210</td></tr>
                                            <tr><td>2025-10-10</td><td>Outbound</td><td>ELC-WR-01</td><td class="text-end">40</td><td>SO-5599</td></tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right column: inbound + outbound -->
                    <div class="col-12 col-lg-5">
                        <div class="card card-move mb-4">
                            <div class="card-body">
                                <h5 class="card-title">Inbound Receipts</h5>
                                <div class="inbound-list mt-3">
                                    <ul class="list-unstyled mb-0">
                                        <li class="mb-3 d-flex align-items-start justify-content-between" data-ref="PO-1234">
                                            <div>
                                                <strong>PO-1234</strong><br>
                                                <small>Portland Cement 50kg — qty 50 — 2025-10-14</small>
                                            </div>
                                            <div class="ms-3">
                                                <button type="button" class="btn btn-sm btn-primary accept-btn" data-ref="PO-1234">Accept</button>
                                            </div>
                                        </li>
                                        <li class="mb-3 d-flex align-items-start justify-content-between" data-ref="PO-1210">
                                            <div>
                                                <strong>PO-1210</strong><br>
                                                <small>Vinyl Flooring Plank — qty 200 — 2025-10-11</small>
                                            </div>
                                            <div class="ms-3">
                                                <button type="button" class="btn btn-sm btn-primary accept-btn" data-ref="PO-1210">Accept</button>
                                            </div>
                                        </li>
                                        <li class="mb-3 d-flex align-items-start justify-content-between" data-ref="PO-1205">
                                            <div>
                                                <strong>PO-1205</strong><br>
                                                <small>PVC Pipe 2in — qty 200 — 2025-10-09</small>
                                            </div>
                                            <div class="ms-3">
                                                <button type="button" class="btn btn-sm btn-primary accept-btn" data-ref="PO-1205">Accept</button>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="card card-move">
                            <div class="card-body">
                                <h5 class="card-title">Outbound Shipments</h5>
                                <div class="outbound-list mt-3">
                                    <ul class="list-unstyled mb-0">
                                        <li class="mb-3 d-flex align-items-start justify-content-between" data-ref="SO-5678">
                                            <div>
                                                <strong>SO-5678</strong><br>
                                                <small>Hex Bolts M10 — qty 100 — 2025-10-13</small>
                                            </div>
                                            <div class="ms-3">
                                                <button type="button" class="btn btn-sm btn-outline-success approve-btn" data-ref="SO-5678">Approve</button>
                                            </div>
                                        </li>
                                        <li class="mb-3 d-flex align-items-start justify-content-between" data-ref="SO-5599">
                                            <div>
                                                <strong>SO-5599</strong><br>
                                                <small>Copper Wire Roll — qty 40 — 2025-10-10</small>
                                            </div>
                                            <div class="ms-3">
                                                <button type="button" class="btn btn-sm btn-outline-success approve-btn" data-ref="SO-5599">Approve</button>
                                            </div>
                                        </li>
                                        <li class="mb-3 d-flex align-items-start justify-content-between" data-ref="SO-5588">
                                            <div>
                                                <strong>SO-5588</strong><br>
                                                <small>Timber Plank 2x4 — qty 60 — 2025-10-08</small>
                                            </div>
                                            <div class="ms-3">
                                                <button type="button" class="btn btn-sm btn-outline-success approve-btn" data-ref="SO-5588">Approve</button>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div> <!-- end right column -->
                </div> <!-- end row -->
            </div> <!-- end container -->
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        (function(){
            // Handle accept button clicks (inbound)
            document.querySelectorAll('.accept-btn').forEach(function(btn){
                btn.addEventListener('click', function(e){
                    var ref = btn.getAttribute('data-ref') || '';
                    btn.disabled = true;
                    btn.classList.remove('btn-primary');
                    btn.classList.add('btn-success');
                    btn.textContent = 'Accepted';
                    var li = btn.closest('li');
                    if(li) li.classList.add('accepted-item');

                    var msg = document.createElement('div');
                    msg.className = 'position-fixed bottom-0 end-0 m-3 alert alert-success py-1 px-2';
                    msg.style.zIndex = 9999;
                    msg.style.opacity = '0.95';
                    msg.textContent = 'Accepted ' + ref;
                    document.body.appendChild(msg);
                    setTimeout(function(){ msg.remove(); }, 1800);
                });
            });

            // Handle approve button clicks (outbound)
            document.querySelectorAll('.approve-btn').forEach(function(btn){
                btn.addEventListener('click', function(e){
                    var ref = btn.getAttribute('data-ref') || '';
                    btn.disabled = true;
                    btn.classList.remove('btn-outline-success');
                    btn.classList.add('btn-success');
                    btn.textContent = 'Approved';
                    var li = btn.closest('li');
                    if(li) li.classList.add('approved-item');

                    var msg = document.createElement('div');
                    msg.className = 'position-fixed bottom-0 end-0 m-3 alert alert-success py-1 px-2';
                    msg.style.zIndex = 9999;
                    msg.style.opacity = '0.95';
                    msg.textContent = 'Approved ' + ref;
                    document.body.appendChild(msg);
                    setTimeout(function(){ msg.remove(); }, 1800);
                });
            });
        })();
    </script>
</body>
</html>