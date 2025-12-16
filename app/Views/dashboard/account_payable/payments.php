<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Payments - Accounts Payable</title>
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
        .payment-method-badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }
        .method-check { background-color: #3498db; color: white; }
        .method-bank { background-color: #9b59b6; color: white; }
        .method-card { background-color: #e74c3c; color: white; }
        .method-cash { background-color: #27ae60; color: white; }
        .payment-status-badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }
        .status-pending { background-color: #f39c12; color: white; }
        .status-completed { background-color: #27ae60; color: white; }
        .status-failed { background-color: #e74c3c; color: white; }
        .payment-card {
            border-radius: 10px;
            border: 1px solid #e0e0e0;
            padding: 20px;
            margin-bottom: 15px;
            background: white;
            transition: all 0.3s ease;
        }
        .payment-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .calendar-container {
            background: white;
            border-radius: 10px;
            padding: 20px;
            border: 1px solid #e0e0e0;
        }
        .calendar-day {
            padding: 10px;
            text-align: center;
            border-radius: 8px;
            cursor: pointer;
        }
        .calendar-day:hover {
            background-color: #f8f9fa;
        }
        .calendar-day.payment-due {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
        }
        .calendar-day.payment-made {
            background-color: #d1ecf1;
            border: 1px solid #bee5eb;
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
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#processPaymentModal">
                        <i class="fas fa-money-check-alt"></i> Process Payment
                    </button>
                </div>
            </div>

            <div class="page-title">PAYMENT PROCESSING</div>

            <!-- Payment Stats -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body text-center">
                            <i class="fas fa-calendar-check fa-2x mb-3"></i>
                            <h4 id="dueThisWeek">0</h4>
                            <p class="mb-0">Due This Week</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-dark">
                        <div class="card-body text-center">
                            <i class="fas fa-exclamation-triangle fa-2x mb-3"></i>
                            <h4 id="overduePayments">0</h4>
                            <p class="mb-0">Overdue Payments</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body text-center">
                            <i class="fas fa-check-circle fa-2x mb-3"></i>
                            <h4 id="processedThisMonth">0</h4>
                            <p class="mb-0">Processed This Month</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body text-center">
                            <i class="fas fa-dollar-sign fa-2x mb-3"></i>
                            <h4 id="totalProcessed">$0</h4>
                            <p class="mb-0">Total Processed</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Payment Calendar -->
                <div class="col-md-4 mb-4">
                    <div class="calendar-container">
                        <h5 class="mb-3">
                            <i class="fas fa-calendar-alt me-2"></i>Payment Calendar
                        </h5>
                        <div id="paymentCalendar">
                            <div class="text-center py-3">
                                <div class="spinner-border spinner-border-sm" role="status"></div>
                                <span class="ms-2">Loading calendar...</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Payments -->
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header bg-light">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">
                                    <i class="fas fa-history me-2"></i>Recent Payments
                                </h5>
                                <div class="btn-group">
                                    <button class="btn btn-sm btn-outline-secondary" onclick="loadPayments('all')">All</button>
                                    <button class="btn btn-sm btn-outline-secondary" onclick="loadPayments('pending')">Pending</button>
                                    <button class="btn btn-sm btn-outline-secondary" onclick="loadPayments('completed')">Completed</button>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div id="paymentsList">
                                <div class="text-center py-3">
                                    <div class="spinner-border spinner-border-sm" role="status"></div>
                                    <span class="ms-2">Loading payments...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Scheduled Payments -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-clock me-2"></i>Scheduled Payments
                            </h5>
                        </div>
                        <div class="card-body">
                            <div id="scheduledPayments">
                                <div class="text-center py-3">
                                    <div class="spinner-border spinner-border-sm" role="status"></div>
                                    <span class="ms-2">Loading scheduled payments...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Process Payment Modal -->
    <div class="modal fade" id="processPaymentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Process Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="processPaymentForm">
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Select Invoice *</label>
                                <select id="invoiceSelect" class="form-select" required>
                                    <option value="">Select an invoice</option>
                                    <!-- Populated by JavaScript -->
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Payment Date *</label>
                                <input type="date" id="paymentDate" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Payment Method *</label>
                                <select id="paymentMethod" class="form-select" required>
                                    <option value="">Select method</option>
                                    <option value="check">Check</option>
                                    <option value="bank_transfer">Bank Transfer</option>
                                    <option value="credit_card">Credit Card</option>
                                    <option value="cash">Cash</option>
                                    <option value="wire">Wire Transfer</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Amount *</label>
                                <input type="number" id="paymentAmount" class="form-control" step="0.01" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Reference Number</label>
                                <input type="text" id="paymentReference" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Payment Status</label>
                                <select id="paymentStatus" class="form-select">
                                    <option value="pending">Pending</option>
                                    <option value="completed">Completed</option>
                                </select>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Notes</label>
                                <textarea id="paymentNotes" class="form-control" rows="3"></textarea>
                            </div>
                        </div>
                        
                        <!-- Invoice Details (shown when invoice is selected) -->
                        <div id="invoiceDetails" class="mt-4" style="display: none;">
                            <h6>Invoice Details</h6>
                            <div class="card">
                                <div class="card-body" id="selectedInvoiceDetails">
                                    <!-- Invoice details will be displayed here -->
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Process Payment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const processPaymentModal = new bootstrap.Modal(document.getElementById('processPaymentModal'));
        
        document.addEventListener('DOMContentLoaded', function() {
            loadPaymentStats();
            loadPayments('all');
            loadScheduledPayments();
            loadPaymentCalendar();
            loadApprovedInvoices();
            
            // Set today's date as default payment date
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('paymentDate').value = today;
            
            // Event listeners
            document.getElementById('processPaymentForm').addEventListener('submit', processPayment);
            document.getElementById('invoiceSelect').addEventListener('change', loadInvoiceDetails);
        });

        async function loadPaymentStats() {
            try {
                const response = await fetch('<?= site_url('api/accounts-payable/payments/stats') ?>');
                if (response.ok) {
                    const stats = await response.json();
                    
                    document.getElementById('dueThisWeek').textContent = stats.due_this_week || 0;
                    document.getElementById('overduePayments').textContent = stats.overdue_payments || 0;
                    document.getElementById('processedThisMonth').textContent = stats.processed_this_month || 0;
                    document.getElementById('totalProcessed').textContent = `$${parseFloat(stats.total_processed || 0).toFixed(2)}`;
                }
            } catch (error) {
                console.error('Error loading payment stats:', error);
            }
        }

        async function loadPayments(filter = 'all') {
            try {
                const params = new URLSearchParams({ filter: filter });
                const response = await fetch(`<?= site_url('api/accounts-payable/payments') ?>?${params}`);
                
                if (response.ok) {
                    const payments = await response.json();
                    displayPayments(payments);
                }
            } catch (error) {
                console.error('Error loading payments:', error);
                document.getElementById('paymentsList').innerHTML = `
                    <div class="alert alert-danger">
                        Failed to load payments. Please try again.
                    </div>
                `;
            }
        }

        function displayPayments(payments) {
            const container = document.getElementById('paymentsList');
            
            if (!payments || payments.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-5">
                        <i class="fas fa-money-check-alt fa-3x text-muted mb-3"></i>
                        <h5>No payments found</h5>
                        <p class="text-muted">No payments match the selected filter.</p>
                    </div>
                `;
                return;
            }

            const paymentsHtml = payments.map(payment => {
                const methodClass = payment.payment_method === 'check' ? 'method-check' :
                                   payment.payment_method === 'bank_transfer' ? 'method-bank' :
                                   payment.payment_method === 'credit_card' ? 'method-card' :
                                   payment.payment_method === 'cash' ? 'method-cash' : 'method-check';
                
                const statusClass = payment.status === 'pending' ? 'status-pending' :
                                   payment.status === 'completed' ? 'status-completed' : 'status-failed';
                
                return `
                    <div class="payment-card">
                        <div class="d-flex justify-content-between align-items-center">
                            <div style="flex: 2;">
                                <div class="d-flex align-items-center">
                                    <span class="payment-method-badge ${methodClass} me-2">
                                        ${payment.payment_method.toUpperCase().replace('_', ' ')}
                                    </span>
                                    <span class="payment-status-badge ${statusClass}">
                                        ${payment.status.toUpperCase()}
                                    </span>
                                </div>
                                <div class="mt-1">
                                    <strong>${payment.invoice_number}</strong>
                                    <br>
                                    <small class="text-muted">${payment.vendor_name}</small>
                                </div>
                            </div>
                            <div style="flex: 1;" class="text-center">
                                <div class="h4 mb-0">$${parseFloat(payment.amount).toFixed(2)}</div>
                                <small class="text-muted">${payment.currency || 'USD'}</small>
                            </div>
                            <div style="flex: 1;">
                                <div class="text-end">
                                    <small class="text-muted">Payment Date</small>
                                    <br>
                                    <strong>${formatDate(payment.payment_date)}</strong>
                                    <br>
                                    <small class="text-muted">Ref: ${payment.reference_number || 'N/A'}</small>
                                </div>
                            </div>
                        </div>
                        ${payment.notes ? `
                            <div class="mt-3 p-2 bg-light rounded">
                                <small class="text-muted">${payment.notes}</small>
                            </div>
                        ` : ''}
                    </div>
                `;
            }).join('');

            container.innerHTML = paymentsHtml;
        }

        async function loadScheduledPayments() {
            try {
                const response = await fetch('<?= site_url('api/accounts-payable/payments/scheduled') ?>');
                if (response.ok) {
                    const scheduled = await response.json();
                    displayScheduledPayments(scheduled);
                }
            } catch (error) {
                console.error('Error loading scheduled payments:', error);
                document.getElementById('scheduledPayments').innerHTML = `
                    <div class="alert alert-warning">
                        Failed to load scheduled payments.
                    </div>
                `;
            }
        }

        function displayScheduledPayments(scheduled) {
            const container = document.getElementById('scheduledPayments');
            
            if (!scheduled || scheduled.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-3">
                        <i class="fas fa-calendar-check fa-2x text-muted mb-3"></i>
                        <p class="text-muted">No scheduled payments</p>
                    </div>
                `;
                return;
            }

            const scheduledHtml = scheduled.map(schedule => {
                const daysUntil = Math.ceil((new Date(schedule.due_date) - new Date()) / (1000 * 60 * 60 * 24));
                const statusClass = daysUntil < 0 ? 'danger' : daysUntil <= 3 ? 'warning' : 'info';
                
                return `
                    <div class="alert alert-${statusClass} d-flex justify-content-between align-items-center">
                        <div>
                            <strong>${schedule.invoice_number}</strong>
                            <br>
                            <small>${schedule.vendor_name} - $${parseFloat(schedule.amount).toFixed(2)}</small>
                        </div>
                        <div class="text-end">
                            <div><strong>${formatDate(schedule.due_date)}</strong></div>
                            <small>${daysUntil < 0 ? `${Math.abs(daysUntil)} days overdue` : 
                                     daysUntil === 0 ? 'Due today' : 
                                     `${daysUntil} days remaining`}</small>
                        </div>
                    </div>
                `;
            }).join('');

            container.innerHTML = scheduledHtml;
        }

        async function loadPaymentCalendar() {
            try {
                const response = await fetch('<?= site_url('api/accounts-payable/payments/calendar') ?>');
                if (response.ok) {
                    const calendarData = await response.json();
                    displayPaymentCalendar(calendarData);
                }
            } catch (error) {
                console.error('Error loading payment calendar:', error);
            }
        }

        function displayPaymentCalendar(calendarData) {
            const container = document.getElementById('paymentCalendar');
            
            // Create a simple calendar view
            const now = new Date();
            const year = now.getFullYear();
            const month = now.getMonth();
            
            // Get first day of month
            const firstDay = new Date(year, month, 1);
            // Get last day of month
            const lastDay = new Date(year, month + 1, 0);
            
            let calendarHtml = `
                <div class="text-center mb-3">
                    <h6>${now.toLocaleDateString('en-US', { month: 'long', year: 'numeric' })}</h6>
                </div>
                <div class="row">
                    ${['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'].map(day => `
                        <div class="col p-1 text-center small text-muted">
                            ${day}
                        </div>
                    `).join('')}
                </div>
                <div class="row">
            `;
            
            // Add empty cells for days before the first day of month
            for (let i = 0; i < firstDay.getDay(); i++) {
                calendarHtml += `<div class="col p-1"></div>`;
            }
            
            // Add days of the month
            for (let day = 1; day <= lastDay.getDate(); day++) {
                const currentDate = new Date(year, month, day);
                const dateStr = currentDate.toISOString().split('T')[0];
                
                // Check if this date has payments
                const dayPayments = calendarData[dateStr] || [];
                const hasDue = dayPayments.some(p => p.type === 'due');
                const hasPaid = dayPayments.some(p => p.type === 'paid');
                
                let dayClass = 'calendar-day';
                let dayTitle = '';
                
                if (hasDue && hasPaid) {
                    dayClass += ' payment-due payment-made';
                    dayTitle = `${dayPayments.length} payments due and paid`;
                } else if (hasDue) {
                    dayClass += ' payment-due';
                    dayTitle = `${dayPayments.length} payments due`;
                } else if (hasPaid) {
                    dayClass += ' payment-made';
                    dayTitle = `${dayPayments.length} payments made`;
                }
                
                calendarHtml += `
                    <div class="col p-1">
                        <div class="${dayClass}" title="${dayTitle}">
                            ${day}
                            ${dayPayments.length > 0 ? 
                                `<div class="small text-muted">${dayPayments.length}</div>` : 
                                ''
                            }
                        </div>
                    </div>
                `;
                
                // Start new row after Saturday
                if ((firstDay.getDay() + day) % 7 === 0) {
                    calendarHtml += `</div><div class="row">`;
                }
            }
            
            calendarHtml += `</div>`;
            container.innerHTML = calendarHtml;
        }

        async function loadApprovedInvoices() {
            try {
                const response = await fetch('<?= site_url('api/accounts-payable/invoices/approved') ?>');
                if (response.ok) {
                    const invoices = await response.json();
                    populateInvoiceSelect(invoices);
                }
            } catch (error) {
                console.error('Error loading approved invoices:', error);
            }
        }

        function populateInvoiceSelect(invoices) {
            const select = document.getElementById('invoiceSelect');
            select.innerHTML = '<option value="">Select an invoice</option>';
            
            invoices.forEach(invoice => {
                const option = document.createElement('option');
                option.value = invoice.id;
                option.textContent = `${invoice.invoice_number} - ${invoice.vendor_name} - $${parseFloat(invoice.amount).toFixed(2)}`;
                option.dataset.invoiceData = JSON.stringify(invoice);
                select.appendChild(option);
            });
        }

        async function loadInvoiceDetails(event) {
            const invoiceId = event.target.value;
            const container = document.getElementById('invoiceDetails');
            const detailsContainer = document.getElementById('selectedInvoiceDetails');
            
            if (!invoiceId) {
                container.style.display = 'none';
                document.getElementById('paymentAmount').value = '';
                return;
            }
            
            // Get the selected option's data
            const selectedOption = event.target.options[event.target.selectedIndex];
            const invoiceData = JSON.parse(selectedOption.dataset.invoiceData);
            
            detailsContainer.innerHTML = `
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <strong>${invoiceData.invoice_number}</strong>
                    <span class="badge bg-success">APPROVED</span>
                </div>
                <div class="row">
                    <div class="col-6">
                        <small class="text-muted">Vendor:</small>
                        <div>${invoiceData.vendor_name}</div>
                    </div>
                    <div class="col-6">
                        <small class="text-muted">Amount Due:</small>
                        <div class="h5">$${parseFloat(invoiceData.amount).toFixed(2)}</div>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-6">
                        <small class="text-muted">Invoice Date:</small>
                        <div>${formatDate(invoiceData.invoice_date)}</div>
                    </div>
                    <div class="col-6">
                        <small class="text-muted">Due Date:</small>
                        <div class="${new Date(invoiceData.due_date) < new Date() ? 'text-danger' : ''}">
                            ${formatDate(invoiceData.due_date)}
                        </div>
                    </div>
                </div>
            `;
            
            // Set the payment amount to the invoice amount
            document.getElementById('paymentAmount').value = parseFloat(invoiceData.amount).toFixed(2);
            
            container.style.display = 'block';
        }

        async function processPayment(event) {
            event.preventDefault();
            
            const paymentData = {
                invoice_id: document.getElementById('invoiceSelect').value,
                payment_date: document.getElementById('paymentDate').value,
                payment_method: document.getElementById('paymentMethod').value,
                amount: document.getElementById('paymentAmount').value,
                reference_number: document.getElementById('paymentReference').value,
                status: document.getElementById('paymentStatus').value,
                notes: document.getElementById('paymentNotes').value
            };
            
            try {
                const response = await fetch('<?= site_url('api/accounts-payable/payments/process') ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(paymentData)
                });
                
                if (response.ok) {
                    alert('Payment processed successfully!');
                    processPaymentModal.hide();
                    event.target.reset();
                    
                    // Refresh all data
                    loadPaymentStats();
                    loadPayments('all');
                    loadScheduledPayments();
                    loadPaymentCalendar();
                    loadApprovedInvoices();
                } else {
                    throw new Error('Payment processing failed');
                }
            } catch (error) {
                console.error('Error processing payment:', error);
                alert('Failed to process payment: ' + error.message);
            }
        }

        function formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', {
                month: 'short',
                day: 'numeric',
                year: 'numeric'
            });
        }
    </script>
</body>
</html>