<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create New Invoice</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
</head>

<body>
    <div class="main-container">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h1 class="page-title">Create New Invoice</h1>
            <a href="invoices.html" class="btn btn-outline-secondary mb-2">
                <i class="fas fa-list"></i> View Invoices
            </a>
        </div>

        <div id="alertContainer"></div>

        <form id="invoiceForm">
            <!-- Invoice Details Card -->
            <div class="form-card">
                <div class="row mb-4">
                    <!-- LEFT COLUMN -->
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label required">Customer</label>
                            <input type="text" class="form-control" id="customer" name="customer" placeholder="Select and begin typing" required>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Bill To</label>
                                <div class="address-box">
                                    <div class="address-actions">
                                        <button type="button" class="btn btn-outline-primary icon-square" id="billAddressBtn" title="Fill Bill To (modal)">
                                            <i class="fas fa-pen-to-square"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary icon-square" id="billPinBtn" title="Enable manual editing">
                                            <i class="fas fa-thumbtack"></i>
                                        </button>
                                    </div>
                                    <textarea class="form-control address-textarea" id="bill_to" name="bill_to" rows="4" placeholder="--" disabled></textarea>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Ship To</label>
                                <div class="address-box">
                                    <div class="address-actions">
                                        <button type="button" class="btn btn-outline-primary icon-square" id="shipAddressBtn" title="Fill Ship To (modal)">
                                            <i class="fas fa-pen-to-square"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary icon-square" id="shipPinBtn" title="Enable manual editing">
                                            <i class="fas fa-thumbtack"></i>
                                        </button>
                                    </div>
                                    <textarea class="form-control address-textarea" id="ship_to" name="ship_to" rows="4" placeholder="--" disabled></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label required">Invoice Number</label>
                            <div class="invoice-number-group">
                                <span class="invoice-prefix">INV-</span>
                                <input type="text" class="form-control" id="invoice_number" name="invoice_number" readonly>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label required">Invoice Date</label>
                                <input type="date" class="form-control" id="invoice_date" name="invoice_date" value="2025-12-10" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Due Date</label>
                                <input type="date" class="form-control" id="due_date" name="due_date" value="2026-01-09">
                            </div>
                        </div>
                    </div>

                    <!-- RIGHT COLUMN -->
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label"><i class="fas fa-tag"></i> Tags</label>
                            <input type="text" class="form-control" id="tags" placeholder="Tag">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Allowed payment modes for this invoice</label>
                            <select class="form-select" id="payment_mode" name="payment_mode">
                                <option value="Bank" selected>Bank</option>
                                <option value="Cash">Cash</option>
                                <option value="Visa">Visa</option>
                            </select>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label required">Currency</label>
                                <select class="form-select" id="currency" name="currency" required>
                                    <option value="SAR" selected>SAR</option>
                                    <option value="USD">USD</option>
                                    <option value="JOD">JOD</option>
                                    <option value="EUR">EUR</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Sale Agent</label>
                                <select class="form-select" id="sale_agent" name="sale_agent">
                                    <option value="ahmed alzoubi" selected>ahmed alzoubi</option>
                                    <option value="Abdullah Alokour">Abdullah Alokour</option>
                                    <option value="Ayman Adel">Ayman Adel</option>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Recurring Invoice?</label>
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
                            <div class="col-md-6">
                                <label class="form-label">Discount Type</label>
                                <select class="form-select" id="discount_type" name="discount_type">
                                    <option value="No discount" selected>No discount</option>
                                    <option value="Before Tax">Before Tax</option>
                                    <option value="After Tax">After Tax</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Admin Note</label>
                            <textarea class="form-control" id="admin_note" name="admin_note" rows="4"></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Items Card -->
            <div class="items-card">
                <div class="d-flex justify-content-end align-items-center mb-3">
                    <select id="itemDropdown" class="form-select item-dropdown">
                        <option value="">Select Item</option>
                    </select>
                </div>

                <div class="items-table">
                    <table class="table">
                        <thead class="table-header">
                            <tr>
                                <th style="width: 15%;">Item</th>
                                <th style="width: 25%;">Description</th>
                                <th style="width: 10%;">Qty</th>
                                <th style="width: 12%;">Rate</th>
                                <th style="width: 12%;">Tax</th>
                                <th style="width: 12%;">Amount</th>
                                <th style="width: 5%;"></th>
                            </tr>
                        </thead>
                        <tbody id="itemsContainer">
                        </tbody>
                    </table>
                </div>

                <!-- Totals Section -->
                <div class="totals-section">
                    <div class="total-row">
                        <span class="total-label">Sub Total :</span>
                        <span class="total-value" id="subTotal">SR0.00</span>
                    </div>
                    <div class="total-row">
                        <span class="total-label">Discount</span>
                        <input type="number" class="form-control total-input" id="discount" value="0" min="0" onchange="calculateTotals()">
                        <select class="form-select" style="width: 80px;" id="discountUnit" onchange="calculateTotals()">
                            <option value="%">%</option>
                            <option value="fixed">Fixed</option>
                        </select>
                        <span class="total-value" id="discountAmount">-SR0.00</span>
                    </div>
                    <div class="total-row">
                        <span class="total-label">Adjustment</span>
                        <input type="number" class="form-control total-input" id="adjustment" value="0" onchange="calculateTotals()">
                        <span class="total-value" id="adjustmentAmount">SR0.00</span>
                    </div>
                    <div class="total-row final-total">
                        <span class="total-label">Total :</span>
                        <span class="total-value" id="finalTotal">SR0.00</span>
                    </div>
                </div>

                <div class="action-buttons">
                    <button type="button" class="btn-draft" onclick="saveDraft()">Save as Draft</button>
                    <button type="submit" class="btn-submit">Submit</button>
                </div>
            </div>
        </form>
    </div>

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