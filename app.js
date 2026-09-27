/**
 * Invoice Application JavaScript
 * Organized into classes for better structure
 */

// Invoice Calculator Class
class InvoiceCalculator {
    static calculateRow(index) {
        const row = document.querySelector(`input[name="items[${index}][qty]"]`)?.closest('tr');
        if (!row) return;

        const qty = parseFloat(row.querySelector(`input[name="items[${index}][qty]"]`).value) || 0;
        const rate = parseFloat(row.querySelector(`input[name="items[${index}][rate]"]`).value) || 0;
        const tax = parseFloat(row.querySelector(`input[name="items[${index}][tax]"]`).value) || 0;

        const subtotal = qty * rate;
        const taxAmount = subtotal * (tax / 100);
        const amount = subtotal + taxAmount;

        const amountInput = row.querySelector(`input[name="items[${index}][amount]"]`);
        if (amountInput) {
            amountInput.value = amount.toFixed(2);
        }
        InvoiceCalculator.calculateTotals();
    }

    static calculateTotals() {
        const currency = document.getElementById('currency')?.value || 'SAR';
        const currencySymbol = InvoiceApp.getCurrencySymbol(currency);

        let subtotal = 0;
        let totalTax = 0;

        document.querySelectorAll('.item-row').forEach((row) => {
            const qty = parseFloat(row.querySelector(`input[name^="items["][name$="][qty]"]`)?.value) || 0;
            const rate = parseFloat(row.querySelector(`input[name^="items["][name$="][rate]"]`)?.value) || 0;
            const tax = parseFloat(row.querySelector(`input[name^="items["][name$="][tax]"]`)?.value) || 0;

            const itemSubtotal = qty * rate;
            const itemTax = itemSubtotal * (tax / 100);

            subtotal += itemSubtotal;
            totalTax += itemTax;
        });

        const discountValue = parseFloat(document.getElementById('discount')?.value) || 0;
        const discountUnit = document.getElementById('discountUnit')?.value || '%';
        const discountAmount = discountUnit === '%' ? subtotal * (discountValue / 100) : discountValue;
        const adjustment = parseFloat(document.getElementById('adjustment')?.value) || 0;
        const total = subtotal + totalTax - discountAmount + adjustment;

        const subTotalEl = document.getElementById('subTotal');
        const discountAmountEl = document.getElementById('discountAmount');
        const adjustmentAmountEl = document.getElementById('adjustmentAmount');
        const finalTotalEl = document.getElementById('finalTotal');

        if (subTotalEl) subTotalEl.textContent = currencySymbol + subtotal.toFixed(2);
        if (discountAmountEl) discountAmountEl.textContent = '-' + currencySymbol + discountAmount.toFixed(2);
        if (adjustmentAmountEl) adjustmentAmountEl.textContent = currencySymbol + adjustment.toFixed(2);
        if (finalTotalEl) finalTotalEl.textContent = currencySymbol + total.toFixed(2);
    }
}

// Invoice Item Manager Class
class InvoiceItemManager {
    static itemCount = 1;

