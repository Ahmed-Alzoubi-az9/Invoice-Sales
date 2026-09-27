<?php

return static function (
    array $invoice,
    array $items,
    int $invoiceNumber,
    string $statusLabel,
    callable $escape,
    callable $formatMoney
): string {
    $billTo = !empty($invoice['bill_to']) ? nl2br($escape($invoice['bill_to'])) : '--';
    $customer = $escape($invoice['customer']);
    ob_start();
?>
<style>
    body { font-family: DejaVu Sans, sans-serif; color: #382db4; font-size: 9px; margin: 0; padding: 0; }
    .header-table td { vertical-align: top; padding: 0; }
    .logo { font-size: 30px; font-weight: 700; color: #6b7280; margin-bottom: 0; }
    .status { color: #6b7280; font-size: 8px; text-align: right; line-height: 1.3; }
    .status strong { display: block; color: #1a1a4d; font-size: 12px; letter-spacing: 0.5px; margin-bottom: 2px; }
    .company-info { font-size: 8.5px; line-height: 1.4; color: #374151; }
    .bill-to-section { text-align: right; }
    .bill-to-label { font-weight: 600; font-size: 9px; color: #1a1a4d; }
    .bill-to-content { font-size: 8.5px; line-height: 1.4; color: #374151; }
    .items-heading { margin: 20px 0 8px 0; }
    .table-head th { font-size: 9px; padding: 12px 10px !important; font-weight: 700; text-align: center;}
    .items td { padding: 15px 10px !important; font-size: 8.5px; text-align: center; }
    .terms { margin-top: 30px; font-size: 9px; padding-top: 15px; margin-bottom: 30px !important; }
    .signature { margin-top: 30px !important; font-size: 9px; line-height: 1.5; }
    .spacer-xl { height: 20px; }
</style>
<table class="header-table" width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td width="55%">
            <div class="logo">AR Company</div>
            <div class="company-info"><?= $customer ?><br><?= $billTo ?></div>
        </td>
        <td width="45%" style="text-align:right;">
            <div class="status">
                <strong>INVOICE</strong><br>
                # INV-<?= $escape($invoiceNumber) ?><br><?= $escape($statusLabel) ?>
            </div>
            <div class="bill-to-section">
                <div class="bill-to-label">Bill To:</div>
                <div class="bill-to-content"><?= $billTo ?></div>
            </div>
            <div style="font-size: 8.5px; margin-top: 15px;">
                Invoice Date: &nbsp;&nbsp;<span style="color: #1d1d1f;"><?= $escape($invoice['invoice_date']) ?></span><br>
                Due Date: &nbsp;&nbsp;<span style="color: #1d1d1f;"><?= $escape($invoice['due_date']) ?></span><br>
                Sale Agent: &nbsp;&nbsp;<span style="color: #1d1d1f;"><?= $escape($invoice['sale_agent'] ?? '') ?></span>
            </div>
        </td>
    </tr>
</table>
<div class="spacer-xl"></div>
<div class="items-heading">
    <table width="100%" cellspacing="0" cellpadding="0" class="table-head">
        <tr bgcolor="#323A45">
            <th width="6%" color="#FFFFFF">#</th>
            <th width="44%" color="#FFFFFF">Item</th>
            <th width="10%" color="#FFFFFF">Qty</th>
            <th width="15%" color="#FFFFFF">Rate</th>
            <th width="10%" color="#FFFFFF">Tax</th>
            <th width="15%" color="#FFFFFF">Amount</th>
        </tr>
        <?php if ($items): ?>
            <?php foreach ($items as $index => $item): ?>
                <tr class="<?= ($index + 1) % 2 === 0 ? 'items zebra' : 'items' ?>">
                    <td><?= $index + 1 ?></td>
                    <td><?= $escape($item['item_name']) ?></td>
                    <td><?= number_format((float) $item['qty'], 0) ?></td>
                    <td><?= $formatMoney($item['rate'], $invoice['currency']) ?></td>
                    <td><?= number_format((float) $item['tax'], 0) ?>%</td>
                    <td><?= $formatMoney($item['amount'], $invoice['currency']) ?></td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="6" style="text-align:center;padding:12px;">No items</td></tr>
        <?php endif; ?>
    </table>
</div>
<div style="margin-top: 25px; text-align: right;">
    <div style="font-size: 9px;">
        <div style="margin-bottom: 1px;">Sub Total: &nbsp;&nbsp;<span style="font-weight: 600;"><?= $formatMoney($invoice['sub_total'], $invoice['currency']) ?></span></div>
        <div style="margin-bottom: 1px;">Discount (30%): &nbsp;&nbsp;<span style="font-weight: 600;">-<?= $formatMoney($invoice['discount_value'], $invoice['currency']) ?></span></div>
        <div style="margin-bottom: 1px;"><span style="font-weight: 600;">Total: &nbsp;&nbsp;<?= $formatMoney($invoice['total'], $invoice['currency']) ?></span></div>
        <div><span style="font-weight: 700;">Amount Due: &nbsp;&nbsp;<?= $formatMoney($invoice['total'], $invoice['currency']) ?></span></div>
    </div>
</div>
<div class="terms">
    <div>Terms &amp; Conditions:</div><br>
    - Payment is due within 30 days from the date of the invoice.<br>
    - Late payments may be subject to interest charges of 2% per month.
</div>
<div class="signature">Authorized Signature _________________________________</div>
<?php
    return (string) ob_get_clean();
};
