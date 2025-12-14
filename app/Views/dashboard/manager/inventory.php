<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Inventory</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body{font-family:'Times New Roman',serif;background:#fff}
        .app-shell{display:flex;min-height:100vh}
        /* make sidebar fixed so it doesn't move when the page scrolls */
        .sidebar{
            width:220px;
            background:#ebeaea;
            padding:18px;
            border-right:1px solid #6b5b5b;

            position:fixed;
            top:0;
            left:0;
            height:100vh;
            overflow:auto; /* keep internal scroll if sidebar content grows */
            z-index: 10;
        }

        /* push main content to the right of the fixed sidebar */
        main{
            margin-left:220px;
            flex:1;
            padding:20px 36px;
        }

        .profile-box{background:#e9e6e6;padding:14px;border:1px solid #6b5b5b;margin-bottom:18px;text-align:center}
        .brand{font-family:'Georgia',serif;font-size:36px;padding:10px 0}
        .page-title{text-align:center;font-size:40px;letter-spacing:2px;margin-bottom:18px}
        .controls{display:flex;gap:12px;justify-content:center;margin-bottom:12px}
        .table thead th{border-bottom:2px solid #ddd}
        .status-badge{padding:6px 10px;border-radius:6px;color:#fff;font-weight:600}
        .status-low{background:#ff7a4a}
        .status-out{background:#c94b3f}
        .status-in{background:#27ae60}
        .add-btn{background:#1e0b0b;color:#fff;border-radius:18px;padding:10px 16px}
    </style>
    <link href="<?= base_url('css/site.css') ?>" rel="stylesheet">
    <link href="<?= base_url('css/inventory.css') ?>" rel="stylesheet">
</head>
<body>
    <div class="app-shell">
        <?= view('partials/sidebar') ?>

        <main style="flex:1;padding:20px 36px">
            <?php if (session()->getFlashdata('success')): ?>
                <div data-flash-message="<?= esc(session()->getFlashdata('success')) ?>" data-flash-type="success"></div>
            <?php endif; ?>
            <?php if (session()->getFlashdata('error')): ?>
                <div data-flash-message="<?= esc(session()->getFlashdata('error')) ?>" data-flash-type="danger"></div>
            <?php endif; ?>
            <div class="brand">WeBuild</div>
            <div class="page-title">INVENTORY</div>

            <div class="controls">
                <select class="form-select" name="category" style="width:260px">
                    <option>All Category</option>
                    <option>Building Materials</option>
                    <option>Lumber &amp; Wood Products</option>
                    <option>Steel &amp; Metal Products</option>
                    <option>Electrical Supplies</option>
                    <option>Plumbing Supplies</option>
                    <option>Paints &amp; Finishes</option>
                    <option>Tools &amp; Equipment</option>
                    <option>Safety Gear</option>
                    <option>Fasteners &amp; Hardware</option>
                    <option>Roofing Materials</option>
                    <option>Flooring Materials</option>
                    <option>Doors &amp; Windows</option>
                    <option>Insulation Materials</option>
                </select>
                <select class="form-select" name="location" style="width:160px">
                    <option value="">All Location</option>
                    <option>A</option>
                    <option>B</option>
                    <option>C</option>
                    <option>D</option>
                    <option>E</option>
                    <option>F</option>
                    <option>G</option>
                </select>
                <div style="flex:1;max-width:420px">
                    <input class="form-control" placeholder="" />
                </div>
                <button class="btn btn-light" style="border-radius:18px">🔍</button>
            </div>

            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>SKU</th>
                            <th>Category</th>
                            <th>Warehouse</th>
                            <th>Location</th>
                            <th>Quantity</th>
                            <th>Status</th>
                            <th>Expiry</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (! empty($items) && is_array($items)): ?>
                            <?php foreach ($items as $it): ?>
                                <tr>
                                    <td><?= esc($it['name']) ?></td>
                                    <td><?= esc($it['sku']) ?></td>
                                    <td><?= esc($it['category']) ?></td>
                                    <td><?= esc($it['warehouse_name'] ?? 'Not assigned') ?></td>
                                    <td><?= esc($it['location']) ?></td>
                                    <td><?= esc($it['quantity']) ?></td>
                                    <td>
                                        <?php $s = $it['status'] ?? 'in'; ?>
                                        <span class="status-badge <?= $s === 'low' ? 'status-low' : ($s === 'out' ? 'status-out' : 'status-in') ?>">
                                            <?= ucfirst($s === 'low' ? 'Low Stock' : ($s === 'out' ? 'Out of Stock' : 'In Stock')) ?>
                                        </span>
                                    </td>
                                    <td><?= esc($it['expiry']) ?></td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-light btn-edit" 
                                            data-id="<?= esc($it['id']) ?>"
                                            data-name="<?= esc($it['name']) ?>"
                                            data-sku="<?= esc($it['sku']) ?>"
                                            data-category="<?= esc($it['category']) ?>"
                                            data-location="<?= esc($it['location']) ?>"
                                            data-warehouse="<?= esc($it['warehouse_id'] ?? '') ?>"
                                            data-quantity="<?= esc($it['quantity']) ?>"
                                            data-status="<?= esc($it['status']) ?>"
                                            data-expiry="<?= esc($it['expiry']) ?>"
                                        >✏️</button>
                                        <form method="post" action="<?= site_url('inventory/delete/' . $it['id']) ?>" class="d-inline delete-form">
                                            <?= csrf_field() ?>
                                            <button type="button" class="btn btn-sm btn-light btn-delete" data-id="<?= esc($it['id']) ?>" data-name="<?= esc($it['name']) ?>">🗑️</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="text-center text-muted">No items found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div style="text-align:right;margin-top:18px">
                <button class="add-btn" data-bs-toggle="modal" data-bs-target="#addItemModal">+ Add item</button>
            </div>

            <!-- Add Item Modal -->
            <div class="modal fade" id="addItemModal" tabindex="-1" aria-labelledby="addItemModalLabel" aria-hidden="true">
              <div class="modal-dialog modal-lg">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title" id="addItemModalLabel">Add Inventory Item</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <form method="post" action="<?= site_url('inventory/create') ?>">
                      <?= csrf_field() ?>
                  <div class="modal-body">
                      <div class="row g-3">
                          <div class="col-md-8">
                              <label class="form-label">Item name</label>
                              <input name="name" class="form-control" required />
                          </div>
                          <div class="col-md-4">
                              <label class="form-label">SKU</label>
                              <input name="sku" class="form-control" />
                          </div>

                          <div class="col-md-4">
                              <label class="form-label">Category</label>
                              <select name="category" class="form-select">
                                  <option>Building Materials</option>
                                  <option>Lumber &amp; Wood Products</option>
                                  <option>Steel &amp; Metal Products</option>
                                  <option>Electrical Supplies</option>
                                  <option>Plumbing Supplies</option>
                                  <option>Paints &amp; Finishes</option>
                                  <option>Tools &amp; Equipment</option>
                                  <option>Safety Gear</option>
                                  <option>Fasteners &amp; Hardware</option>
                                  <option>Roofing Materials</option>
                                  <option>Flooring Materials</option>
                                  <option>Doors &amp; Windows</option>
                                  <option>Insulation Materials</option>
                              </select>
                          </div>
                          <div class="col-md-4">
                              <label class="form-label">Warehouse</label>
                              <select name="warehouse_id" class="form-select" id="add-warehouse" required>
                                  <option value="">Select Warehouse</option>
                              </select>
                          </div>
                          <div class="col-md-4">
                              <label class="form-label">Location</label>
                              <select name="location" class="form-select">
                                  <option value="">Location</option>
                                  <option>A</option>
                                  <option>B</option>
                                  <option>C</option>
                                  <option>D</option>
                                  <option>E</option>
                                  <option>F</option>
                                  <option>G</option>
                              </select>
                          </div>
                          <div class="col-md-4">
                              <label class="form-label">Quantity</label>
                              <input name="quantity" type="number" min="0" value="0" class="form-control" />
                          </div>

                          <div class="col-md-6">
                              <label class="form-label">Status</label>
                              <select name="status" class="form-select">
                                  <option value="in">In Stock</option>
                                  <option value="low">Low Stock</option>
                                  <option value="out">Out of Stock</option>
                              </select>
                          </div>
                          <div class="col-md-6">
                              <label class="form-label">Expiry (optional)</label>
                              <input name="expiry" type="date" class="form-control" />
                          </div>
                      </div>
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add item</button>
                  </div>
                  </form>
                </div>
              </div>
            </div>
                        <!-- Delete Confirm Modal -->
                        <div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="deleteConfirmLabel">Confirm delete</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>

                        
                                    <div class="modal-body">
                                        <p>Are you sure you want to delete <strong id="delete-item-name">this item</strong>?</p>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="button" class="btn btn-danger" id="confirm-delete">Delete</button>
                                    </div>
                                </div>
                            </div>
                        </div>

            <!-- Edit Item Modal -->
            <div class="modal fade" id="editItemModal" tabindex="-1" aria-labelledby="editItemModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                    <h5 class="modal-title" id="editItemModalLabel">Edit Inventory Item</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form method="post" id="edit-item-form" action="<?= site_url('inventory/update/0') ?>">
                        <?= csrf_field() ?>
                    <div class="modal-body">
                        <input type="hidden" name="id" id="edit-id" />
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label">Item name</label>
                                <input name="name" id="edit-name" class="form-control" required />
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">SKU</label>
                                <input name="sku" id="edit-sku" class="form-control" />
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Category</label>
                                <select name="category" id="edit-category" class="form-select">
                                    <option>Building Materials</option>
                                    <option>Lumber &amp; Wood Products</option>
                                    <option>Steel &amp; Metal Products</option>
                                    <option>Electrical Supplies</option>
                                    <option>Plumbing Supplies</option>
                                    <option>Paints &amp; Finishes</option>
                                    <option>Tools &amp; Equipment</option>
                                    <option>Safety Gear</option>
                                    <option>Fasteners &amp; Hardware</option>
                                    <option>Roofing Materials</option>
                                    <option>Flooring Materials</option>
                                    <option>Doors &amp; Windows</option>
                                    <option>Insulation Materials</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Warehouse</label>
                                <select name="warehouse_id" id="edit-warehouse" class="form-select" required>
                                    <option value="">Select Warehouse</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Location</label>
                                <select name="location" id="edit-location" class="form-select">
                                    <option value="">Location</option>
                                    <option>A</option>
                                    <option>B</option>
                                    <option>C</option>
                                    <option>D</option>
                                    <option>E</option>
                                    <option>F</option>
                                    <option>G</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Quantity</label>
                                <input name="quantity" id="edit-quantity" type="number" min="0" value="0" class="form-control" />
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Status</label>
                                <select name="status" id="edit-status" class="form-select">
                                    <option value="in">In Stock</option>
                                    <option value="low">Low Stock</option>
                                    <option value="out">Out of Stock</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Expiry (optional)</label>
                                <input name="expiry" id="edit-expiry" type="date" class="form-control" />
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save changes</button>
                    </div>
                    </form>
                </div>
                </div>
            </div>
                        <!-- Flash container (top-right) -->
                        <div id="flash-container" style="position:fixed;top:18px;right:18px;z-index:1080"></div>
        </main>
    </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Pass site URL to JavaScript
        window.siteUrl = '<?= site_url() ?>';
    </script>
    <script src="<?= base_url('js/inventory.js') ?>"></script>
</body>
</html>