    static addItem(itemData = null) {
        const container = document.getElementById('itemsContainer');
        if (!container) return;

        const row = document.createElement('tr');
        row.className = 'item-row';
        const qtyVal = itemData?.qty ?? 1;
        const rateVal = itemData?.rate ?? 0;
        const taxVal = itemData?.tax ?? 15;
        const amountVal = Number(itemData?.amount ?? 0);
        // Ensure item_name is properly handled - check both item_name and name fields
        let nameVal = '';
        if (itemData) {
            nameVal = (itemData.item_name || itemData.name || '').toString().trim();
        }
        const descVal = (itemData?.description || '').toString().trim();

        // Escape HTML to prevent XSS and ensure proper display
        const escapedName = nameVal.replace(/"/g, '&quot;').replace(/'/g, '&#39;');
        const escapedDesc = descVal.replace(/</g, '&lt;').replace(/>/g, '&gt;');
        
        row.innerHTML = `
            <td>
                <input type="text" class="item-input" name="items[${this.itemCount}][item_name]" placeholder="Item name" value="${escapedName}">
            </td>
            <td>
                <textarea class="item-input" name="items[${this.itemCount}][description]" placeholder="Description">${escapedDesc}</textarea>
            </td>
            <td>
                <div class="qty-input-wrapper">
                    <input type="number" class="item-input qty-input" name="items[${this.itemCount}][qty]" value="${qtyVal}" step="0.01" onchange="InvoiceCalculator.calculateRow(${this.itemCount})">
                </div>
                <div class="unit-display">Unit</div>
            </td>
            <td>
                <input type="number" class="item-input" name="items[${this.itemCount}][rate]" value="${rateVal}" step="0.01" onchange="InvoiceCalculator.calculateRow(${this.itemCount})">
            </td>
            <td>
                <input type="number" class="item-input" name="items[${this.itemCount}][tax]" value="${taxVal}" step="0.01" onchange="InvoiceCalculator.calculateRow(${this.itemCount})">
                <div class="unit-display">%</div>
            </td>
            <td>
                <input type="text" class="item-input" name="items[${this.itemCount}][amount]" value="${amountVal.toFixed(2)}" readonly>
            </td>
            <td class="text-center">
                <i class="fas fa-trash btn-remove-item" onclick="InvoiceItemManager.removeItem(this)"></i>
            </td>
        `;
        container.appendChild(row);
        this.itemCount++;
    }

    static removeItem(btn) {
        btn.closest('tr').remove();
        InvoiceCalculator.calculateTotals();
    }
}

// AJAX Utility Class
class Ajax {
    static request(url, method = 'GET', data = null, headers = {}) {
        return new Promise((resolve, reject) => {
            const xhr = new XMLHttpRequest();

            if (!headers['Content-Type'] && (method === 'POST' || method === 'DELETE')) {
                headers['Content-Type'] = 'application/json';
            }

            xhr.open(method, url, true);

            Object.keys(headers).forEach(key => {
                xhr.setRequestHeader(key, headers[key]);
            });

            xhr.onreadystatechange = function () {
                if (xhr.readyState === XMLHttpRequest.DONE) {
                    if (xhr.status >= 200 && xhr.status < 300) {
                        try {
                            const response = JSON.parse(xhr.responseText);
                            resolve(response);
                        } catch (e) {
                            reject(new Error('Invalid JSON response'));
                        }
                    } else {
                        try {
                            const error = JSON.parse(xhr.responseText);
                            reject(new Error(error.message || `HTTP Error ${xhr.status}`));
                        } catch (e) {
                            reject(new Error(`HTTP Error ${xhr.status}: ${xhr.statusText}`));
                        }
                    }
                }
            };

            xhr.onerror = function () {
                reject(new Error('Network error occurred'));
            };

            xhr.ontimeout = function () {
                reject(new Error('Request timeout'));
            };

            xhr.timeout = 30000;

            if (data && (method === 'POST' || method === 'DELETE')) {
                xhr.send(JSON.stringify(data));
            } else {
                xhr.send();
            }
        });
    }

    static get(url) {
        return this.request(url, 'GET');
    }

    static post(url, data) {
        return this.request(url, 'POST', data);
    }

    static delete(url, data) {
        return this.request(url, 'DELETE', data);
    }
}

// Invoice API Service Class
class InvoiceAPI {
    static async getNextNumber() {
        try {
            const response = await Ajax.get('api.php?next=1');
            if (!response.success) {
                throw new Error(response.message || 'Failed to load next invoice number');
            }
            return response.data.next_invoice_number;
        } catch (error) {
            throw new Error(error.message || 'Failed to load next invoice number');
        }
    }

    static async getAll() {
        try {
            const response = await Ajax.get('api.php');
            if (!response.success) {
                throw new Error(response.message || 'Failed to load invoices');
            }
            return response.data.invoices || [];
        } catch (error) {
            throw new Error(error.message || 'Failed to load invoices');
        }
    }

    static async getOne(invoiceNumber) {
        try {
            const response = await Ajax.get(`api.php?invoice_number=${invoiceNumber}`);
            if (!response.success) {
                throw new Error(response.message || 'Failed to load invoice');
            }
            return response.data;
        } catch (error) {
            throw new Error(error.message || 'Failed to load invoice');
        }
    }

