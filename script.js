let itemCount = 1;
let addressModal;
let editingInvoiceNumber = null;
let invoicesCache = [];


function addItem(itemData = null) {
    const container = document.getElementById('itemsContainer');
    const row = document.createElement('tr');
    row.className = 'item-row';
    const qtyVal = itemData?.qty ?? (itemCount === 0 ? 1 : 1);
    const rateVal = itemData?.rate ?? 0;
    const taxVal = itemData?.tax ?? 15;
    const amountVal = itemData?.amount ?? 0;
    const nameVal = itemData?.item_name ?? '';
    const descVal = itemData?.description ?? '';
    row.innerHTML = `
        <td>
            <input type="text" class="item-input" name="items[${itemCount}][item_name]" placeholder="Description" value="${nameVal}">
        </td>
        <td>
            <textarea class="item-input" name="items[${itemCount}][description]" placeholder="Long description">${descVal}</textarea>
        </td>
        <td>
            <div class="qty-input-wrapper">
                <input type="number" class="item-input qty-input" name="items[${itemCount}][qty]" value="${qtyVal}" min="0" onchange="calculateRow(${itemCount})">
            </div>
            <div class="unit-display">Unit</div>
        </td>
        <td>
            <input type="number" class="item-input" name="items[${itemCount}][rate]" value="${rateVal}" onchange="calculateRow(${itemCount})">
        </td>
        <td>
            <input type="number" class="item-input" name="items[${itemCount}][tax]" value="${taxVal}" onchange="calculateRow(${itemCount})">
            <div class="unit-display">%</div>
        </td>
        <td>
            <input type="text" class="item-input" name="items[${itemCount}][amount]" value="${amountVal}" readonly>
        </td>
        <td class="text-center">
            <button type="button" class="btn-add-item" onclick="addItem()">
                <i class="fas fa-check"></i>
            </button>
        </td>
    `;
    container.appendChild(row);
    itemCount++;
}

/**
 * Remove an item row from the invoice
 * @param {HTMLElement} btn - The button element that triggered the removal
 */
function removeItem(btn) {
    btn.closest('tr').remove();
    calculateTotals();
}

/**
 * Calculate the amount for a specific item row
 * @param {number} index - The index of the item row
 */
function calculateRow(index) {
    const row = document.querySelector(`input[name="items[${index}][qty]"]`).closest('tr');
    const qty = parseFloat(row.querySelector(`input[name="items[${index}][qty]"]`).value) || 0;
    const rate = parseFloat(row.querySelector(`input[name="items[${index}][rate]"]`).value) || 0;
    const tax = parseFloat(row.querySelector(`input[name="items[${index}][tax]"]`).value) || 0;
    
    const subtotal = qty * rate;
    const taxAmount = subtotal * (tax / 100);
    const amount = subtotal + taxAmount;
    
    row.querySelector(`input[name="items[${index}][amount]"]`).value = amount.toFixed(2);
    calculateTotals();
}

/**
 * Calculate all totals including subtotal, discount, adjustment, and final total
 */
function calculateTotals() {
    const currency = document.getElementById('currency').value;
    const currencySymbol = getCurrencySymbol(currency);
    
    let subtotal = 0;
    let totalTax = 0;
    
    // Calculate subtotal and tax from all item rows
    document.querySelectorAll('.item-row').forEach((row, index) => {
        const qty = parseFloat(row.querySelector(`input[name^="items["][name$="][qty]"]`)?.value) || 0;
        const rate = parseFloat(row.querySelector(`input[name^="items["][name$="][rate]"]`)?.value) || 0;
        const tax = parseFloat(row.querySelector(`input[name^="items["][name$="][tax]"]`)?.value) || 0;
        
        const itemSubtotal = qty * rate;
        const itemTax = itemSubtotal * (tax / 100);
        
        subtotal += itemSubtotal;
        totalTax += itemTax;
    });
    
    // Calculate discount
    const discountValue = parseFloat(document.getElementById('discount').value) || 0;
    const discountUnit = document.getElementById('discountUnit').value;
    let discountAmount = 0;
    
    if (discountUnit === '%') {
        discountAmount = subtotal * (discountValue / 100);
    } else {
        discountAmount = discountValue;
    }
    
    // Get adjustment
    const adjustment = parseFloat(document.getElementById('adjustment').value) || 0;
    
    // Calculate final total
    const total = subtotal + totalTax - discountAmount + adjustment;
    
    // Update display
    document.getElementById('subTotal').textContent = currencySymbol + subtotal.toFixed(2);
    document.getElementById('discountAmount').textContent = '-' + currencySymbol + discountAmount.toFixed(2);
    document.getElementById('adjustmentAmount').textContent = currencySymbol + adjustment.toFixed(2);
    document.getElementById('finalTotal').textContent = currencySymbol + total.toFixed(2);
}

