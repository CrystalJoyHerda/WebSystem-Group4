<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Vendor Management - Accounts Payable</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { 
            font-family: 'Times New Roman', serif; 
            background: #f8f9fa; 
        }
        .app-shell { 
            display: flex; 
            min-height: 100vh; 
        }
        .main { 
            flex: 1; 
            padding: 24px 32px; 
            margin-left: 220px; 
        }
        .header { 
            display: flex; 
            align-items: center; 
            justify-content: space-between; 
            margin-bottom: 24px; 
            padding-bottom: 16px;
            border-bottom: 2px solid #e0e0e0;
        }
        .brand { 
            font-family: 'Georgia', serif; 
            font-size: 32px; 
            font-weight: bold;
            color: #2c3e50;
        }
        .page-title { 
            text-align: center; 
            font-size: 36px; 
            margin: 20px 0 30px 0;
            color: #34495e;
            font-weight: bold;
            letter-spacing: 1px;
        }
        .vendor-card {
            border-radius: 10px;
            border: 1px solid #e0e0e0;
            padding: 20px;
            margin-bottom: 15px;
            background: white;
            transition: all 0.3s ease;
            height: 100%;
        }
        .vendor-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.1);
        }
        .vendor-avatar {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
            font-weight: bold;
            margin: 0 auto 15px auto;
        }
        .vendor-status {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            display: inline-block;
            margin-bottom: 10px;
        }
        .status-active { background-color: #27ae60; color: white; }
        .status-inactive { background-color: #7f8c8d; color: white; }
        .status-pending { background-color: #f39c12; color: white; }
        .vendor-metrics {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 8px;
            margin-top: 15px;
        }
        .metric-item {
            text-align: center;
            padding: 8px;
        }
        .metric-value {
            font-size: 18px;
            font-weight: bold;
            color: #2c3e50;
        }
        .metric-label {
            font-size: 12px;
            color: #7f8c8d;
        }
        .action-buttons {
            display: flex;
            gap: 8px;
            margin-top: 15px;
            justify-content: center;
        }
        @media (max-width: 900px) { 
            .main { margin-left: 0; padding: 16px; } 
        }
    </style>
</head>
<body>
    <div class="app-shell">
        <?= view('partials/sidebar') ?>

        <main class="main">
            <div class="header">
                <div class="brand">WeBuild</div>
                <div>
                    <a href="<?= site_url('dashboard/accounts_payable/dashboard') ?>" class="btn btn-outline-secondary me-2">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addVendorModal">
                        <i class="fas fa-plus"></i> Add Vendor
                    </button>
                </div>
            </div>

            <div class="page-title">VENDOR MANAGEMENT</div>

            <!-- Search and Filter -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                                <input type="text" id="searchVendors" class="form-control" placeholder="Search vendors...">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <select id="filterStatus" class="form-select">
                                <option value="">All Status</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="pending">Pending</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select id="filterCategory" class="form-select">
                                <option value="">All Categories</option>
                                <!-- Populated by JavaScript -->
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button id="applyVendorFilters" class="btn btn-primary w-100">
                                <i class="fas fa-filter"></i> Filter
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Vendors Grid -->
            <div id="vendorsList">
                <div class="text-center py-5">
                    <div class="spinner-border spinner-border-lg" role="status"></div>
                    <p class="mt-3">Loading vendors...</p>
                </div>
            </div>
        </main>
    </div>

    <!-- Add Vendor Modal -->
    <div class="modal fade" id="addVendorModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Vendor</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="addVendorForm">
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Vendor Name *</label>
                                <input type="text" name="name" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Vendor Code *</label>
                                <input type="text" name="vendor_code" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Contact Person</label>
                                <input type="text" name="contact_person" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email *</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Phone</label>
                                <input type="text" name="phone" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Category</label>
                                <select name="category" class="form-select">
                                    <option value="supplier">Supplier</option>
                                    <option value="service">Service Provider</option>
                                    <option value="contractor">Contractor</option>
                                    <option value="consultant">Consultant</option>
                                </select>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Address</label>
                                <textarea name="address" class="form-control" rows="2"></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Payment Terms</label>
                                <select name="payment_terms" class="form-select">
                                    <option value="net30">Net 30</option>
                                    <option value="net60">Net 60</option>
                                    <option value="net15">Net 15</option>
                                    <option value="due_on_receipt">Due on Receipt</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Tax ID</label>
                                <input type="text" name="tax_id" class="form-control">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Notes</label>
                                <textarea name="notes" class="form-control" rows="3"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Vendor</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Vendor Detail Modal -->
    <div class="modal fade" id="vendorDetailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Vendor Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="vendorDetailContent">
                    <!-- Content loaded by JavaScript -->
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let vendors = [];
        const vendorModal = new bootstrap.Modal(document.getElementById('vendorDetailModal'));
        const addVendorModal = new bootstrap.Modal(document.getElementById('addVendorModal'));

        document.addEventListener('DOMContentLoaded', function() {
            loadVendors();
            loadVendorCategories();
            
            // Event listeners
            document.getElementById('addVendorForm').addEventListener('submit', saveVendor);
            document.getElementById('applyVendorFilters').addEventListener('click', applyVendorFilters);
            document.getElementById('searchVendors').addEventListener('keyup', debounce(searchVendors, 300));
        });

        async function loadVendors() {
            try {
                const response = await fetch('<?= site_url('api/accounts-payable/vendors/list') ?>');
                if (response.ok) {
                    vendors = await response.json();
                    displayVendors(vendors);
                }
            } catch (error) {
                console.error('Error loading vendors:', error);
                document.getElementById('vendorsList').innerHTML = `
                    <div class="alert alert-danger">
                        Failed to load vendors. Please try again.
                    </div>
                `;
            }
        }

        function displayVendors(vendorsToDisplay) {
            const container = document.getElementById('vendorsList');
            
            if (!vendorsToDisplay || vendorsToDisplay.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-5">
                        <i class="fas fa-building fa-3x text-muted mb-3"></i>
                        <h5>No vendors found</h5>
                        <p class="text-muted">Try adjusting your filters or add a new vendor.</p>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addVendorModal">
                            <i class="fas fa-plus"></i> Add Vendor
                        </button>
                    </div>
                `;
                return;
            }

            const vendorsHtml = vendorsToDisplay.map(vendor => {
                const statusClass = vendor.status === 'active' ? 'status-active' : 
                                  vendor.status === 'inactive' ? 'status-inactive' : 'status-pending';
                const firstLetter = vendor.name.charAt(0).toUpperCase();
                
                return `
                    <div class="col-md-4 mb-4">
                        <div class="vendor-card">
                            <div class="vendor-avatar">${firstLetter}</div>
                            <div class="text-center">
                                <span class="vendor-status ${statusClass}">${vendor.status.toUpperCase()}</span>
                                <h5 class="mb-1">${vendor.name}</h5>
                                <p class="text-muted small mb-2">${vendor.vendor_code}</p>
                                <p class="small mb-2">
                                    <i class="fas fa-user me-1"></i> ${vendor.contact_person || 'N/A'}
                                </p>
                                <p class="small mb-2">
                                    <i class="fas fa-envelope me-1"></i> ${vendor.email}
                                </p>
                            </div>
                            
                            <div class="vendor-metrics">
                                <div class="row text-center">
                                    <div class="col-4 metric-item">
                                        <div class="metric-value">${vendor.pending_invoices || 0}</div>
                                        <div class="metric-label">Pending</div>
                                    </div>
                                    <div class="col-4 metric-item">
                                        <div class="metric-value">${vendor.overdue_invoices || 0}</div>
                                        <div class="metric-label">Overdue</div>
                                    </div>
                                    <div class="col-4 metric-item">
                                        <div class="metric-value">$${parseFloat(vendor.total_pending || 0).toFixed(0)}</div>
                                        <div class="metric-label">Total Due</div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="action-buttons">
                                <button class="btn btn-sm btn-outline-info" onclick="viewVendorDetail(${vendor.id})">
                                    <i class="fas fa-eye"></i> View
                                </button>
                                <button class="btn btn-sm btn-outline-warning" onclick="editVendor(${vendor.id})">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <button class="btn btn-sm btn-outline-primary" onclick="createInvoiceForVendor(${vendor.id})">
                                    <i class="fas fa-file-invoice"></i> Invoice
                                </button>
                            </div>
                        </div>
                    </div>
                `;
            }).join('');

            container.innerHTML = `<div class="row">${vendorsHtml}</div>`;
        }

        async function loadVendorCategories() {
            try {
                const response = await fetch('<?= site_url('api/accounts-payable/vendors/categories') ?>');
                if (response.ok) {
                    const categories = await response.json();
                    const select = document.getElementById('filterCategory');
                    
                    categories.forEach(category => {
                        const option = document.createElement('option');
                        option.value = category;
                        option.textContent = category.charAt(0).toUpperCase() + category.slice(1);
                        select.appendChild(option);
                    });
                }
            } catch (error) {
                console.error('Error loading categories:', error);
            }
        }

        async function saveVendor(event) {
            event.preventDefault();
            
            const formData = new FormData(event.target);
            const vendorData = Object.fromEntries(formData.entries());
            
            try {
                const response = await fetch('<?= site_url('api/accounts-payable/vendors') ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(vendorData)
                });
                
                if (response.ok) {
                    alert('Vendor saved successfully!');
                    addVendorModal.hide();
                    event.target.reset();
                    loadVendors();
                } else {
                    throw new Error('Failed to save vendor');
                }
            } catch (error) {
                console.error('Error saving vendor:', error);
                alert('Failed to save vendor');
            }
        }

        async function viewVendorDetail(vendorId) {
            try {
                const response = await fetch(`<?= site_url('api/accounts-payable/vendors/') ?>${vendorId}`);
                if (response.ok) {
                    const vendor = await response.json();
                    displayVendorDetail(vendor);
                    vendorModal.show();
                }
            } catch (error) {
                console.error('Error loading vendor details:', error);
                alert('Failed to load vendor details');
            }
        }

        function displayVendorDetail(vendor) {
            const container = document.getElementById('vendorDetailContent');
            
            container.innerHTML = `
                <div class="row">
                    <div class="col-md-8">
                        <h4>${vendor.name}</h4>
                        <p class="text-muted">${vendor.vendor_code}</p>
                    </div>
                    <div class="col-md-4 text-end">
                        <span class="badge ${vendor.status === 'active' ? 'bg-success' : 'bg-secondary'} fs-6">
                            ${vendor.status.toUpperCase()}
                        </span>
                    </div>
                </div>
                
                <div class="row mt-4">
                    <div class="col-md-6">
                        <h6>Contact Information</h6>
                        <table class="table table-sm">
                            <tr><td><strong>Contact Person:</strong></td><td>${vendor.contact_person || 'N/A'}</td></tr>
                            <tr><td><strong>Email:</strong></td><td>${vendor.email}</td></tr>
                            <tr><td><strong>Phone:</strong></td><td>${vendor.phone || 'N/A'}</td></tr>
                            <tr><td><strong>Address:</strong></td><td>${vendor.address || 'N/A'}</td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6>Business Information</h6>
                        <table class="table table-sm">
                            <tr><td><strong>Category:</strong></td><td>${vendor.category || 'N/A'}</td></tr>
                            <tr><td><strong>Payment Terms:</strong></td><td>${vendor.payment_terms || 'Net 30'}</td></tr>
                            <tr><td><strong>Tax ID:</strong></td><td>${vendor.tax_id || 'N/A'}</td></tr>
                            <tr><td><strong>Created:</strong></td><td>${formatDate(vendor.created_at)}</td></tr>
                        </table>
                    </div>
                </div>
                
                <div class="row mt-4">
                    <div class="col-md-12">
                        <h6>Financial Summary</h6>
                        <div class="row text-center">
                            <div class="col-3">
                                <div class="p-3 border rounded">
                                    <div class="h4 text-primary">$${parseFloat(vendor.total_pending || 0).toFixed(2)}</div>
                                    <small class="text-muted">Total Due</small>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="p-3 border rounded">
                                    <div class="h4 text-warning">${vendor.pending_invoices || 0}</div>
                                    <small class="text-muted">Pending Invoices</small>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="p-3 border rounded">
                                    <div class="h4 text-danger">${vendor.overdue_invoices || 0}</div>
                                    <small class="text-muted">Overdue Invoices</small>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="p-3 border rounded">
                                    <div class="h4 text-success">${vendor.paid_invoices || 0}</div>
                                    <small class="text-muted">Paid Invoices</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                ${vendor.notes ? `
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <h6>Notes</h6>
                            <div class="p-3 bg-light rounded">
                                ${vendor.notes}
                            </div>
                        </div>
                    </div>
                ` : ''}
                
                <div class="row mt-4">
                    <div class="col-md-12 text-end">
                        <button class="btn btn-warning" onclick="editVendor(${vendor.id})">
                            <i class="fas fa-edit"></i> Edit Vendor
                        </button>
                        <button class="btn btn-primary" onclick="createInvoiceForVendor(${vendor.id})">
                            <i class="fas fa-file-invoice-dollar"></i> Create Invoice
                        </button>
                    </div>
                </div>
            `;
        }

        function editVendor(vendorId) {
            window.location.href = `<?= site_url('dashboard/accounts_payable/vendors/edit/') ?>${vendorId}`;
        }

        function createInvoiceForVendor(vendorId) {
            window.location.href = `<?= site_url('dashboard/accounts_payable/invoices/create/') ?>${vendorId}`;
        }

        function applyVendorFilters() {
            const status = document.getElementById('filterStatus').value;
            const category = document.getElementById('filterCategory').value;
            
            let filteredVendors = vendors;
            
            if (status) {
                filteredVendors = filteredVendors.filter(v => v.status === status);
            }
            
            if (category) {
                filteredVendors = filteredVendors.filter(v => v.category === category);
            }
            
            displayVendors(filteredVendors);
        }

        function searchVendors() {
            const searchTerm = document.getElementById('searchVendors').value.toLowerCase().trim();
            
            if (!searchTerm) {
                displayVendors(vendors);
                return;
            }
            
            const filteredVendors = vendors.filter(vendor => 
                vendor.name.toLowerCase().includes(searchTerm) ||
                vendor.vendor_code.toLowerCase().includes(searchTerm) ||
                vendor.email.toLowerCase().includes(searchTerm) ||
                (vendor.contact_person && vendor.contact_person.toLowerCase().includes(searchTerm))
            );
            
            displayVendors(filteredVendors);
        }

        function formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            });
        }

        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }
    </script>
</body>
</html>