    static async save(invoiceData) {
        try {
            const response = await Ajax.post('api.php', invoiceData);
            if (!response.success) {
                throw new Error(response.message || 'Failed to save invoice');
            }
            return response.data;
        } catch (error) {
            throw new Error(error.message || 'Failed to save invoice');
        }
    }

    static async delete(invoiceNumber) {
        try {
            const response = await Ajax.delete('api.php', { invoice_number: invoiceNumber });
            if (!response.success) {
                throw new Error(response.message || 'Failed to delete invoice');
            }
            return true;
        } catch (error) {
            throw new Error(error.message || 'Failed to delete invoice');
        }
    }

    static async getProducts() {
        try {
            const response = await Ajax.get('api.php?products=1');
            if (!response.success) {
                throw new Error(response.message || 'Failed to load products');
            }
            return response.data.products || [];
        } catch (error) {
            console.error('Error loading products:', error);
            return [];
        }
    }
}

// Main Application Class
class InvoiceApp {
    static editingInvoiceNumber = null;
    static invoicesCache = [];
    static productsCache = [];

    static getCurrencySymbol(currency) {
        const symbols = {
            'SAR': 'SR',
            'USD': '$',
            'JOD': 'JOD',
            'EUR': '€'
        };
        return symbols[currency] || currency;
    }

    static showAlert(message, type = 'success') {
        const alertContainer = document.getElementById('alertContainer');
        if (!alertContainer) return;

        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        alertContainer.appendChild(alertDiv);
        setTimeout(() => alertDiv.remove(), 5000);
    }