/**
 * Get the currency symbol based on currency code
 * @param {string} currency - Currency code (SAR, USD, JOD, EUR)
 * @returns {string} Currency symbol
 */
function getCurrencySymbol(currency) {
    const symbols = {
        'SAR': 'SR',
        'USD': '$',
        'JOD': 'JOD',
        'EUR': '€'
    };
    return symbols[currency] || currency;
}

/**
 * Show alert message to user
 * @param {string} message - Message to display
 * @param {string} type - Alert type (success, danger, info, warning)
 */
function showAlert(message, type = 'success') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.getElementById('alertContainer').appendChild(alertDiv);
    
    // Auto remove after 5 seconds
    setTimeout(() => alertDiv.remove(), 5000);
}

/**
 * Save invoice as draft
 */
function saveDraft() {
    showAlert('Draft saved successfully!', 'info');
}

/**
 * Collect form data and prepare for submission
 * @returns {object} Invoice data object
 */
function collectFormData() {
    const formData = new FormData(document.getElementById('invoiceForm'));
    const data = {
        invoice_number: formData.get('invoice_number'),
        customer: formData.get('customer'),
        tags: formData.get('tags'),
        bill_to: formData.get('bill_to'),
        ship_to: formData.get('ship_to'),
        invoice_date: formData.get('invoice_date'),
        due_date: formData.get('due_date'),
        currency: formData.get('currency'),
        payment_mode: formData.get('payment_mode'),
        sale_agent: formData.get('sale_agent'),
        discount_type: formData.get('discount_type'),
        recurring_invoice: formData.get('recurring_invoice'),
        admin_note: formData.get('admin_note'),
        items: []
    };
    
    // Collect items
    const itemRows = document.querySelectorAll('.item-row');
    itemRows.forEach((row, index) => {
        const item = {
            item_name: row.querySelector(`input[name^="items["][name$="][item_name]"]`)?.value || '',
            description: row.querySelector(`textarea[name^="items["][name$="][description]"]`)?.value || '',
            qty: parseFloat(row.querySelector(`input[name^="items["][name$="][qty]"]`)?.value) || 0,
            rate: parseFloat(row.querySelector(`input[name^="items["][name$="][rate]"]`)?.value) || 0,
            tax: parseFloat(row.querySelector(`input[name^="items["][name$="][tax]"]`)?.value) || 0,
            amount: parseFloat(row.querySelector(`input[name^="items["][name$="][amount]"]`)?.value) || 0
        };
        
        // Only add items with a name
        if (item.item_name) {
            data.items.push(item);
        }
    });
    
    // Calculate totals
    const subtotal = data.items.reduce((sum, item) => sum + (item.qty * item.rate), 0);
    const totalTax = data.items.reduce((sum, item) => {
        const itemSubtotal = item.qty * item.rate;
        return sum + (itemSubtotal * (item.tax / 100));
    }, 0);
    
    const discountValue = parseFloat(document.getElementById('discount').value) || 0;
    const discountUnit = document.getElementById('discountUnit').value;
    const discountAmount = discountUnit === '%' ? subtotal * (discountValue / 100) : discountValue;
    const adjustment = parseFloat(document.getElementById('adjustment').value) || 0;
    
    data.sub_total = subtotal;
    data.total_tax = totalTax;
    data.discount_value = discountAmount;
    data.adjustment = adjustment;
    data.total = subtotal + totalTax - discountAmount + adjustment;
    
    return data;
}

