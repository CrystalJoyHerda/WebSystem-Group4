<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invoice Management - Accounts Payable</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= base_url('public/assets/theme.css') ?>">
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
        .filter-section {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .invoice-card {
            border-radius: 8px;
            border: 1px solid #e0e0e0;
            padding: 16px;
            margin-bottom: 12px;
            background: white;
            transition: all 0.3s ease;
        }
        .invoice-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .status-badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }
        .status-pending { background-color: #f39c12; color: white; }
        .status-approved { background-color: #27ae60; color: white; }
        .status-overdue { background-color: #e74c3c; color: white; }
        .status-paid { background-color: #3498db; color: white; }
        .action-buttons {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        .btn-action {
            padding: 4px 12px;
            font-size: 14px;
            border-radius: 6px;
        }
        @media (max-width: 900px) { 
            .main { margin-left: 0; padding: 16px; } 
            .action-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body class="bg-light">
    <div class="app-shell">
        <?= view('partials/sidebar') ?>

        <main class="main">
            <div class="header">
                <div class="brand">WeBuild</div>
                <div>
                    <a href="<?= site_url('dashboard/accounts_payable/dashboard') ?>" class="btn btn-outline-secondary me-2">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                    <a href="<?= site_url('dashboard/accounts_payable/invoices/create') ?>" class="btn btn-primary">
                        <i class="fas fa-plus"></i> New Invoice
                    </a>
                </div>
            </div>

            <div class="page-title">INVOICE MANAGEMENT</div>

            <!-- Filter Section -->
            <div class="filter-section">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select id="filterStatus" class="form-select">
                            <option value="">All Status</option>
                            <option value="pending">Pending</option>
                            <option value="approved">Approved</option>
                            <option value="overdue">Overdue</option>
                            <option value="paid">Paid</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Vendor</label>
                        <select id="filterVendor" class="form-select">
                            <option value="">All Vendors</option>
                            <!-- Populated by JavaScript -->
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Date From</label>
                        <input type="date" id="filterDateFrom" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Date To</label>
                        <input type="date" id="filterDateTo" class="form-control">
                    </div>
                    <div class="col-md-12 mt-3">
                        <button id="applyFilters" class="btn btn-primary">
                            <i class="fas fa-filter"></i> Apply Filters
                        </button>
                        <button id="resetFilters" class="btn btn-outline-secondary">
                            <i class="fas fa-redo"></i> Reset
                        </button>
                        <button id="exportInvoices" class="btn btn-success float-end">
                            <i class="fas fa-file-export"></i> Export to Excel
                        </button>
                    </div>
                </div>
            </div>

            <!-- Invoices List -->
            <div class="card">
                <div class="card-header bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-file-invoice"></i> Invoices
                            <span id="totalInvoices" class="badge bg-secondary ms-2">0</span>
                        </h5>
                        <div class="d-flex gap-2">
                            <div class="input-group input-group-sm" style="width: 300px;">
                                <input type="text" id="searchInvoices" class="form-control" placeholder="Search invoices...">
                                <button class="btn btn-outline-secondary" type="button">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                            <button id="refreshInvoices" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-sync-alt"></i> Refresh
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div id="invoicesList">
                        <div class="text-center py-5">
                            <div class="spinner-border spinner-border-lg" role="status"></div>
                            <p class="mt-3">Loading invoices...</p>
                        </div>
                    </div>
                    <div id="pagination" class="mt-4 text-center"></div>
                </div>
            </div>
        </main>
    </div>

    <!-- Invoice Detail Modal -->
    <div class="modal fade" id="invoiceDetailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Invoice Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="invoiceDetailContent">
                    <!-- Content loaded by JavaScript -->
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let currentPage = 1;
        let totalPages = 1;
        const itemsPerPage = 10;
        let currentFilters = {};

        const invoiceModal = new bootstrap.Modal(document.getElementById('invoiceDetailModal'));

        document.addEventListener('DOMContentLoaded', function() {
            loadVendors();
            loadInvoices();
            
            // Event listeners
            document.getElementById('applyFilters').addEventListener('click', applyFilters);
            document.getElementById('resetFilters').addEventListener('click', resetFilters);
            document.getElementById('refreshInvoices').addEventListener('click', loadInvoices);
            document.getElementById('searchInvoices').addEventListener('keyup', debounce(searchInvoices, 300));
            document.getElementById('exportInvoices').addEventListener('click', exportInvoices);
        });

        async function loadVendors() {
            try {
                const response = await fetch('<?= site_url('api/accounts-payable/vendors') ?>');
                if (response.ok) {
                    const vendors = await response.json();
                    const select = document.getElementById('filterVendor');
                    
                    vendors.forEach(vendor => {
                        const option = document.createElement('option');
                        option.value = vendor.id;
                        option.textContent = vendor.name;
                        select.appendChild(option);
                    });
                }
            } catch (error) {
                console.error('Error loading vendors:', error);
            }
        }

        async function loadInvoices(page = 1) {
            try {
                const params = new URLSearchParams({
                    page: page,
                    limit: itemsPerPage,
                    ...currentFilters
                });

                const response = await fetch(`<?= site_url('api/accounts-payable/invoices') ?>?${params}`);
                if (response.ok) {
                    const data = await response.json();
                    displayInvoices(data.invoices);
                    setupPagination(data.total, page);
                    document.getElementById('totalInvoices').textContent = data.total;
                }
            } catch (error) {
                console.error('Error loading invoices:', error);
                document.getElementById('invoicesList').innerHTML = `
                    <div class="alert alert-danger">
                        Failed to load invoices. Please try again.
                    </div>
                `;
            }
        }

        function displayInvoices(invoices) {
            const container = document.getElementById('invoicesList');
            
            if (!invoices || invoices.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-5">
                        <i class="fas fa-file-invoice fa-3x text-muted mb-3"></i>
                        <h5>No invoices found</h5>
                        <p class="text-muted">Try adjusting your filters or create a new invoice.</p>
                        <a href="<?= site_url('dashboard/accounts_payable/invoices/create') ?>" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Create New Invoice
                        </a>
                    </div>
                `;
                return;
            }

            const invoicesHtml = invoices.map(invoice => {
                const dueDate = new Date(invoice.due_date);
                const today = new Date();
                const daysUntilDue = Math.ceil((dueDate - today) / (1000 * 60 * 60 * 24));
                
                let statusClass = '';
                let statusText = '';
                
                switch(invoice.status) {
                    case 'pending':
                        statusClass = 'status-pending';
                        statusText = 'PENDING';
                        break;
                    case 'approved':
                        statusClass = 'status-approved';
                        statusText = 'APPROVED';
                        break;
                    case 'overdue':
                        statusClass = 'status-overdue';
                        statusText = 'OVERDUE';
                        break;
                    case 'paid':
                        statusClass = 'status-paid';
                        statusText = 'PAID';
                        break;
                    default:
                        statusClass = 'status-pending';
                        statusText = 'UNKNOWN';
                }

                return `
                    <div class="invoice-card" onclick="viewInvoiceDetail(${invoice.id})" style="cursor: pointer;">
                        <div class="d-flex justify-content-between align-items-center">
                            <div style="flex: 2;">
                                <div class="d-flex align-items-center">
                                    <span class="badge ${statusClass} me-2">${statusText}</span>
                                    <strong>${invoice.invoice_number}</strong>
                                </div>
                                <div class="mt-1">
                                    <small class="text-muted">Vendor: ${invoice.vendor_name}</small>
                                    <br>
                                    <small class="text-muted">Invoice Date: ${formatDate(invoice.invoice_date)}</small>
                                </div>
                            </div>
                            <div style="flex: 1;" class="text-center">
                                <div class="h5 mb-0">$${parseFloat(invoice.amount).toFixed(2)}</div>
                                <small class="text-muted">${invoice.currency || 'USD'}</small>
                            </div>
                            <div style="flex: 1;">
                                <div class="text-end">
                                    <small class="text-muted">Due Date</small>
                                    <br>
                                    <strong class="${daysUntilDue < 0 ? 'text-danger' : daysUntilDue < 3 ? 'text-warning' : 'text-success'}">
                                        ${formatDate(invoice.due_date)}
                                    </strong>
                                    <br>
                                    <small class="${daysUntilDue < 0 ? 'text-danger' : ''}">
                                        ${daysUntilDue < 0 ? `${Math.abs(daysUntilDue)} days overdue` : 
                                         daysUntilDue === 0 ? 'Due today' : 
                                         `${daysUntilDue} days remaining`}
                                    </small>
                                </div>
                            </div>
                            <div style="flex: 1;" class="action-buttons">
                                <button class="btn btn-sm btn-outline-primary btn-action" onclick="event.stopPropagation(); approveInvoice(${invoice.id})">
                                    <i class="fas fa-check"></i> Approve
                                </button>
                                <button class="btn btn-sm btn-outline-success btn-action" onclick="event.stopPropagation(); processPayment(${invoice.id})">
                                    <i class="fas fa-money-check-alt"></i> Pay
                                </button>
                                <button class="btn btn-sm btn-outline-info btn-action" onclick="event.stopPropagation(); viewInvoiceDetail(${invoice.id})">
                                    <i class="fas fa-eye"></i> View
                                </button>
                            </div>
                        </div>
                    </div>
                `;
            }).join('');

            container.innerHTML = invoicesHtml;
        }

        function setupPagination(totalItems, currentPage) {
            const container = document.getElementById('pagination');
            totalPages = Math.ceil(totalItems / itemsPerPage);
            
            if (totalPages <= 1) {
                container.innerHTML = '';
                return;
            }

            let paginationHtml = `
                <nav aria-label="Invoice pagination">
                    <ul class="pagination justify-content-center">
                        <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                            <a class="page-link" href="#" onclick="changePage(${currentPage - 1})">Previous</a>
                        </li>
            `;

            for (let i = 1; i <= totalPages; i++) {
                if (i === 1 || i === totalPages || (i >= currentPage - 2 && i <= currentPage + 2)) {
                    paginationHtml += `
                        <li class="page-item ${i === currentPage ? 'active' : ''}">
                            <a class="page-link" href="#" onclick="changePage(${i})">${i}</a>
                        </li>
                    `;
                } else if (i === currentPage - 3 || i === currentPage + 3) {
                    paginationHtml += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
                }
            }

            paginationHtml += `
                        <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
                            <a class="page-link" href="#" onclick="changePage(${currentPage + 1})">Next</a>
                        </li>
                    </ul>
                </nav>
            `;

            container.innerHTML = paginationHtml;
        }

        function changePage(page) {
            if (page < 1 || page > totalPages) return;
            currentPage = page;
            loadInvoices(page);
            window.scrollTo(0, 0);
        }

        function applyFilters() {
            currentFilters = {
                status: document.getElementById('filterStatus').value,
                vendor_id: document.getElementById('filterVendor').value,
                date_from: document.getElementById('filterDateFrom').value,
                date_to: document.getElementById('filterDateTo').value
            };
            currentPage = 1;
            loadInvoices(currentPage);
        }

        function resetFilters() {
            document.getElementById('filterStatus').value = '';
            document.getElementById('filterVendor').value = '';
            document.getElementById('filterDateFrom').value = '';
            document.getElementById('filterDateTo').value = '';
            currentFilters = {};
            currentPage = 1;
            loadInvoices(currentPage);
        }

        async function searchInvoices() {
            const searchTerm = document.getElementById('searchInvoices').value.trim();
            if (searchTerm) {
                currentFilters.search = searchTerm;
            } else {
                delete currentFilters.search;
            }
            currentPage = 1;
            loadInvoices(currentPage);
        }

        async function viewInvoiceDetail(invoiceId) {
            try {
                const response = await fetch(`<?= site_url('api/accounts-payable/invoices/') ?>${invoiceId}`);
                if (response.ok) {
                    const invoice = await response.json();
                    displayInvoiceDetail(invoice);
                    invoiceModal.show();
                }
            } catch (error) {
                console.error('Error loading invoice details:', error);
                alert('Failed to load invoice details');
            }
        }

        function displayInvoiceDetail(invoice) {
            const container = document.getElementById('invoiceDetailContent');
            
            // Format line items
            const lineItemsHtml = invoice.line_items ? invoice.line_items.map(item => `
                <tr>
                    <td>${item.description}</td>
                    <td>${item.quantity}</td>
                    <td>$${parseFloat(item.unit_price).toFixed(2)}</td>
                    <td>$${parseFloat(item.total).toFixed(2)}</td>
                </tr>
            `).join('') : '';

            container.innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <h6>Invoice Information</h6>
                        <table class="table table-sm">
                            <tr><td><strong>Invoice Number:</strong></td><td>${invoice.invoice_number}</td></tr>
                            <tr><td><strong>Vendor:</strong></td><td>${invoice.vendor_name}</td></tr>
                            <tr><td><strong>Invoice Date:</strong></td><td>${formatDate(invoice.invoice_date)}</td></tr>
                            <tr><td><strong>Due Date:</strong></td><td>${formatDate(invoice.due_date)}</td></tr>
                            <tr><td><strong>Status:</strong></td><td><span class="badge ${getStatusClass(invoice.status)}">${invoice.status.toUpperCase()}</span></td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6>Payment Information</h6>
                        <table class="table table-sm">
                            <tr><td><strong>Total Amount:</strong></td><td class="h5">$${parseFloat(invoice.amount).toFixed(2)}</td></tr>
                            <tr><td><strong>Tax:</strong></td><td>$${parseFloat(invoice.tax_amount || 0).toFixed(2)}</td></tr>
                            <tr><td><strong>Payment Terms:</strong></td><td>${invoice.payment_terms || 'Net 30'}</td></tr>
                            <tr><td><strong>PO Number:</strong></td><td>${invoice.po_number || 'N/A'}</td></tr>
                        </table>
                    </div>
                </div>
                
                <div class="mt-4">
                    <h6>Line Items</h6>
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Description</th>
                                <th>Quantity</th>
                                <th>Unit Price</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${lineItemsHtml}
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-4">
                    <h6>Attachments</h6>
                    ${invoice.attachments ? invoice.attachments.map(attachment => `
                        <a href="${attachment.url}" class="btn btn-sm btn-outline-secondary me-2" target="_blank">
                            <i class="fas fa-paperclip"></i> ${attachment.name}
                        </a>
                    `).join('') : '<p class="text-muted">No attachments</p>'}
                </div>
                
                <div class="mt-4 text-end">
                    <button class="btn btn-primary" onclick="approveInvoice(${invoice.id})">
                        <i class="fas fa-check"></i> Approve Invoice
                    </button>
                    <button class="btn btn-success" onclick="processPayment(${invoice.id})">
                        <i class="fas fa-money-check-alt"></i> Process Payment
                    </button>
                </div>
            `;
        }

        async function approveInvoice(invoiceId) {
            if (!confirm('Are you sure you want to approve this invoice?')) return;
            
            try {
                const response = await fetch(`<?= site_url('api/accounts-payable/invoices/') ?>${invoiceId}/approve`, {
                    method: 'POST'
                });
                
                if (response.ok) {
                    alert('Invoice approved successfully!');
                    loadInvoices(currentPage);
                    invoiceModal.hide();
                } else {
                    throw new Error('Approval failed');
                }
            } catch (error) {
                console.error('Error approving invoice:', error);
                alert('Failed to approve invoice');
            }
        }

        async function processPayment(invoiceId) {
            window.location.href = `<?= site_url('dashboard/accounts_payable/payments/create/') ?>${invoiceId}`;
        }

        async function exportInvoices() {
            try {
                const params = new URLSearchParams(currentFilters);
                window.open(`<?= site_url('api/accounts-payable/invoices/export') ?>?${params}`, '_blank');
            } catch (error) {
                console.error('Error exporting invoices:', error);
                alert('Failed to export invoices');
            }
        }

        function formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            });
        }

        function getStatusClass(status) {
            switch(status) {
                case 'pending': return 'status-pending';
                case 'approved': return 'status-approved';
                case 'overdue': return 'status-overdue';
                case 'paid': return 'status-paid';
                default: return 'badge-secondary';
            }
        }

        // Utility function for debouncing
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