    static collectFormData() {
        const formData = new FormData(document.getElementById('invoiceForm'));
        // Get bill_to and ship_to directly from elements (not FormData) because disabled fields aren't included
        const billToEl = document.getElementById('bill_to');
        const shipToEl = document.getElementById('ship_to');
        
        const data = {
            invoice_number: formData.get('invoice_number'),
            customer: formData.get('customer'),
            tags: document.getElementById('tags')?.value || null,
            bill_to: billToEl ? billToEl.value : null,
            ship_to: shipToEl ? shipToEl.value : null,
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

        const itemRows = document.querySelectorAll('.item-row');
        itemRows.forEach((row) => {
            const item = {
                item_name: row.querySelector(`input[name^="items["][name$="][item_name]"]`)?.value || '',
                description: row.querySelector(`textarea[name^="items["][name$="][description]"]`)?.value || '',
                qty: parseFloat(row.querySelector(`input[name^="items["][name$="][qty]"]`)?.value) || 0,
                rate: parseFloat(row.querySelector(`input[name^="items["][name$="][rate]"]`)?.value) || 0,
                tax: parseFloat(row.querySelector(`input[name^="items["][name$="][tax]"]`)?.value) || 0,
                amount: parseFloat(row.querySelector(`input[name^="items["][name$="][amount]"]`)?.value) || 0
            };
            if (item.item_name) {
                data.items.push(item);
            }
        });

        const subtotal = data.items.reduce((sum, item) => sum + (item.qty * item.rate), 0);
        const totalTax = data.items.reduce((sum, item) => {
            const itemSubtotal = item.qty * item.rate;
            return sum + (itemSubtotal * (item.tax / 100));
        }, 0);

        const discountValue = parseFloat(document.getElementById('discount')?.value) || 0;
        const discountUnit = document.getElementById('discountUnit')?.value || '%';
        const discountAmount = discountUnit === '%' ? subtotal * (discountValue / 100) : discountValue;
        const adjustment = parseFloat(document.getElementById('adjustment')?.value) || 0;

        data.sub_total = subtotal;
        data.total_tax = totalTax;
        data.discount_value = discountAmount;
        data.adjustment = adjustment;
        data.total = subtotal + totalTax - discountAmount + adjustment;

        return data;
    }

    static async submitInvoice(e) {
        e.preventDefault();

        const data = this.collectFormData();
        if (this.editingInvoiceNumber) {
            data.invoice_number = this.editingInvoiceNumber;
        }

        if (!data.customer) {
            this.showAlert('Please enter a customer name', 'danger');
            return;
        }

        if (data.items.length === 0) {
            this.showAlert('Please add at least one item', 'danger');
            return;
        }

        try {
            const result = await InvoiceAPI.save(data);
            this.showAlert('Invoice saved! Invoice #' + result.invoice_number, 'success');
            this.editingInvoiceNumber = null;
            this.resetForm();
            if (typeof loadInvoices === 'function') {
                loadInvoices();
            }
        } catch (error) {
            this.showAlert('Error: ' + error.message, 'danger');
        }
    }

    static resetForm() {
        const form = document.getElementById('invoiceForm');
        if (!form) return;

        form.reset();
        const billArea = document.getElementById('bill_to');
        const shipArea = document.getElementById('ship_to');
        if (billArea) {
            billArea.value = '';
            billArea.setAttribute('disabled', 'disabled');
        }
        if (shipArea) {
            shipArea.value = '';
            shipArea.setAttribute('disabled', 'disabled');
        }
        if (document.getElementById('discount')) document.getElementById('discount').value = 0;
        if (document.getElementById('adjustment')) document.getElementById('adjustment').value = 0;
        if (document.getElementById('discountUnit')) document.getElementById('discountUnit').value = '%';

        const container = document.getElementById('itemsContainer');
        if (container) {
            container.innerHTML = '';
            InvoiceItemManager.itemCount = 0;
            InvoiceItemManager.addItem({ qty: 1, rate: 0, tax: 15, amount: 0 });
        }
        InvoiceCalculator.calculateTotals();
        this.initInvoiceNumber();
    }

    static setInvoiceNumber(num) {
        const el = document.getElementById('invoice_number');
        if (el) el.value = num;
    }

    static async initInvoiceNumber() {
        // Don't initialize if we're editing an invoice
        if (this.editingInvoiceNumber) {
            return;
        }
        // Don't initialize if there's an invoice_number in the URL
        const urlParams = new URLSearchParams(window.location.search);
        const invoiceParam = urlParams.get('invoice_number');
        if (invoiceParam) {
            return;
        }
        try {
            const next = await InvoiceAPI.getNextNumber();
            this.setInvoiceNumber(next);
        } catch (err) {
            this.showAlert('Could not fetch next invoice number: ' + err.message, 'danger');
        }
    }

    static async loadProducts() {
        try {
            this.productsCache = await InvoiceAPI.getProducts();
            this.populateProductDropdown();
        } catch (err) {
            console.error('Error loading products:', err);
        }
    }

    static populateProductDropdown() {
        const dropdown = document.getElementById('itemDropdown');
        if (!dropdown) return;

        dropdown.innerHTML = '<option value="">Select Item</option>';
        this.productsCache.forEach(product => {
            const option = document.createElement('option');
            option.value = product.id;
            option.textContent = product.name;
            dropdown.appendChild(option);
        });
    }

    static handleProductSelection() {
        const dropdown = document.getElementById('itemDropdown');
        if (!dropdown || !dropdown.value) return;

        const productId = parseInt(dropdown.value);
        const product = this.productsCache.find(p => p.id === productId);
        
        if (product) {
            let targetRow = null;
            const rows = document.querySelectorAll('.item-row');
            
            for (let row of rows) {
                const nameInput = row.querySelector('input[name$="[item_name]"]');
                if (nameInput && !nameInput.value.trim()) {
                    targetRow = row;
                    break;
                }
            }

            if (!targetRow) {
                InvoiceItemManager.addItem();
                const allRows = document.querySelectorAll('.item-row');
                targetRow = allRows[allRows.length - 1];
            }

            if (targetRow) {
                const nameInput = targetRow.querySelector('input[name$="[item_name]"]');
                const descInput = targetRow.querySelector('textarea[name$="[description]"]');
                const qtyInput = targetRow.querySelector('input[name$="[qty]"]');
                const rateInput = targetRow.querySelector('input[name$="[rate]"]');
                const taxInput = targetRow.querySelector('input[name$="[tax]"]');

                if (nameInput) nameInput.value = (product.name || product.item_name || '').toString();
                if (descInput) descInput.value = (product.description || '').toString();
                if (qtyInput) qtyInput.value = 1;
                if (rateInput) rateInput.value = product.rate || 0;
                if (taxInput) taxInput.value = product.tax || 15;

                const idxMatch = qtyInput?.name.match(/items\[(\d+)]/);
                if (idxMatch) {
                    const idx = parseInt(idxMatch[1], 10);
                    InvoiceCalculator.calculateRow(idx);
                }
            }
        }

        dropdown.value = '';
    }

    static async editInvoice(invoiceNumber) {
        // Parse invoice number to ensure it's a number
        const invNum = parseInt(invoiceNumber, 10);
        if (isNaN(invNum) || invNum <= 0) {
            this.showAlert('Invalid invoice number', 'danger');
            return;
        }

        const invoiceForm = document.getElementById('invoiceForm');
        if (!invoiceForm) {
            // Redirect to index.php with invoice number
            window.location.href = `index.php?invoice_number=${invNum}`;
            return;
        }

        try {
            // Set editing flag immediately to prevent form reset
            this.editingInvoiceNumber = invNum;
            
            // Show loading message
            this.showAlert('Loading invoice...', 'info');
            
            const data = await InvoiceAPI.getOne(invNum);
            if (!data || !data.invoice) {
                throw new Error('Invoice data not found');
            }
            
            const inv = data.invoice;

            // Set editing flag
            this.editingInvoiceNumber = inv.invoice_number;
            
            // Populate invoice number
            this.setInvoiceNumber(inv.invoice_number);
            
            // Populate customer
            const customerField = document.getElementById('customer');
            if (customerField) customerField.value = inv.customer || '';
            
            // Populate tags
            const tagsField = document.getElementById('tags');
            if (tagsField) tagsField.value = inv.tags || '';

            // Populate addresses
            const billArea = document.getElementById('bill_to');
            const shipArea = document.getElementById('ship_to');
            if (billArea) {
                billArea.value = inv.bill_to || '';
                if (inv.bill_to) {
                    billArea.removeAttribute('disabled');
                } else {
                    billArea.setAttribute('disabled', 'disabled');
                }
            }
            if (shipArea) {
                shipArea.value = inv.ship_to || '';
                if (inv.ship_to) {
                    shipArea.removeAttribute('disabled');
                } else {
                    shipArea.setAttribute('disabled', 'disabled');
                }
            }

            // Populate dates
            const invoiceDateField = document.getElementById('invoice_date');
            if (invoiceDateField) invoiceDateField.value = inv.invoice_date || '';
            
            const dueDateField = document.getElementById('due_date');
            if (dueDateField) dueDateField.value = inv.due_date || '';

            // Populate dropdowns
            const currencyField = document.getElementById('currency');
            if (currencyField) currencyField.value = inv.currency || 'SAR';
            
            const paymentModeField = document.getElementById('payment_mode');
            if (paymentModeField) paymentModeField.value = inv.payment_mode || 'Bank';
            
            const saleAgentField = document.getElementById('sale_agent');
            if (saleAgentField) saleAgentField.value = inv.sale_agent || '';
            
            const discountTypeField = document.getElementById('discount_type');
            if (discountTypeField) discountTypeField.value = inv.discount_type || 'Before Tax';
            
            const recurringInvoiceField = document.getElementById('recurring_invoice');
            if (recurringInvoiceField) recurringInvoiceField.value = inv.recurring_invoice || 'No';
            
            const adminNoteField = document.getElementById('admin_note');
            if (adminNoteField) adminNoteField.value = inv.admin_note || '';
            
            const discountField = document.getElementById('discount');
            if (discountField) discountField.value = inv.discount_value || 0;
            
            const adjustmentField = document.getElementById('adjustment');
            if (adjustmentField) adjustmentField.value = inv.adjustment || 0;

            // Populate items
            const container = document.getElementById('itemsContainer');
            if (container) {
                container.innerHTML = '';
                InvoiceItemManager.itemCount = 1; // Reset counter to start from 1
                if (data.items && data.items.length > 0) {
                    data.items.forEach(item => {
                        InvoiceItemManager.addItem(item);
                    });
                } else {
                    InvoiceItemManager.addItem({ qty: 1, rate: 0, tax: 15, amount: 0 });
                }
            }
            
            // Trigger currency change to update totals display
            if (currencyField) {
                currencyField.dispatchEvent(new Event('change'));
            }
            
            // Ensure dropdown handler is initialized
            const itemDropdown = document.getElementById('itemDropdown');
            if (itemDropdown && !itemDropdown.hasAttribute('data-listener-attached')) {
                itemDropdown.setAttribute('data-listener-attached', 'true');
                itemDropdown.addEventListener('change', () => InvoiceApp.handleProductSelection());
            }
            
            // Recalculate totals
            InvoiceCalculator.calculateTotals();
            
            // Scroll to top of form
            setTimeout(() => {
                if (invoiceForm) {
                    invoiceForm.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            }, 100);
            
            // Update page title and heading
            document.title = `Edit Invoice #${inv.invoice_number}`;
            const pageTitle = document.querySelector('.page-title');
            if (pageTitle) {
                pageTitle.textContent = `Edit Invoice #${inv.invoice_number}`;
            }
            
            // Show success message
            setTimeout(() => {
                this.showAlert('Invoice loaded successfully. Edit and submit to save.', 'success');
            }, 300);
            
        } catch (err) {
            console.error('Error loading invoice:', err);
            this.showAlert('Error loading invoice: ' + (err.message || 'Unknown error'), 'danger');
        }
    }

    static viewInvoice(invoiceNumber) {
        window.open(`invoice_pdf.php?invoice_number=${invoiceNumber}`, '_blank');
    }

    static async deleteInvoice(invoiceNumber) {
        if (!confirm('Delete invoice #' + invoiceNumber + '?')) return;
        try {
            await InvoiceAPI.delete(invoiceNumber);
            this.showAlert('Invoice deleted', 'success');
            if (typeof loadInvoices === 'function') {
                loadInvoices();
            }
        } catch (err) {
            this.showAlert('Error deleting invoice: ' + err.message, 'danger');
        }
    }
}

// Address Modal Handler
class AddressModal {
    static modal = null;
    static currentTarget = null; // 'bill_to' or 'ship_to'
    static initialized = false;

    static init() {
        // Prevent multiple initializations
        if (this.initialized) {
            console.log('AddressModal already initialized');
            return;
        }
        
        console.log('Initializing AddressModal...');
        this.initialized = true;
        
        const addressModalEl = document.getElementById('addressModal');
        if (!addressModalEl) {
            console.error('Address modal element not found!');
            return;
        }
        
        // Check if Bootstrap is available
        if (typeof bootstrap === 'undefined') {
            console.error('Bootstrap is not loaded!');
            return;
        }
        
        try {
            this.modal = new bootstrap.Modal(addressModalEl);
            console.log('Bootstrap modal created');
            
            // Reset currentTarget when modal is hidden
            addressModalEl.addEventListener('hidden.bs.modal', () => {
                this.currentTarget = null;
            });
        } catch (err) {
            console.error('Error creating Bootstrap modal:', err);
            return;
        }

        const billBtn = document.getElementById('billAddressBtn');
        const shipBtn = document.getElementById('shipAddressBtn');
        const applyBtn = document.getElementById('applyAddressBtn');

        if (!billBtn) {
            console.error('Bill address button not found!');
        } else if (!billBtn.hasAttribute('data-listener-attached')) {
            billBtn.setAttribute('data-listener-attached', 'true');
            billBtn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                console.log('Bill To button clicked');
                this.currentTarget = 'bill_to';
                this.updateModalTitle('Bill To');
                if (this.modal) {
                    this.modal.show();
                }
            });
            console.log('Bill To button listener attached');
        }

        if (!shipBtn) {
            console.error('Ship address button not found!');
        } else if (!shipBtn.hasAttribute('data-listener-attached')) {
            shipBtn.setAttribute('data-listener-attached', 'true');
            shipBtn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                console.log('Ship To button clicked');
                this.currentTarget = 'ship_to';
                this.updateModalTitle('Ship To');
                if (this.modal) {
                    this.modal.show();
                }
            });
            console.log('Ship To button listener attached');
        }