/**
 * Submit invoice form via AJAX
 * @param {Event} e - Form submit event
 */
async function submitInvoice(e) {
    e.preventDefault();
    
    const data = collectFormData();
    if (editingInvoiceNumber) {
        data.invoice_number = editingInvoiceNumber;
    }
    
    // Validate customer field
    if (!data.customer) {
        showAlert('Please enter a customer name', 'danger');
        return;
    }
    
    // Validate items
    if (data.items.length === 0) {
        showAlert('Please add at least one item', 'danger');
        return;
    }
    
    try {
        const response = await fetch('save_invoice.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            showAlert('Invoice saved! Invoice #' + result.invoice_number, 'success');
            editingInvoiceNumber = null;
            resetForm();
            loadInvoices();
        } else {
            showAlert('Error: ' + result.message, 'danger');
        }
    } catch (error) {
        showAlert('Error submitting invoice: ' + error.message, 'danger');
        console.error('Submission error:', error);
    }
}

function applyAddressFromModal(e) {
    e.preventDefault();

    const street = document.getElementById('addressStreet').value.trim();
    const city = document.getElementById('addressCity').value.trim();
    const state = document.getElementById('addressState').value.trim();
    const zip = document.getElementById('addressZip').value.trim();
    const country = document.getElementById('addressCountry').value.trim();

    if (!street || !city || !state || !zip || !country) {
        showAlert('Please complete all address fields', 'danger');
        return;
    }

    const cityStateZip = [city, state].filter(Boolean).join(', ') + (zip ? ` ${zip}` : '');
    const addressText = [street, cityStateZip, country].filter(Boolean).join('\n');

    // Always mirror the address into both areas so delivery/receiving match
    const billArea = document.getElementById('bill_to');
    const shipArea = document.getElementById('ship_to');
    [billArea, shipArea].forEach(area => {
        area.removeAttribute('disabled');
        area.value = addressText;
        area.dispatchEvent(new Event('input'));
    });

    if (addressModal) {
        addressModal.hide();
    }

    showAlert('Address added to Bill To and Ship To', 'success');
}

function resetForm() {
    document.getElementById('invoiceForm').reset();
    const billArea = document.getElementById('bill_to');
    const shipArea = document.getElementById('ship_to');
    billArea.value = '';
    shipArea.value = '';
    billArea.setAttribute('disabled', true);
    shipArea.setAttribute('disabled', true);

    // Reset pin button states
    const billPinBtn = document.getElementById('billPinBtn');
    const shipPinBtn = document.getElementById('shipPinBtn');
    if (billPinBtn) {
        billPinBtn.innerHTML = '<i class="fas fa-thumbtack"></i>';
        billPinBtn.title = 'Enable manual editing';
        billPinBtn.classList.remove('btn-outline-success');
        billPinBtn.classList.add('btn-outline-secondary');
    }
    if (shipPinBtn) {
        shipPinBtn.innerHTML = '<i class="fas fa-thumbtack"></i>';
        shipPinBtn.title = 'Enable manual editing';
        shipPinBtn.classList.remove('btn-outline-success');
        shipPinBtn.classList.add('btn-outline-secondary');
    }

    document.getElementById('discount').value = 0;
    document.getElementById('adjustment').value = 0;
    document.getElementById('discountUnit').value = '%';
    const container = document.getElementById('itemsContainer');
    container.innerHTML = '';
    itemCount = 0;
    addItem({ qty: 1, rate: 0, tax: 15, amount: 0 });
    calculateTotals();
}

async function loadInvoices() {
    try {
        const res = await fetch('list_invoices.php');
        const data = await res.json();
        if (!data.success) {
            showAlert('Could not load invoices: ' + data.message, 'danger');
            return;
        }
        invoicesCache = data.invoices || [];
        renderInvoiceTable(invoicesCache);
    } catch (err) {
        showAlert('Error loading invoices: ' + err.message, 'danger');
    }
}

