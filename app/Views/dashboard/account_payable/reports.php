<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reports - Accounts Payable</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Include Chart.js for reports -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        .report-card {
            border-radius: 10px;
            border: 1px solid #e0e0e0;
            padding: 20px;
            margin-bottom: 20px;
            background: white;
            height: 100%;
        }
        .report-header {
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .report-chart-container {
            position: relative;
            height: 300px;
            margin-bottom: 20px;
        }
        .report-summary {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-top: 20px;
        }
        .summary-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #e0e0e0;
        }
        .summary-item:last-child {
            border-bottom: none;
        }
        .report-filter-section {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            border: 1px solid #e0e0e0;
        }
        .download-btn {
            min-width: 120px;
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
                    <a href="<?= site_url('dashboard/accounts_payable/dashboard') ?>" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                </div>
            </div>

            <div class="page-title">ACCOUNTS PAYABLE REPORTS</div>

            <!-- Report Filters -->
            <div class="report-filter-section">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Report Type</label>
                        <select id="reportType" class="form-select">
                            <option value="overview">Overview Dashboard</option>
                            <option value="aging">Aging Report</option>
                            <option value="vendor">Vendor Analysis</option>
                            <option value="payment">Payment History</option>
                            <option value="cashflow">Cash Flow Projection</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Date Range</label>
                        <select id="dateRange" class="form-select">
                            <option value="this_month">This Month</option>
                            <option value="last_month">Last Month</option>
                            <option value="this_quarter">This Quarter</option>
                            <option value="last_quarter">Last Quarter</option>
                            <option value="this_year">This Year</option>
                            <option value="last_year">Last Year</option>
                            <option value="custom">Custom Range</option>
                        </select>
                    </div>
                    <div class="col-md-3 custom-date-range" style="display: none;">
                        <label class="form-label">From Date</label>
                        <input type="date" id="dateFrom" class="form-control">
                    </div>
                    <div class="col-md-3 custom-date-range" style="display: none;">
                        <label class="form-label">To Date</label>
                        <input type="date" id="dateTo" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <div>
                            <button id="generateReport" class="btn btn-primary w-100">
                                <i class="fas fa-chart-bar"></i> Generate Report
                            </button>
                        </div>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-12">
                        <div class="d-flex gap-2">
                            <button class="btn btn-success download-btn" onclick="exportReport('pdf')">
                                <i class="fas fa-file-pdf"></i> PDF
                            </button>
                            <button class="btn btn-warning download-btn" onclick="exportReport('excel')">
                                <i class="fas fa-file-excel"></i> Excel
                            </button>
                            <button class="btn btn-info download-btn" onclick="exportReport('csv')">
                                <i class="fas fa-file-csv"></i> CSV
                            </button>
                            <button class="btn btn-secondary download-btn" onclick="printReport()">
                                <i class="fas fa-print"></i> Print
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Report Content -->
            <div id="reportContent">
                <!-- Overview Dashboard (Default) -->
                <div id="overviewReport" class="report-content">
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="report-card">
                                <div class="report-header">
                                    <h5>
                                        <i class="fas fa-tachometer-alt me-2"></i>Accounts Payable Overview
                                    </h5>
                                </div>
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="report-summary">
                                            <div class="summary-item">
                                                <span>Total Payables:</span>
                                                <strong class="text-primary" id="totalPayables">$0.00</strong>
                                            </div>
                                            <div class="summary-item">
                                                <span>Current (0-30 days):</span>
                                                <strong id="currentPayables">$0.00</strong>
                                            </div>
                                            <div class="summary-item">
                                                <span>31-60 days:</span>
                                                <strong class="text-warning" id="payables31to60">$0.00</strong>
                                            </div>
                                            <div class="summary-item">
                                                <span>61-90 days:</span>
                                                <strong class="text-danger" id="payables61to90">$0.00</strong>
                                            </div>
                                            <div class="summary-item">
                                                <span>> 90 days:</span>
                                                <strong class="text-danger" id="payablesOver90">$0.00</strong>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-9">
                                        <div class="report-chart-container">
                                            <canvas id="agingChart"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="report-card">
                                <div class="report-header">
                                    <h5>
                                        <i class="fas fa-building me-2"></i>Top Vendors by Payables
                                    </h5>
                                </div>
                                <div class="report-chart-container">
                                    <canvas id="vendorsChart"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="report-card">
                                <div class="report-header">
                                    <h5>
                                        <i class="fas fa-calendar me-2"></i>Monthly Payment Trend
                                    </h5>
                                </div>
                                <div class="report-chart-container">
                                    <canvas id="monthlyTrendChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="report-card">
                                <div class="report-header">
                                    <h5>
                                        <i class="fas fa-list me-2"></i>Recent Transactions
                                    </h5>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-hover" id="recentTransactions">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Vendor</th>
                                                <th>Invoice</th>
                                                <th>Amount</th>
                                                <th>Status</th>
                                                <th>Due Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Filled by JavaScript -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Aging Report -->
                <div id="agingReport" class="report-content" style="display: none;">
                    <div class="report-card">
                        <div class="report-header">
                            <h5>
                                <i class="fas fa-clock me-2"></i>Aging Report
                            </h5>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover" id="agingReportTable">
                                <thead>
                                    <tr>
                                        <th>Vendor</th>
                                        <th>Invoice</th>
                                        <th>Invoice Date</th>
                                        <th>Due Date</th>
                                        <th>Amount</th>
                                        <th>Current</th>
                                        <th>31-60 days</th>
                                        <th>61-90 days</th>
                                        <th>> 90 days</th>
                                        <th>Total Due</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Filled by JavaScript -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Vendor Analysis -->
                <div id="vendorReport" class="report-content" style="display: none;">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="report-card">
                                <div class="report-header">
                                    <h5>
                                        <i class="fas fa-chart-pie me-2"></i>Vendor Distribution
                                    </h5>
                                </div>
                                <div class="report-chart-container">
                                    <canvas id="vendorDistributionChart"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="report-card">
                                <div class="report-header">
                                    <h5>
                                        <i class="fas fa-star me-2"></i>Top 5 Vendors
                                    </h5>
                                </div>
                                <div id="topVendorsList">
                                    <!-- Filled by JavaScript -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment History -->
                <div id="paymentReport" class="report-content" style="display: none;">
                    <div class="report-card">
                        <div class="report-header">
                            <h5>
                                <i class="fas fa-history me-2"></i>Payment History Report
                            </h5>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover" id="paymentHistoryTable">
                                <thead>
                                    <tr>
                                        <th>Payment Date</th>
                                        <th>Vendor</th>
                                        <th>Invoice</th>
                                        <th>Payment Method</th>
                                        <th>Amount</th>
                                        <th>Reference</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Filled by JavaScript -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Cash Flow Projection -->
                <div id="cashflowReport" class="report-content" style="display: none;">
                    <div class="report-card">
                        <div class="report-header">
                            <h5>
                                <i class="fas fa-chart-line me-2"></i>Cash Flow Projection
                            </h5>
                        </div>
                        <div class="report-chart-container">
                            <canvas id="cashflowChart"></canvas>
                        </div>
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <div class="report-summary">
                                    <h6>Upcoming Payments</h6>
                                    <div id="upcomingPayments">
                                        <!-- Filled by JavaScript -->
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="report-summary">
                                    <h6>Cash Requirements</h6>
                                    <div class="summary-item">
                                        <span>This Week:</span>
                                        <strong class="text-warning" id="cashRequiredWeek">$0.00</strong>
                                    </div>
                                    <div class="summary-item">
                                        <span>This Month:</span>
                                        <strong class="text-danger" id="cashRequiredMonth">$0.00</strong>
                                    </div>
                                    <div class="summary-item">
                                        <span>Next Month:</span>
                                        <strong id="cashRequiredNextMonth">$0.00</strong>
                                    </div>
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
        let chartInstances = {};
        
        document.addEventListener('DOMContentLoaded', function() {
            // Set default dates
            const today = new Date();
            const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
            const lastDay = new Date(today.getFullYear(), today.getMonth() + 1, 0);
            
            document.getElementById('dateFrom').value = firstDay.toISOString().split('T')[0];
            document.getElementById('dateTo').value = lastDay.toISOString().split('T')[0];
            
            // Event listeners
            document.getElementById('dateRange').addEventListener('change', handleDateRangeChange);
            document.getElementById('generateReport').addEventListener('click', generateReport);
            
            // Load default report
            loadOverviewReport();
        });

        function handleDateRangeChange() {
            const range = document.getElementById('dateRange').value;
            const customRangeElements = document.querySelectorAll('.custom-date-range');
            
            if (range === 'custom') {
                customRangeElements.forEach(el => el.style.display = 'block');
            } else {
                customRangeElements.forEach(el => el.style.display = 'none');
            }
        }

        function switchReport(reportType) {
            // Hide all reports
            document.querySelectorAll('.report-content').forEach(el => {
                el.style.display = 'none';
            });
            
            // Show selected report
            document.getElementById(`${reportType}Report`).style.display = 'block';
            
            // Destroy existing charts
            Object.values(chartInstances).forEach(chart => {
                if (chart) chart.destroy();
            });
            chartInstances = {};
        }

        async function generateReport() {
            const reportType = document.getElementById('reportType').value;
            const dateRange = document.getElementById('dateRange').value;
            
            switchReport(reportType);
            
            switch(reportType) {
                case 'overview':
                    await loadOverviewReport();
                    break;
                case 'aging':
                    await loadAgingReport();
                    break;
                case 'vendor':
                    await loadVendorReport();
                    break;
                case 'payment':
                    await loadPaymentReport();
                    break;
                case 'cashflow':
                    await loadCashflowReport();
                    break;
            }
        }

        async function loadOverviewReport() {
            try {
                const response = await fetch('<?= site_url('api/accounts-payable/reports/overview') ?>');
                if (response.ok) {
                    const data = await response.json();
                    
                    // Update summary numbers
                    document.getElementById('totalPayables').textContent = `$${data.total_payables.toFixed(2)}`;
                    document.getElementById('currentPayables').textContent = `$${data.current_payables.toFixed(2)}`;
                    document.getElementById('payables31to60').textContent = `$${data.payables_31_60.toFixed(2)}`;
                    document.getElementById('payables61to90').textContent = `$${data.payables_61_90.toFixed(2)}`;
                    document.getElementById('payablesOver90').textContent = `$${data.payables_over_90.toFixed(2)}`;
                    
                    // Create aging chart
                    createAgingChart(data.aging_data);
                    
                    // Create vendors chart
                    createVendorsChart(data.top_vendors);
                    
                    // Create monthly trend chart
                    createMonthlyTrendChart(data.monthly_trend);
                    
                    // Populate recent transactions
                    populateRecentTransactions(data.recent_transactions);
                }
            } catch (error) {
                console.error('Error loading overview report:', error);
            }
        }

        function createAgingChart(agingData) {
            const ctx = document.getElementById('agingChart').getContext('2d');
            
            if (chartInstances.aging) chartInstances.aging.destroy();
            
            chartInstances.aging = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: agingData.labels || ['Current', '31-60 days', '61-90 days', '> 90 days'],
                    datasets: [{
                        label: 'Amount ($)',
                        data: agingData.values || [0, 0, 0, 0],
                        backgroundColor: [
                            '#4CAF50',
                            '#FF9800',
                            '#FF5722',
                            '#F44336'
                        ],
                        borderColor: [
                            '#388E3C',
                            '#F57C00',
                            '#D84315',
                            '#D32F2F'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        title: {
                            display: true,
                            text: 'Accounts Payable Aging'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '$' + value.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });
        }

        function createVendorsChart(vendorsData) {
            const ctx = document.getElementById('vendorsChart').getContext('2d');
            
            if (chartInstances.vendors) chartInstances.vendors.destroy();
            
            chartInstances.vendors = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: vendorsData.map(v => v.vendor_name),
                    datasets: [{
                        data: vendorsData.map(v => v.amount),
                        backgroundColor: [
                            '#FF6384',
                            '#36A2EB',
                            '#FFCE56',
                            '#4BC0C0',
                            '#9966FF',
                            '#FF9F40'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        },
                        title: {
                            display: true,
                            text: 'Top Vendors by Payables'
                        }
                    }
                }
            });
        }

        function createMonthlyTrendChart(trendData) {
            const ctx = document.getElementById('monthlyTrendChart').getContext('2d');
            
            if (chartInstances.trend) chartInstances.trend.destroy();
            
            chartInstances.trend = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: trendData.months || [],
                    datasets: [{
                        label: 'Payments ($)',
                        data: trendData.amounts || [],
                        borderColor: '#2196F3',
                        backgroundColor: 'rgba(33, 150, 243, 0.1)',
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        title: {
                            display: true,
                            text: 'Monthly Payment Trend'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '$' + value.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });
        }

        function populateRecentTransactions(transactions) {
            const tbody = document.querySelector('#recentTransactions tbody');
            
            if (!transactions || transactions.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="6" class="text-center text-muted py-3">
                            No recent transactions
                        </td>
                    </tr>
                `;
                return;
            }
            
            tbody.innerHTML = transactions.map(transaction => {
                const dueDate = new Date(transaction.due_date);
                const today = new Date();
                const daysUntilDue = Math.ceil((dueDate - today) / (1000 * 60 * 60 * 24));
                
                let statusBadge = '';
                if (transaction.status === 'overdue') {
                    statusBadge = '<span class="badge bg-danger">OVERDUE</span>';
                } else if (transaction.status === 'pending') {
                    statusBadge = '<span class="badge bg-warning">PENDING</span>';
                } else if (transaction.status === 'paid') {
                    statusBadge = '<span class="badge bg-success">PAID</span>';
                }
                
                return `
                    <tr>
                        <td>${formatDate(transaction.invoice_date)}</td>
                        <td>${transaction.vendor_name}</td>
                        <td>${transaction.invoice_number}</td>
                        <td>$${parseFloat(transaction.amount).toFixed(2)}</td>
                        <td>${statusBadge}</td>
                        <td class="${daysUntilDue < 0 ? 'text-danger' : ''}">
                            ${formatDate(transaction.due_date)}
                        </td>
                    </tr>
                `;
            }).join('');
        }

        async function loadAgingReport() {
            try {
                const response = await fetch('<?= site_url('api/accounts-payable/reports/aging') ?>');
                if (response.ok) {
                    const data = await response.json();
                    displayAgingReport(data);
                }
            } catch (error) {
                console.error('Error loading aging report:', error);
            }
        }

        function displayAgingReport(data) {
            const tbody = document.querySelector('#agingReportTable tbody');
            
            if (!data || data.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="10" class="text-center text-muted py-3">
                            No aging data available
                        </td>
                    </tr>
                `;
                return;
            }
            
            tbody.innerHTML = data.map(item => `
                <tr>
                    <td>${item.vendor_name}</td>
                    <td>${item.invoice_number}</td>
                    <td>${formatDate(item.invoice_date)}</td>
                    <td>${formatDate(item.due_date)}</td>
                    <td>$${parseFloat(item.amount).toFixed(2)}</td>
                    <td>$${parseFloat(item.current || 0).toFixed(2)}</td>
                    <td>$${parseFloat(item.days_31_60 || 0).toFixed(2)}</td>
                    <td>$${parseFloat(item.days_61_90 || 0).toFixed(2)}</td>
                    <td>$${parseFloat(item.over_90 || 0).toFixed(2)}</td>
                    <td class="fw-bold">$${parseFloat(item.total_due).toFixed(2)}</td>
                </tr>
            `).join('');
        }

        async function loadVendorReport() {
            try {
                const response = await fetch('<?= site_url('api/accounts-payable/reports/vendor-analysis') ?>');
                if (response.ok) {
                    const data = await response.json();
                    displayVendorReport(data);
                }
            } catch (error) {
                console.error('Error loading vendor report:', error);
            }
        }

        function displayVendorReport(data) {
            // Create vendor distribution chart
            const ctx = document.getElementById('vendorDistributionChart').getContext('2d');
            
            if (chartInstances.vendorDistribution) chartInstances.vendorDistribution.destroy();
            
            chartInstances.vendorDistribution = new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: data.categories?.map(c => c.name) || [],
                    datasets: [{
                        data: data.categories?.map(c => c.value) || [],
                        backgroundColor: [
                            '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', 
                            '#9966FF', '#FF9F40', '#C9CBCF', '#7C4DFF'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        title: {
                            display: true,
                            text: 'Vendor Distribution by Category'
                        }
                    }
                }
            });
            
            // Display top vendors list
            const topVendorsList = document.getElementById('topVendorsList');
            
            if (!data.top_vendors || data.top_vendors.length === 0) {
                topVendorsList.innerHTML = '<p class="text-muted">No vendor data available</p>';
                return;
            }
            
            topVendorsList.innerHTML = data.top_vendors.map((vendor, index) => `
                <div class="d-flex justify-content-between align-items-center mb-2 p-2 border-bottom">
                    <div>
                        <div class="fw-bold">${index + 1}. ${vendor.vendor_name}</div>
                        <small class="text-muted">${vendor.invoice_count} invoices</small>
                    </div>
                    <div class="text-end">
                        <div class="fw-bold">$${parseFloat(vendor.total_amount).toFixed(2)}</div>
                        <small class="text-muted">${vendor.status}</small>
                    </div>
                </div>
            `).join('');
        }

        async function loadPaymentReport() {
            try {
                const response = await fetch('<?= site_url('api/accounts-payable/reports/payment-history') ?>');
                if (response.ok) {
                    const data = await response.json();
                    displayPaymentReport(data);
                }
            } catch (error) {
                console.error('Error loading payment report:', error);
            }
        }

        function displayPaymentReport(data) {
            const tbody = document.querySelector('#paymentHistoryTable tbody');
            
            if (!data || data.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="7" class="text-center text-muted py-3">
                            No payment history available
                        </td>
                    </tr>
                `;
                return;
            }
            
            tbody.innerHTML = data.map(payment => {
                const methodClass = payment.payment_method === 'check' ? 'badge-primary' :
                                   payment.payment_method === 'bank_transfer' ? 'badge-info' :
                                   payment.payment_method === 'credit_card' ? 'badge-warning' : 'badge-secondary';
                
                const statusClass = payment.status === 'completed' ? 'badge-success' :
                                   payment.status === 'pending' ? 'badge-warning' : 'badge-danger';
                
                return `
                    <tr>
                        <td>${formatDate(payment.payment_date)}</td>
                        <td>${payment.vendor_name}</td>
                        <td>${payment.invoice_number}</td>
                        <td>
                            <span class="badge ${methodClass}">
                                ${payment.payment_method.toUpperCase().replace('_', ' ')}
                            </span>
                        </td>
                        <td>$${parseFloat(payment.amount).toFixed(2)}</td>
                        <td>${payment.reference_number || 'N/A'}</td>
                        <td>
                            <span class="badge ${statusClass}">
                                ${payment.status.toUpperCase()}
                            </span>
                        </td>
                    </tr>
                `;
            }).join('');
        }

        async function loadCashflowReport() {
            try {
                const response = await fetch('<?= site_url('api/accounts-payable/reports/cashflow') ?>');
                if (response.ok) {
                    const data = await response.json();
                    displayCashflowReport(data);
                }
            } catch (error) {
                console.error('Error loading cashflow report:', error);
            }
        }

        function displayCashflowReport(data) {
            // Create cash flow chart
            const ctx = document.getElementById('cashflowChart').getContext('2d');
            
            if (chartInstances.cashflow) chartInstances.cashflow.destroy();
            
            chartInstances.cashflow = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.months || [],
                    datasets: [
                        {
                            label: 'Projected Payments',
                            data: data.projected || [],
                            borderColor: '#FF5722',
                            backgroundColor: 'rgba(255, 87, 34, 0.1)',
                            fill: true,
                            tension: 0.4
                        },
                        {
                            label: 'Actual Payments',
                            data: data.actual || [],
                            borderColor: '#4CAF50',
                            backgroundColor: 'rgba(76, 175, 80, 0.1)',
                            fill: true,
                            tension: 0.4
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        title: {
                            display: true,
                            text: 'Cash Flow Projection'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '$' + value.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });
            
            // Update summary numbers
            document.getElementById('cashRequiredWeek').textContent = `$${data.cash_required_week?.toFixed(2) || '0.00'}`;
            document.getElementById('cashRequiredMonth').textContent = `$${data.cash_required_month?.toFixed(2) || '0.00'}`;
            document.getElementById('cashRequiredNextMonth').textContent = `$${data.cash_required_next_month?.toFixed(2) || '0.00'}`;
            
            // Display upcoming payments
            const upcomingPayments = document.getElementById('upcomingPayments');
            
            if (!data.upcoming_payments || data.upcoming_payments.length === 0) {
                upcomingPayments.innerHTML = '<p class="text-muted">No upcoming payments</p>';
                return;
            }
            
            upcomingPayments.innerHTML = data.upcoming_payments.map(payment => `
                <div class="d-flex justify-content-between align-items-center mb-2 p-2 border-bottom">
                    <div>
                        <div class="fw-bold">${payment.vendor_name}</div>
                        <small class="text-muted">Due: ${formatDate(payment.due_date)}</small>
                    </div>
                    <div class="text-end">
                        <div class="fw-bold">$${parseFloat(payment.amount).toFixed(2)}</div>
                        <small class="text-muted">${payment.invoice_number}</small>
                    </div>
                </div>
            `).join('');
        }

        function exportReport(format) {
            const reportType = document.getElementById('reportType').value;
            const dateRange = document.getElementById('dateRange').value;
            const dateFrom = document.getElementById('dateFrom').value;
            const dateTo = document.getElementById('dateTo').value;
            
            // Construct export URL
            const params = new URLSearchParams({
                type: reportType,
                format: format,
                date_range: dateRange,
                date_from: dateFrom,
                date_to: dateTo
            });
            
            window.open(`<?= site_url('api/accounts-payable/reports/export') ?>?${params}`, '_blank');
        }

        function printReport() {
            window.print();
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