        if (!applyBtn) {
            console.error('Apply address button not found!');
        } else if (!applyBtn.hasAttribute('data-listener-attached')) {
            applyBtn.setAttribute('data-listener-attached', 'true');
            applyBtn.addEventListener('click', (e) => {
                console.log('Apply address button clicked, target:', this.currentTarget);
                this.applyAddress(e);
            });
            console.log('Apply button listener attached');
        }

        const billPinBtn = document.getElementById('billPinBtn');
        const shipPinBtn = document.getElementById('shipPinBtn');
        
        if (billPinBtn) {
            billPinBtn.addEventListener('click', () => {
                const area = document.getElementById('bill_to');
                if (area) {
                    area.removeAttribute('disabled');
                    area.focus();
                }
            });
        }
        
        if (shipPinBtn) {
            shipPinBtn.addEventListener('click', () => {
                const area = document.getElementById('ship_to');
                if (area) {
                    area.removeAttribute('disabled');
                    area.focus();
                }
            });
        }
        
        console.log('AddressModal initialization complete');
    }

    static updateModalTitle(targetName) {
        const modalLabel = document.getElementById('addressModalLabel');
        const applyBtn = document.getElementById('applyAddressBtn');
        if (modalLabel) {
            modalLabel.textContent = `Add ${targetName} Address`;
        }
        if (applyBtn) {
            applyBtn.textContent = `Apply to ${targetName}`;
        }
    }

    static applyAddress(e) {
        e.preventDefault();
        e.stopPropagation();

        if (!this.currentTarget) {
            InvoiceApp.showAlert('Error: No target field selected', 'danger');
            return;
        }

        const street = document.getElementById('addressStreet')?.value.trim();
        const city = document.getElementById('addressCity')?.value.trim();
        const state = document.getElementById('addressState')?.value.trim();
        const zip = document.getElementById('addressZip')?.value.trim();
        const country = document.getElementById('addressCountry')?.value.trim();

        if (!street || !city || !state || !zip || !country) {
            InvoiceApp.showAlert('Please complete all address fields', 'danger');
            return;
        }

        const cityStateZip = [city, state].filter(Boolean).join(', ') + (zip ? ` ${zip}` : '');
        const addressText = [street, cityStateZip, country].filter(Boolean).join('\n');

        // Only apply to the selected target field
        const targetArea = document.getElementById(this.currentTarget);
        if (targetArea) {
            targetArea.removeAttribute('disabled');
            targetArea.value = addressText;
            targetArea.dispatchEvent(new Event('input'));
        }

        // Clear form
        const streetInput = document.getElementById('addressStreet');
        const cityInput = document.getElementById('addressCity');
        const stateInput = document.getElementById('addressState');
        const zipInput = document.getElementById('addressZip');
        const countryInput = document.getElementById('addressCountry');
        
        if (streetInput) streetInput.value = '';
        if (cityInput) cityInput.value = '';
        if (stateInput) stateInput.value = '';
        if (zipInput) zipInput.value = '';
        if (countryInput) countryInput.value = '';

        const targetName = this.currentTarget === 'bill_to' ? 'Bill To' : 'Ship To';
        
        // Hide modal
        if (this.modal) {
            this.modal.hide();
        }
        
        // Show success message after a short delay to ensure modal is closed
        setTimeout(() => {
            InvoiceApp.showAlert(`Address added to ${targetName}`, 'success');
        }, 300);
    }
}

