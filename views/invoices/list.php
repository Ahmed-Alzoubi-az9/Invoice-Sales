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
    <div class="main-container" style="max-width: 1100px; margin: 0 auto;">
        <div class="d-flex align-items-center flex-wrap gap-2 mb-3">
            <a href="index.php" class="btn btn-primary mt-3">
                <i class="fas fa-plus  "></i> Create New Invoice
            </a>
        </div>

        <div id="alertContainer"></div>

        <div class="form-card mx-auto" style="max-width: 1100px;">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                <div class="d-flex align-items-center gap-2">
                    <button class="btn btn-outline-secondary" id="exportInvoicesBtn">
                        <i class="fas fa-file-export"></i> Export
                    </button>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <div class="search-box">
                        <i class="fas fa-search text-muted"></i>
                        <input type="search" class="form-control" id="invoiceSearch" placeholder="Search invoices...">
                    </div>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Invoice #</th>
                            <th>Amount</th>
                            <th>Total Tax</th>
                            <th>Date</th>
                            <th>Customer</th>
                            <th>Tags</th>
                            <th>Due Date</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="invoiceTableBody">
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">No invoices yet</td>
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
                            inv.currency
                        ].join(' ').toLowerCase();
                        return haystack.includes(term);
                    });
                    renderInvoiceTable(filtered);
                });
            }
            
            // Export functionality
            const exportBtn = document.getElementById('exportInvoicesBtn');
            if (exportBtn) {
                exportBtn.addEventListener('click', () => {
                    if (!InvoiceApp.invoicesCache.length) {
                        InvoiceApp.showAlert('No invoices to export', 'info');
                        return;
                    }
                    const rows = [
                        ['Invoice #','Amount','Total Tax','Date','Customer','Tags','Due Date']
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
                            inv.due_date || ''
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