function renderInvoiceTable(invoices) {
    const body = document.getElementById('invoiceTableBody');
    body.innerHTML = '';
    if (!invoices.length) {
        body.innerHTML = '<tr><td colspan="8" class="text-center text-muted py-4">No invoices yet</td></tr>';
        return;
    }

    invoices.forEach(inv => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>INV-${inv.invoice_number}</td>
            <td>${inv.invoice_date || ''}</td>
            <td>${inv.customer || ''}</td>
            <td>${inv.tags || ''}</td>
            <td>${inv.currency || ''}${Number(inv.total || 0).toFixed(2)}</td>
            <td>${inv.currency || ''}${Number(inv.total_tax || 0).toFixed(2)}</td>
            <td>${inv.due_date || ''}</td>
            <td class="text-end">
                <button class="btn btn-sm btn-outline-primary me-1" onclick="viewInvoice(${inv.invoice_number})"><i class="fas fa-file-pdf"></i></button>
                <button class="btn btn-sm btn-outline-secondary me-1" onclick="editInvoice(${inv.invoice_number})"><i class="fas fa-pen"></i></button>
                <button class="btn btn-sm btn-outline-danger" onclick="deleteInvoice(${inv.invoice_number})"><i class="fas fa-trash"></i></button>
            </td>
        `;
        body.appendChild(tr);
    });
}

function viewInvoice(invoiceNumber) {
    window.open(`invoice_pdf.php?invoice_number=${invoiceNumber}`, '_blank');
}

async function editInvoice(invoiceNumber) {
    const invoiceForm = document.getElementById('invoiceForm');
    if (!invoiceForm) {
        window.location.href = `index.php?invoice_number=${invoiceNumber}`;
        return;
    }
    try {
        const res = await fetch(`get_invoice.php?invoice_number=${invoiceNumber}`);
        const data = await res.json();
        if (!data.success) {
            showAlert('Could not load invoice: ' + data.message, 'danger');
            return;
        }
        const inv = data.invoice;
        editingInvoiceNumber = inv.invoice_number;
        document.getElementById('invoice_number').value = inv.invoice_number;
        document.getElementById('customer').value = inv.customer || '';
        const tagsInput = document.getElementById('tags');
        if (tagsInput) {
            tagsInput.value = inv.tags || '';
        }
        const billArea = document.getElementById('bill_to');
        const shipArea = document.getElementById('ship_to');
        billArea.value = inv.bill_to || '';
        shipArea.value = inv.ship_to || '';
        billArea.setAttribute('disabled', true);
        shipArea.setAttribute('disabled', true);

        // Reset pin button states for editing
        const billPinBtn = document.getElementById('billPinBtn');
        const shipPinBtn = document.getElementById('shipPinBtn');
        if (billPinBtn) {
            billPinBtn.innerHTML = '<i class="fas fa-thumbtack"></i>';
            billPinBtn.title = 'Enable manual editing';
            billPinBtn.classList.remove('btn-outline-success');
            billPinBtn.classList.add('btn-outline-secondary');
        }
        if (shipPinBtn) {
            shipPinBtn.innerHTML = '<i class="fas fa-thumbtack"></i>';
            shipPinBtn.title = 'Enable manual editing';
            shipPinBtn.classList.remove('btn-outline-success');
            shipPinBtn.classList.add('btn-outline-secondary');
        }
        document.getElementById('invoice_date').value = inv.invoice_date || '';
        document.getElementById('due_date').value = inv.due_date || '';
        document.getElementById('currency').value = inv.currency || 'SAR';
        document.getElementById('payment_mode').value = inv.payment_mode || 'Bank';
        document.getElementById('sale_agent').value = inv.sale_agent || '';
        document.getElementById('discount_type').value = inv.discount_type || 'Before Tax';
        document.getElementById('recurring_invoice').value = inv.recurring_invoice || 'No';
        document.getElementById('admin_note').value = inv.admin_note || '';
        document.getElementById('discount').value = inv.discount_value || 0;
        document.getElementById('adjustment').value = inv.adjustment || 0;

        // Items
        const container = document.getElementById('itemsContainer');
        container.innerHTML = '';
        itemCount = 0;
        (data.items || []).forEach(item => {
            addItem(item);
        });
        if ((data.items || []).length === 0) {
            addItem({ qty: 1, rate: 0, tax: 15, amount: 0 });
        }
        calculateTotals();
        showAlert('Invoice loaded. Edit and submit to save.', 'info');
    } catch (err) {
        showAlert('Error loading invoice: ' + err.message, 'danger');
    }
}

async function deleteInvoice(invoiceNumber) {
    if (!confirm('Delete invoice #' + invoiceNumber + '?')) return;
    try {
        const res = await fetch('delete_invoice.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ invoice_number: invoiceNumber })
        });
        const data = await res.json();
        if (!data.success) {
            showAlert('Delete failed: ' + data.message, 'danger');
            return;
        }
        showAlert('Invoice deleted', 'success');
        loadInvoices();
    } catch (err) {
        showAlert('Error deleting invoice: ' + err.message, 'danger');
    }
}

// Load products into dropdown
async function loadPredefinedItems() {
    try {
        const response = await fetch('api.php?products');
        const data = await response.json();
        if (data.products && Array.isArray(data.products)) {
            const dropdown = document.getElementById('itemDropdown');
            if (dropdown) {
                dropdown.innerHTML = '<option value="">Select Item</option>';
                data.products.forEach(item => {
                    const option = document.createElement('option');
                    option.value = item.id;
                    option.textContent = item.item_name;
                    option.dataset.item = JSON.stringify(item);
                    dropdown.appendChild(option);
                });
            }
        }
    } catch (error) {
        console.error('Error loading products:', error);
    }
}

// Load next invoice number
async function loadNextInvoiceNumber() {
    try {
        const response = await fetch('api.php?next');
        const data = await response.json();
        if (data.next_invoice_number) {
            const invoiceInput = document.getElementById('invoice_number');
            if (invoiceInput) {
                invoiceInput.value = data.next_invoice_number;
            }
        }
    } catch (error) {
        console.error('Error loading next invoice number:', error);
    }
}

// Fill item row when dropdown changes
function fillItemRow(index) {
    const dropdown = document.getElementById('itemDropdown');
    const selectedOption = dropdown.options[dropdown.selectedIndex];
    if (selectedOption.value && selectedOption.dataset.item) {
        const item = JSON.parse(selectedOption.dataset.item);

        // Find the first item row (index 0)
        const firstRow = document.querySelector('.item-row');
        if (firstRow) {
            // Fill the item details
            const nameInput = firstRow.querySelector('input[name="items[0][item_name]"]');
            const descTextarea = firstRow.querySelector('textarea[name="items[0][description]"]');
            const qtyInput = firstRow.querySelector('input[name="items[0][qty]"]');
            const rateInput = firstRow.querySelector('input[name="items[0][rate]"]');
            const taxInput = firstRow.querySelector('input[name="items[0][tax]"]');

            if (nameInput) nameInput.value = item.item_name || '';
            if (descTextarea) descTextarea.value = item.description || '';
            if (qtyInput) qtyInput.value = 1; // Default qty is 1 for new items
            if (rateInput) rateInput.value = item.rate || 0;
            if (taxInput) taxInput.value = item.tax || 15;

            // Calculate the row
            calculateRow(0);

            // Reset dropdown
            dropdown.value = '';
        }
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    const invoiceForm = document.getElementById('invoiceForm');
    const invoiceTableBody = document.getElementById('invoiceTableBody');
    const urlParams = new URLSearchParams(window.location.search);
    const invoiceParam = urlParams.get('invoice_number');

    // Attach form submit handler if on the invoice form page
    if (invoiceForm) {
        invoiceForm.addEventListener('submit', submitInvoice);
        const currencySelect = document.getElementById('currency');
        if (currencySelect) {
            currencySelect.addEventListener('change', calculateTotals);
        }
        // Initialize calculations for the first row
        calculateRow(0);
        if (invoiceParam) {
            editInvoice(invoiceParam);
        } else {
            // Load next invoice number and predefined items for new invoice
            loadNextInvoiceNumber();
            loadPredefinedItems();
        }
    }

    // Address modal handlers
    const addressModalEl = document.getElementById('addressModal');
    const billBtn = document.getElementById('billAddressBtn');
    const shipBtn = document.getElementById('shipAddressBtn');
    const addressForm = document.getElementById('addressForm');

    if (addressModalEl) {
        addressModal = new bootstrap.Modal(addressModalEl);
    }

    if (billBtn) {
        billBtn.addEventListener('click', () => {
            addressModal?.show();
        });
    }

    if (shipBtn) {
        shipBtn.addEventListener('click', () => {
            addressModal?.show();
        });
    }

    if (addressForm) {
        addressForm.addEventListener('submit', applyAddressFromModal);
    }

    // Pin buttons to toggle manual editing
    const billPinBtn = document.getElementById('billPinBtn');
    const shipPinBtn = document.getElementById('shipPinBtn');

    if (billPinBtn) {
        billPinBtn.addEventListener('click', () => {
            const area = document.getElementById('bill_to');
            const isDisabled = area.hasAttribute('disabled');
            if (isDisabled) {
                area.removeAttribute('disabled');
                area.focus();
                billPinBtn.innerHTML = '<i class="fas fa-lock-open"></i>';
                billPinBtn.title = 'Disable manual editing';
                billPinBtn.classList.remove('btn-outline-secondary');
                billPinBtn.classList.add('btn-outline-success');
            } else {
                area.setAttribute('disabled', 'disabled');
                billPinBtn.innerHTML = '<i class="fas fa-thumbtack"></i>';
                billPinBtn.title = 'Enable manual editing';
                billPinBtn.classList.remove('btn-outline-success');
                billPinBtn.classList.add('btn-outline-secondary');
            }
        });
    }

    if (shipPinBtn) {
        shipPinBtn.addEventListener('click', () => {
            const area = document.getElementById('ship_to');
            const isDisabled = area.hasAttribute('disabled');
            if (isDisabled) {
                area.removeAttribute('disabled');
                area.focus();
                shipPinBtn.innerHTML = '<i class="fas fa-lock-open"></i>';
                shipPinBtn.title = 'Disable manual editing';
                shipPinBtn.classList.remove('btn-outline-secondary');
                shipPinBtn.classList.add('btn-outline-success');
            } else {
                area.setAttribute('disabled', 'disabled');
                shipPinBtn.innerHTML = '<i class="fas fa-thumbtack"></i>';
                shipPinBtn.title = 'Enable manual editing';
                shipPinBtn.classList.remove('btn-outline-success');
                shipPinBtn.classList.add('btn-outline-secondary');
            }
        });
    }

    const searchInput = document.getElementById('invoiceSearch');
    if (searchInput) {
        searchInput.addEventListener('input', () => {
            const term = searchInput.value.trim().toLowerCase();
            const filtered = invoicesCache.filter(inv => {
                const haystack = [
                    inv.invoice_number,
                    inv.customer,
                    inv.tags,
                    inv.invoice_date,
                    inv.due_date
                ].join(' ').toLowerCase();
                return haystack.includes(term);
            });
            renderInvoiceTable(filtered);
        });
    }

    const exportBtn = document.getElementById('exportInvoicesBtn');
    if (exportBtn) {
        exportBtn.addEventListener('click', () => {
            if (!invoicesCache.length) {
                showAlert('No invoices to export', 'info');
                return;
            }
            const rows = [
                ['Invoice #','Date','Customer','Tags','Amount','Total Tax','Due Date']
            ];
            invoicesCache.forEach(inv => {
                const total = `${inv.currency || ''}${Number(inv.total || 0).toFixed(2)}`;
                const totalTax = `${inv.currency || ''}${Number(inv.total_tax || 0).toFixed(2)}`;
                rows.push([
                    `INV-${inv.invoice_number}`,
                    inv.invoice_date || '',
                    inv.customer || '',
                    inv.tags || '',
                    total,
                    totalTax,
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

    if (invoiceTableBody) {
        loadInvoices();
    }
});