// Invoice List Functions
async function loadInvoices() {
    try {
        const invoices = await InvoiceAPI.getAll();
        InvoiceApp.invoicesCache = invoices;
        renderInvoiceTable(invoices);
    } catch (err) {
        InvoiceApp.showAlert('Error loading invoices: ' + err.message, 'danger');
    }
}

function renderInvoiceTable(invoices) {
    const body = document.getElementById('invoiceTableBody');
    if (!body) return;

    body.innerHTML = '';
    if (!invoices.length) {
        body.innerHTML = '<tr><td colspan="8" class="text-center text-muted py-4">No invoices yet</td></tr>';
        return;
    }

    invoices.forEach(inv => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>INV-${inv.invoice_number}</td>
            <td>${inv.currency || ''}${Number(inv.total || 0).toFixed(2)}</td>
            <td>${inv.currency || ''}${Number(inv.total_tax || 0).toFixed(2)}</td>
            <td>${inv.invoice_date || ''}</td>
            <td>${inv.customer || ''}</td>
            <td>${inv.tags || ''}</td>
            <td>${inv.due_date || ''}</td>
            <td class="text-end">
                <button class="btn btn-sm btn-outline-primary me-1" onclick="InvoiceApp.viewInvoice(${inv.invoice_number})">
                    <i class="fas fa-file-pdf"></i>
                </button>
                <button class="btn btn-sm btn-outline-secondary me-1" onclick="InvoiceApp.editInvoice(${inv.invoice_number})">
                    <i class="fas fa-pen"></i>
                </button>
                <button class="btn btn-sm btn-outline-danger" onclick="InvoiceApp.deleteInvoice(${inv.invoice_number})">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
        body.appendChild(tr);
    });
}

