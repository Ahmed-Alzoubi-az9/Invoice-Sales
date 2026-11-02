<!DOCTYPE html>
<html lang="en" class="invoice-create-document">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create New Invoice</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
</head>

<body>
    <main class="main-container invoice-create-page">
        <header class="invoice-page-header">
            <h1 class="page-title mb-0">Create New Invoice</h1>
            <a href="invoices.php" class="btn btn-outline-secondary invoice-list-link">
                <i class="fas fa-list"></i> <span>View invoices</span>
            </a>
        </header>

        <div id="alertContainer"></div>

        <form id="invoiceForm" class="invoice-create-form">
            <section class="create-card invoice-parties-card" aria-labelledby="invoiceDetailsHeading">
                <div class="create-card-heading">
                    <h2 id="invoiceDetailsHeading">Invoice details</h2>
                </div>

                <div class="invoice-parties-grid">
                    <div class="customer-panel">
                        <label class="form-label required" for="customer">Customer</label>
                        <input type="text" class="form-control" id="customer" name="customer" placeholder="Enter customer name" autocomplete="organization" required>

                        <div class="address-grid">
                            <div class="address-card">
                                <div class="address-card-heading">
                                    <label class="form-label mb-0" for="bill_to">Bill to</label>
                                    <div class="address-actions">
                                        <button type="button" class="address-icon-btn" id="billAddressBtn" title="Fill billing address" aria-label="Fill billing address">
                                            <i class="fas fa-pen-to-square"></i>
                                        </button>
                                        <button type="button" class="address-icon-btn" id="billPinBtn" title="Enable manual editing" aria-label="Enable manual editing for billing address">
                                            <i class="fas fa-thumbtack"></i>
                                        </button>
                                    </div>
                                </div>
                                <textarea class="form-control address-textarea" id="bill_to" name="bill_to" rows="3" placeholder="Billing address" disabled></textarea>
                            </div>
                            <div class="address-card">
                                <div class="address-card-heading">
                                    <label class="form-label mb-0" for="ship_to">Ship to</label>
                                    <div class="address-actions">
                                        <button type="button" class="address-icon-btn" id="shipAddressBtn" title="Fill shipping address" aria-label="Fill shipping address">
                                            <i class="fas fa-pen-to-square"></i>
                                        </button>
                                        <button type="button" class="address-icon-btn" id="shipPinBtn" title="Enable manual editing" aria-label="Enable manual editing for shipping address">
                                            <i class="fas fa-thumbtack"></i>
                                        </button>
                                    </div>
                                </div>
                                <textarea class="form-control address-textarea" id="ship_to" name="ship_to" rows="3" placeholder="Shipping address" disabled></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="invoice-meta-panel">
                        <div class="invoice-meta-fields">
                            <div class="form-field invoice-number-field">
                                <label class="form-label required" for="invoice_number">Invoice number</label>
                                <div class="invoice-number-group">
                                    <span class="invoice-prefix">INV-</span>
                                    <input type="text" class="form-control" id="invoice_number" name="invoice_number" readonly>
                                </div>
                            </div>
                            <div class="form-field">
                                <label class="form-label required" for="invoice_date">Invoice date</label>
                                <input type="date" class="form-control" id="invoice_date" name="invoice_date" value="2025-12-10" required>
                            </div>
                            <div class="form-field">
                                <label class="form-label" for="due_date">Due date</label>
                                <input type="date" class="form-control" id="due_date" name="due_date" value="2026-01-09">
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="create-card invoice-settings-card" aria-labelledby="invoiceSettingsHeading">
                <div class="create-card-heading">
                    <h2 id="invoiceSettingsHeading">Payment</h2>
                </div>

                <div class="primary-payment-grid">
                    <div class="form-field">
                        <label class="form-label" for="payment_mode">Payment mode</label>
                        <select class="form-select" id="payment_mode" name="payment_mode">
                            <option value="Bank" selected>Bank</option>
                            <option value="Cash">Cash</option>
                            <option value="Visa">Visa</option>
                        </select>
                    </div>
                    <div class="form-field">
                        <label class="form-label required" for="currency">Currency</label>
                        <select class="form-select" id="currency" name="currency" required>
                            <option value="SAR" selected>SAR</option>
                            <option value="USD">USD</option>
                            <option value="JOD">JOD</option>
                            <option value="EUR">EUR</option>
                        </select>
                    </div>
                </div>

                <details class="invoice-more-options">
                    <summary>More options</summary>
                    <div class="settings-grid">
                        <div class="form-field">
                            <label class="form-label" for="sale_agent">Sales agent</label>
                            <select class="form-select" id="sale_agent" name="sale_agent">
                                <option value="ahmed alzoubi" selected>ahmed alzoubi</option>
                                <option value="Abdullah Alokour">Abdullah Alokour</option>
                                <option value="Ayman Adel">Ayman Adel</option>
                            </select>
                        </div>
                        <div class="form-field">
                            <label class="form-label" for="recurring_invoice">Recurring invoice</label>
                            <select class="form-select" id="recurring_invoice" name="recurring_invoice">
                                <option value="No" selected>No</option>
                                <option value="Every 1 month">Every 1 month</option>
                                <option value="Every 2 months">Every 2 months</option>
                                <option value="Every 3 months">Every 3 months</option>
                                <option value="Every 4 months">Every 4 months</option>
                                <option value="Every 5 months">Every 5 months</option>
                                <option value="Every 6 months">Every 6 months</option>
                                <option value="Every 7 months">Every 7 months</option>
                                <option value="Every 8 months">Every 8 months</option>
                                <option value="Every 9 months">Every 9 months</option>
                                <option value="Every 10 months">Every 10 months</option>
                                <option value="Every 11 months">Every 11 months</option>
                                <option value="Every 12 months">Every 12 months</option>
                            </select>
                        </div>
                        <div class="form-field">
                            <label class="form-label" for="discount_type">Discount type</label>
                            <select class="form-select" id="discount_type" name="discount_type">
                                <option value="No discount" selected>No discount</option>
                                <option value="Before Tax">Before Tax</option>
                                <option value="After Tax">After Tax</option>
                            </select>
                        </div>
                        <div class="form-field">
                            <label class="form-label" for="tags">Tags</label>
                            <input type="text" class="form-control" id="tags" name="tags" placeholder="e.g. project, priority">
                        </div>
                        <div class="form-field settings-note-field">
                            <label class="form-label" for="admin_note">Internal note</label>
                            <textarea class="form-control" id="admin_note" name="admin_note" rows="2" placeholder="Add a note for your team (not shown to the customer)"></textarea>
                        </div>
                    </div>
                </details>
            </section>

            <section class="create-card invoice-items-card" aria-labelledby="invoiceItemsHeading">
                <div class="items-toolbar">
                    <div class="create-card-heading mb-0">
                        <h2 id="invoiceItemsHeading">Items</h2>
                    </div>
                    <div class="quick-add-field">
                        <select id="itemDropdown" class="form-select item-dropdown" aria-label="Add a saved product">
                            <option value="">Add saved product</option>
                        </select>
                    </div>
                </div>

                <div class="invoice-items-scroll">
                    <table class="table invoice-items-table">
                        <colgroup>
                            <col class="item-name-column">
                            <col class="item-description-column">
                            <col class="item-qty-column">
                            <col class="item-rate-column">
                            <col class="item-tax-column">
                            <col class="item-amount-column">
                            <col class="item-remove-column">
                        </colgroup>
                        <thead>
                            <tr>
                                <th scope="col">Item</th>
                                <th scope="col">Description</th>
                                <th scope="col" class="text-center">Qty</th>
                                <th scope="col" class="text-end">Rate</th>
                                <th scope="col" class="text-center">Tax %</th>
                                <th scope="col" class="text-end">Amount</th>
                                <th scope="col"><span class="visually-hidden">Remove</span></th>
                            </tr>
                        </thead>
                        <tbody id="itemsContainer"></tbody>
                    </table>
                </div>

                <div class="invoice-items-footer">
                    <button type="button" class="btn-add-item" onclick="addItem(null)">
                        <i class="fas fa-plus"></i> Add item
                    </button>

                    <div class="totals-panel" aria-label="Invoice totals">
                        <div class="total-row subtotal-row">
                            <span class="total-label">Subtotal</span>
                            <span class="total-value" id="subTotal">SR0.00</span>
                        </div>
                        <div class="total-row">
                            <label class="total-label" for="discount">Discount</label>
                            <div class="total-controls">
                                <input type="number" class="form-control" id="discount" value="0" min="0" onchange="calculateTotals()">
                                <select class="form-select" id="discountUnit" aria-label="Discount unit" onchange="calculateTotals()">
                                    <option value="%">%</option>
                                    <option value="fixed">Fixed</option>
                                </select>
                            </div>
                            <span class="total-value" id="discountAmount">-SR0.00</span>
                        </div>
                        <div class="total-row">
                            <label class="total-label" for="adjustment">Adjustment</label>
                            <div class="total-controls">
                                <input type="number" class="form-control" id="adjustment" value="0" aria-label="Adjustment amount" onchange="calculateTotals()">
                            </div>
                            <span class="total-value" id="adjustmentAmount">SR0.00</span>
                        </div>
                        <div class="total-row final-total">
                            <span class="total-label">Total due</span>
                            <span class="total-value" id="finalTotal">SR0.00</span>
                        </div>
                    </div>
                </div>

                <footer class="invoice-form-footer">
                    <div class="action-buttons">
                        <button type="button" class="btn-draft" onclick="saveDraft(event)">
                            <i class="fas fa-floppy-disk"></i> Save as draft
                        </button>
                        <button type="submit" class="btn-submit">
                            <i class="fas fa-paper-plane"></i> Create invoice
                        </button>
                    </div>
                </footer>
            </section>
        </form>
    </main>

    <!-- Address Modal -->
    <div class="modal fade" id="addressModal" tabindex="-1" aria-labelledby="addressModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addressModalLabel">Add Address</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="addressStreet" class="form-label">Street</label>
                        <input type="text" class="form-control" id="addressStreet">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="addressCity" class="form-label">City</label>
                            <input type="text" class="form-control" id="addressCity">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="addressState" class="form-label">State</label>
                            <input type="text" class="form-control" id="addressState">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="addressZip" class="form-label">Zip Code</label>
                            <input type="text" class="form-control" id="addressZip">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="addressCountry" class="form-label">Country</label>
                            <input type="text" class="form-control" id="addressCountry">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="applyAddressBtn">Apply Address</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
    <script src="app.js"></script>
</body>

</html>
