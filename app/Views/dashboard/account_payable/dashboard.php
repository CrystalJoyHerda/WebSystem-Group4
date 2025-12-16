<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Accounts Payable Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= base_url('css/site.css') ?>" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { 
            font-family: 'Times New Roman', serif; 
            background: #f8f9fa; 
            margin: 0;
            padding: 0;
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
        .welcome-message {
            font-size: 18px;
            color: #7f8c8d;
        }
        .page-title { 
            text-align: center; 
            font-size: 36px; 
            margin: 20px 0 30px 0;
            color: #34495e;
            font-weight: bold;
            letter-spacing: 1px;
        }
        .stat-card { 
            border-radius: 12px; 
            border: 1px solid #e0e0e0; 
            padding: 24px; 
            text-align: center;
            background: white;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
            height: 100%;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.1);
        }
        .stat-card i {
            font-size: 36px;
            margin-bottom: 15px;
        }
        .stat-card h3 { 
            font-size: 42px; 
            margin: 10px 0;
            font-weight: bold;
        }
        .stat-card h6 {
            color: #7f8c8d;
            font-size: 16px;
            margin-bottom: 10px;
        }
        .stat-link { 
            display: block; 
            color: inherit; 
            text-decoration: none; 
        }
        .stat-link:focus, .stat-link:hover { 
            text-decoration: none; 
        }
        .overdue-badge {
            background-color: #e74c3c;
            color: white;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 12px;
        }
        .pending-badge {
            background-color: #f39c12;
            color: white;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 12px;
        }
        .approved-badge {
            background-color: #27ae60;
            color: white;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 12px;
        }
        .action-buttons {
            margin-top: 20px;
        }
        .action-btn {
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: bold;
            transition: all 0.3s ease;
        }
        .recent-invoices {
            margin-top: 30px;
        }
        .invoice-card {
            border-radius: 8px;
            border: 1px solid #e0e0e0;
            padding: 16px;
            margin-bottom: 12px;
            background: white;
        }
        .invoice-card:hover {
            background-color: #f8f9fa;
        }
        @media (max-width: 900px) { 
            .main { margin-left: 0; padding: 16px; } 
        }
        .alert-section {
            margin-top: 30px;
        }
        .dashboard-section {
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <div class="app-shell">
        <?= view('partials/sidebar') ?>

        <main class="main">
            <div class="header">
                <div class="brand">WeBuild</div>
                <div class="welcome-message">
                    <i class="fas fa-user-circle"></i> Welcome, <?= session()->get('name') ?? 'Accounts Payable Clerk' ?>
                </div>
            </div>

            <div class="page-title">ACCOUNTS PAYABLE DASHBOARD</div>

            <!-- Statistics Cards -->
            <div class="row g-4 dashboard-section">
                <div class="col-md-3">
                    <a class="stat-link" href="<?= site_url('dashboard/accounts_payable/invoices') ?>">
                        <div class="stat-card">
                            <i class="fas fa-file-invoice-dollar text-primary"></i>
                            <h6>Pending Invoices</h6>
                            <h3 id="pendingInvoices">0</h3>
                            <span class="pending-badge">Require Approval</span>
                        </div>
                    </a>
                </div>
                <div class="col-md-3">
                    <a class="stat-link" href="<?= site_url('dashboard/accounts_payable/invoices?status=overdue') ?>">
                        <div class="stat-card">
                            <i class="fas fa-exclamation-triangle text-danger"></i>
                            <h6>Overdue Invoices</h6>
                            <h3 id="overdueInvoices">0</h3>
                            <span class="overdue-badge">Payment Due</span>
                        </div>
                    </a>
                </div>
                <div class="col-md-3">
                    <a class="stat-link" href="<?= site_url('dashboard/accounts_payable/invoices?status=approved') ?>">
                        <div class="stat-card">
                            <i class="fas fa-check-circle text-success"></i>
                            <h6>Approved Invoices</h6>
                            <h3 id="approvedInvoices">0</h3>
                            <span class="approved-badge">Ready for Payment</span>
                        </div>
                    </a>
                </div>
                <div class="col-md-3">
                    <a class="stat-link" href="<?= site_url('dashboard/accounts_payable/vendors') ?>">
                        <div class="stat-card">
                            <i class="fas fa-building text-info"></i>
                            <h6>Active Vendors</h6>
                            <h3 id="activeVendors">0</h3>
                            <span>Vendor Management</span>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="row g-3 action-buttons">
                <div class="col-md-3">
                    <a href="<?= site_url('dashboard/accounts_payable/invoices/create') ?>" class="btn btn-primary btn-lg action-btn w-100">
                        <i class="fas fa-plus-circle"></i> Create New Invoice
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="<?= site_url('dashboard/accounts_payable/vendors/create') ?>" class="btn btn-success btn-lg action-btn w-100">
                        <i class="fas fa-user-plus"></i> Add New Vendor
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="<?= site_url('dashboard/accounts_payable/payments') ?>" class="btn btn-warning btn-lg action-btn w-100">
                        <i class="fas fa-money-check-alt"></i> Process Payments
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="<?= site_url('dashboard/accounts_payable/reports') ?>" class="btn btn-info btn-lg action-btn w-100">
                        <i class="fas fa-chart-bar"></i> View Reports
                    </a>
                </div>
            </div>

            <!-- Recent Invoices Section -->
            <div class="row recent-invoices dashboard-section">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-clock"></i> Recent Invoices Requiring Attention
                            </h5>
                        </div>
                        <div class="card-body">
                            <div id="recentInvoicesList">
                                <div class="text-center py-3">
                                    <div class="spinner-border spinner-border-sm" role="status"></div>
                                    <span class="ms-2">Loading recent invoices...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Alerts -->
            <div class="row alert-section">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-warning text-dark">
                            <h5 class="mb-0">
                                <i class="fas fa-bell"></i> Payment Alerts
                            </h5>
                        </div>
                        <div class="card-body">
                            <div id="paymentAlerts">
                                <div class="text-center py-3">
                                    <div class="spinner-border spinner-border-sm" role="status"></div>
                                    <span class="ms-2">Loading payment alerts...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            loadDashboardData();
            
            // Auto-refresh every 2 minutes
            setInterval(loadDashboardData, 120000);
        });

        async function loadDashboardData() {
            try {
                // Load all dashboard data
                await Promise.all([
                    loadInvoiceStats(),
                    loadRecentInvoices(),
                    loadPaymentAlerts()
                ]);
            } catch (error) {
                console.error('Error loading dashboard data:', error);
            }
        }

        async function loadInvoiceStats() {
            try {
                const response = await fetch('<?= site_url('api/accounts-payable/stats') ?>');
                if (response.ok) {
                    const stats = await response.json();
                    
                    // Update stat cards
                    document.getElementById('pendingInvoices').textContent = stats.pending_invoices || 0;
                    document.getElementById('overdueInvoices').textContent = stats.overdue_invoices || 0;
                    document.getElementById('approvedInvoices').textContent = stats.approved_invoices || 0;
                    document.getElementById('activeVendors').textContent = stats.active_vendors || 0;
                }
            } catch (error) {
                console.error('Error loading invoice stats:', error);
            }
        }

        async function loadRecentInvoices() {
            try {
                const response = await fetch('<?= site_url('api/accounts-payable/recent-invoices') ?>');
                if (response.ok) {
                    const invoices = await response.json();
                    displayRecentInvoices(invoices);
                }
            } catch (error) {
                console.error('Error loading recent invoices:', error);
                document.getElementById('recentInvoicesList').innerHTML = 
                    '<div class="alert alert-warning">Unable to load recent invoices</div>';
            }
        }

        function displayRecentInvoices(invoices) {
            const container = document.getElementById('recentInvoicesList');
            
            if (!invoices || invoices.length === 0) {
                container.innerHTML = '<div class="alert alert-success">✅ No pending invoices requiring attention.</div>';
                return;
            }

            const invoicesHtml = invoices.map(invoice => {
                const dueDate = new Date(invoice.due_date);
                const today = new Date();
                const daysUntilDue = Math.ceil((dueDate - today) / (1000 * 60 * 60 * 24));
                
                let statusBadge = '';
                let statusClass = '';
                
                if (invoice.status === 'overdue') {
                    statusBadge = '<span class="badge bg-danger">OVERDUE</span>';
                    statusClass = 'border-left-danger';
                } else if (invoice.status === 'pending') {
                    statusBadge = '<span class="badge bg-warning">PENDING</span>';
                    statusClass = 'border-left-warning';
                } else if (invoice.status === 'approved') {
                    statusBadge = '<span class="badge bg-success">APPROVED</span>';
                    statusClass = 'border-left-success';
                }
                
                return `
                    <div class="invoice-card ${statusClass}" style="border-left: 4px solid; cursor: pointer;" 
                         onclick="viewInvoice(${invoice.id})">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong>${invoice.invoice_number}</strong>
                                <br>
                                <small class="text-muted">${invoice.vendor_name || 'Unknown Vendor'}</small>
                            </div>
                            <div class="text-end">
                                <div class="h5 mb-1">$${parseFloat(invoice.amount).toFixed(2)}</div>
                                ${statusBadge}
                                <br>
                                <small class="text-muted">Due: ${formatDate(invoice.due_date)} (${daysUntilDue > 0 ? daysUntilDue + ' days' : 'Today'})</small>
                            </div>
                        </div>
                    </div>
                `;
            }).join('');

            container.innerHTML = invoicesHtml;
        }

        async function loadPaymentAlerts() {
            try {
                const response = await fetch('<?= site_url('api/accounts-payable/payment-alerts') ?>');
                if (response.ok) {
                    const alerts = await response.json();
                    displayPaymentAlerts(alerts);
                }
            } catch (error) {
                console.error('Error loading payment alerts:', error);
                document.getElementById('paymentAlerts').innerHTML = 
                    '<div class="alert alert-warning">Unable to load payment alerts</div>';
            }
        }

        function displayPaymentAlerts(alerts) {
            const container = document.getElementById('paymentAlerts');
            
            if (!alerts || alerts.length === 0) {
                container.innerHTML = '<div class="alert alert-success">✅ No payment alerts at this time.</div>';
                return;
            }

            const alertsHtml = alerts.map(alert => {
                const alertType = alert.type || 'info';
                const icon = alertType === 'urgent' ? 'exclamation-triangle' : 
                            alertType === 'warning' ? 'exclamation-circle' : 'info-circle';
                const alertClass = alertType === 'urgent' ? 'danger' : 
                                 alertType === 'warning' ? 'warning' : 'info';
                
                return `
                    <div class="alert alert-${alertClass} d-flex align-items-center">
                        <i class="fas fa-${icon} fa-2x me-3"></i>
                        <div class="flex-grow-1">
                            <strong>${alert.title}</strong>
                            <div>${alert.message}</div>
                            <small class="text-muted">${formatDate(alert.date)}</small>
                        </div>
                        ${alert.action_url ? 
                            `<a href="${alert.action_url}" class="btn btn-sm btn-${alertClass}">${alert.action_text || 'View'}</a>` : 
                            ''
                        }
                    </div>
                `;
            }).join('');

            container.innerHTML = alertsHtml;
        }

        function formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            });
        }

        function viewInvoice(invoiceId) {
            window.location.href = `<?= site_url('dashboard/accounts_payable/invoices/') ?>${invoiceId}`;
        }
    </script>
</body>
</html>