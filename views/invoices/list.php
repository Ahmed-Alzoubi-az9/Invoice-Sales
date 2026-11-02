<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoices</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
</head>
<body>
    <div class="main-container list-container">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 page-header">
            <div>
                <h1 class="page-title mb-1">Invoices</h1>
                <p class="page-subtitle mb-0">Manage and track all your sales invoices</p>
            </div>
            <a href="index.php" class="btn btn-primary btn-shadow">
                <i class="fas fa-plus"></i> Create New Invoice
            </a>
        </div>

        <div id="alertContainer"></div>

        <div class="stats-bar" id="statsBar">
            <a class="stat-card" id="statTotal" data-filter="all" role="button">
                <div class="stat-icon stat-icon-blue">
                    <i class="fas fa-file-invoice"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-value" id="statTotalValue">0</span>
                    <span class="stat-label">Total Invoices</span>
                </div>
            </a>
            <a class="stat-card" id="statOutstanding" data-filter="unpaid" role="button">
                <div class="stat-icon stat-icon-yellow">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-value" id="statOutstandingValue">0.00</span>
                    <span class="stat-label">Outstanding</span>
                </div>
            </a>
            <a class="stat-card" id="statPaid" data-filter="paid" role="button">
                <div class="stat-icon stat-icon-green">
                    <i class="fas fa-circle-check"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-value" id="statPaidValue">0.00</span>
                    <span class="stat-label">Paid</span>
                </div>
            </a>
            <div class="stat-card">
                <div class="stat-icon stat-icon-purple">
                    <i class="fas fa-percent"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-value" id="statTaxValue">0.00</span>
                    <span class="stat-label">Tax Collected</span>
                </div>
            </div>
        </div>

        <div class="form-card list-card">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                <div class="d-flex align-items-center gap-2">
                    <button class="btn btn-outline-secondary btn-shadow" id="exportInvoicesBtn">
                        <i class="fas fa-file-export"></i> Export
                    </button>
                    <span class="result-counter" id="resultCounter"></span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <div class="search-box">
                        <i class="fas fa-search text-muted"></i>
                        <input type="search" class="form-control" id="invoiceSearch" placeholder="Search invoices...">
                    </div>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table invoice-table align-middle">
                    <thead>
                        <tr>
                            <th>Invoice #</th>
                            <th>Customer</th>
                            <th class="th-amount">Amount</th>
                            <th>Total Tax</th>
                            <th>Date</th>
                            <th>Due Date</th>
                            <th>Tags</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="invoiceTableBody">
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">No invoices yet</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
    <script src="app.js"></script>
    <script>
        // Initialize invoices list page
        document.addEventListener('DOMContentLoaded', function() {
            loadInvoices();

            // Search functionality
            const searchInput = document.getElementById('invoiceSearch');
            if (searchInput) {
                searchInput.addEventListener('input', () => {
                    const term = searchInput.value.trim().toLowerCase();
                    const filtered = InvoiceApp.invoicesCache.filter(inv => {
                        const haystack = [
                            inv.invoice_number,
                            inv.customer,
                            inv.invoice_date,
                            inv.due_date,
                            inv.tags,
                            inv.currency,
                            inv.status
                        ].join(' ').toLowerCase();
                        return haystack.includes(term);
                    });
                    renderInvoiceTable(filtered);
                });
            }

            // Stat card quick filters
            document.querySelectorAll('.stat-card[data-filter]').forEach(card => {
                card.addEventListener('click', () => {
                    const filter = card.dataset.filter;
                    document.querySelectorAll('.stat-card').forEach(c => c.classList.remove('active'));
                    card.classList.add('active');

                    let filtered = InvoiceApp.invoicesCache;
                    if (filter === 'unpaid') {
                        filtered = InvoiceApp.invoicesCache.filter(inv => inv.status !== 'paid');
                    } else if (filter === 'paid') {
                        filtered = InvoiceApp.invoicesCache.filter(inv => inv.status === 'paid');
                    }
                    renderInvoiceTable(filtered);
                });
            });

            // Export functionality
            const exportBtn = document.getElementById('exportInvoicesBtn');
            if (exportBtn) {
                exportBtn.addEventListener('click', () => {
                    if (!InvoiceApp.invoicesCache.length) {
                        InvoiceApp.showAlert('No invoices to export', 'info');
                        return;
                    }
                    const rows = [
                        ['Invoice #','Amount','Total Tax','Date','Customer','Tags','Due Date','Status']
                    ];
                    InvoiceApp.invoicesCache.forEach(inv => {
                        const total = `${inv.currency || ''}${Number(inv.total || 0).toFixed(2)}`;
                        const tax = `${inv.currency || ''}${Number(inv.total_tax || 0).toFixed(2)}`;
                        rows.push([
                            `INV-${inv.invoice_number}`,
                            total,
                            tax,
                            inv.invoice_date || '',
                            inv.customer || '',
                            inv.tags || '',
                            inv.due_date || '',
                            inv.status || 'unpaid'
                        ]);
                    });
                    const csv = rows.map(r => r.map(field => `"${String(field).replace(/"/g,'""')}"`).join(',')).join('\n');
                    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
                    const url = URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = 'invoices.csv';
                    a.click();
                    URL.revokeObjectURL(url);
                });
            }
        });
    </script>
</body>
</html>
