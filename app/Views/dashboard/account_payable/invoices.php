<?php $role = session() ? session()->get('role') ?? 'User' : 'User'; ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Manage Invoices - Accounts Payable</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= base_url('css/site.css') ?>" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Times New Roman', serif; background: #f8f9fa; }
        .app-shell { display: flex; min-height: 100vh; }
        .main { flex: 1; padding: 20px; margin-left: 220px; }
        .invoice-header {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .filter-badge {
            cursor: pointer;
            transition: all 0.3s;
        }
        .filter-badge:hover {
            transform: scale(1.05);
        }
        .invoice-row {
            background: white;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 10px;
            transition: all 0.3s;
            border-left: 4px solid transparent;
        }
        .invoice-row:hover {
            transform: translateX(5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .invoice-row.overdue { border-left-color: #dc3545; }
        .invoice-row.pending { border-left-color: #ffc107; }
        .invoice-row.paid { border-left-color: #28a745; }
    </style>
</head>
<body>
    <div class="app-shell">
        <?= view('partials/sidebar') ?>
        
        <main class="main">
            <div class="invoice-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h4>📋 Invoice Management</h4>
                    <button class="btn btn-primary" onclick="location.href='<?= site_url('dashboard/account_payable/create_invoice') ?>'">
                        <i class="fas fa-plus"></i> New Invoice
                    </button>
                </div>
                
                <div class="mt-3">
                    <span class="badge filter-badge bg-secondary me-2" onclick="filterInvoices('all')">All</span>
                    <span class="badge filter-badge bg-warning me-2" onclick="filterInvoices('pending')">Pending</span>
                    <span class="badge filter-badge bg-danger me-2" onclick="filterInvoices('overdue')">Overdue</span>
                    <span class="badge filter-badge bg-success me-2" onclick="filterInvoices('paid')">Paid</span>
                </div>
            </div>
            
            <div id="invoicesContainer">
                <!-- Invoices will be loaded here -->
                <div class="text-center py-5">
                    <div class="spinner-border" role="status"></div>
                    <p class="mt-2">Loading invoices...</p>
                </div>
            </div>
        </main>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        async function loadInvoices(filter = 'all') {
            const container = document.getElementById('invoicesContainer');
            container.innerHTML = `
                <div class="text-center py-3">
                    <div class="spinner-border spinner-border-sm" role="status"></div>
                    <span class="ms-2">Loading invoices...</span>
                </div>
            `;
            
            try {
                const response = await fetch(`<?= site_url('api/ap/recent-invoices') ?>?filter=${filter}`);
                const invoices = await response.json();
                
                if (invoices.length === 0) {
                    container.innerHTML = `
                        <div class="text-center py-5">
                            <i class="fas fa-file-invoice fa-3x text-muted mb-3"></i>
                            <p>No invoices found</p>
                        </div>
                    `;
                    return;
                }
                
                container.innerHTML = invoices.map(invoice => `
                    <div class="invoice-row ${invoice.status}">
                        <div class="row align-items-center">
                            <div class="col-md-3">
                                <strong>${invoice.invoice_number}</strong>
                                <div class="small text-muted">${invoice.vendor_name}</div>
                            </div>
                            <div class="col-md-2">
                                <strong>$${parseFloat(invoice.amount).toFixed(2)}</strong>
                            </div>
                            <div class="col-md-2">
                                Due: ${formatDate(invoice.due_date)}
                                ${isOverdue(invoice.due_date) ? '<br><small class="text-danger">OVERDUE!</small>' : ''}
                            </div>
                            <div class="col-md-2">
                                <span class="badge bg-${getStatusColor(invoice.status)}">
                                    ${invoice.status.toUpperCase()}
                                </span>
                            </div>
                            <div class="col-md-3 text-end">
                                <div class="btn-group">
                                    <button class="btn btn-sm btn-outline-primary" onclick="viewInvoice(${invoice.id})">
                                        <i class="fas fa-eye"></i> View
                                    </button>
                                    ${invoice.status !== 'paid' ? `
                                        <button class="btn btn-sm btn-success" onclick="processPayment(${invoice.id})">
                                            <i class="fas fa-money-bill-wave"></i> Pay
                                        </button>
                                    ` : ''}
                                    <button class="btn btn-sm btn-outline-secondary" onclick="printInvoice(${invoice.id})">
                                        <i class="fas fa-print"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                `).join('');
                
            } catch (error) {
                container.innerHTML = `
                    <div class="alert alert-danger">
                        Failed to load invoices. Please try again.
                    </div>
                `;
            }
        }
        
        function filterInvoices(filter) {
            loadInvoices(filter);
        }
        
        function viewInvoice(id) {
            window.location.href = `<?= site_url('dashboard/account_payable/invoices/') ?>${id}`;
        }
        
        function processPayment(id) {
            if (confirm('Process payment for this invoice?')) {
                window.location.href = `<?= site_url('dashboard/account_payable/process_payment/') ?>${id}`;
            }
        }
        
        function printInvoice(id) {
            window.open(`<?= site_url('dashboard/account_payable/print/') ?>${id}`, '_blank');
        }
        
        function formatDate(dateString) {
            return new Date(dateString).toLocaleDateString();
        }
        
        function isOverdue(dueDate) {
            return new Date(dueDate) < new Date();
        }
        
        function getStatusColor(status) {
            const colors = {
                'pending': 'warning',
                'overdue': 'danger',
                'paid': 'success',
                'draft': 'secondary'
            };
            return colors[status] || 'secondary';
        }
        
        // Load invoices on page load
        document.addEventListener('DOMContentLoaded', () => loadInvoices('all'));
    </script>
</body>
</html>