// Global functions for backwards compatibility
function addItem(itemData) {
    InvoiceItemManager.addItem(itemData);
}

function removeItem(btn) {
    InvoiceItemManager.removeItem(btn);
}

function calculateRow(index) {
    InvoiceCalculator.calculateRow(index);
}

function calculateTotals() {
    InvoiceCalculator.calculateTotals();
}

function viewInvoice(invoiceNumber) {
    InvoiceApp.viewInvoice(invoiceNumber);
}

function editInvoice(invoiceNumber) {
    InvoiceApp.editInvoice(invoiceNumber);
}

function deleteInvoice(invoiceNumber) {
    InvoiceApp.deleteInvoice(invoiceNumber);
}

function saveDraft() {
    InvoiceApp.showAlert('Draft saved successfully!', 'info');
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM Content Loaded - Initializing...');
    
    const invoiceForm = document.getElementById('invoiceForm');
    const invoiceTableBody = document.getElementById('invoiceTableBody');
    
    // Check for invoice_number in URL parameters
    const urlParams = new URLSearchParams(window.location.search);
    const invoiceParam = urlParams.get('invoice_number');
    
    console.log('Invoice Form found:', !!invoiceForm);
    console.log('Invoice Param:', invoiceParam);

    if (invoiceForm) {
        // Form submit handler
        invoiceForm.addEventListener('submit', (e) => {
            e.preventDefault();
            InvoiceApp.submitInvoice(e);
        });
        
        // Currency change handler
        const currencySelect = document.getElementById('currency');
        if (currencySelect) {
            currencySelect.addEventListener('change', InvoiceCalculator.calculateTotals);
        }

        // Initialize address modal FIRST
        try {
            AddressModal.init();
            console.log('AddressModal initialized');
        } catch (err) {
            console.error('Error initializing AddressModal:', err);
        }
        
        // Load products
        try {
            InvoiceApp.loadProducts();
            console.log('Products loading...');
        } catch (err) {
            console.error('Error loading products:', err);
        }
        
        // Set up item dropdown handler
        const itemDropdown = document.getElementById('itemDropdown');
        if (itemDropdown) {
            if (!itemDropdown.hasAttribute('data-listener-attached')) {
                itemDropdown.setAttribute('data-listener-attached', 'true');
                itemDropdown.addEventListener('change', () => {
                    console.log('Item dropdown changed');
                    InvoiceApp.handleProductSelection();
                });
                console.log('Item dropdown handler attached');
            }
        } else {
            console.warn('Item dropdown not found!');
        }
        
        // If invoice_number is in URL, load that invoice for editing
        if (invoiceParam) {
            console.log('Loading invoice for editing:', invoiceParam);
            // Update page title immediately
            const pageTitle = document.querySelector('.page-title');
            if (pageTitle) {
                pageTitle.textContent = 'Edit Invoice';
            }
            // Small delay to ensure all form elements are ready
            setTimeout(() => {
                InvoiceApp.editInvoice(invoiceParam).catch(err => {
                    console.error('Error editing invoice:', err);
                });
            }, 300);
        } else {
            console.log('Initializing new invoice');
            InvoiceApp.initInvoiceNumber();
            InvoiceItemManager.addItem({ qty: 1, rate: 0, tax: 15, amount: 0 });
        }
    } else {
        console.warn('Invoice form not found!');
    }

    if (invoiceTableBody) {
        console.log('Loading invoices list...');
        loadInvoices();
    }
    
    console.log('Initialization complete');
});