<?php
// TEMPORARY demo-data seeder — run once, then delete.

require __DIR__ . '/Core/bootstrap.php';

use App\Models\InvoiceModel;

$m = new InvoiceModel();

$demo = [
    [
        'customer' => 'Amen Adel',
        'tags' => 'Consulting',
        'bill_to' => "Al Rabiah\nAmman, 81111\nJordan",
        'invoice_date' => '2025-12-10',
        'due_date' => '2026-01-09',
        'status' => 'paid',
        'items' => [['item_name' => 'Consulting', 'description' => 'Technical consulting services', 'qty' => 1, 'rate' => 200, 'tax' => 15, 'amount' => 230]],
        'sub_total' => 200, 'total_tax' => 30, 'discount_value' => 0, 'adjustment' => 0, 'total' => 230,
    ],
    [
        'customer' => 'Rania Khalil',
        'tags' => 'Urgent, Monthly',
        'bill_to' => "Abdoun\nAmman, 11183\nJordan",
        'invoice_date' => '2026-08-12',
        'due_date' => '2026-09-11',
        'status' => 'unpaid',
        'items' => [['item_name' => 'Web Development', 'description' => 'Company website redesign', 'qty' => 1, 'rate' => 1000, 'tax' => 15, 'amount' => 1150]],
        'sub_total' => 1000, 'total_tax' => 150, 'discount_value' => 0, 'adjustment' => 0, 'total' => 1150,
    ],
    [
        'customer' => 'Omar Haddad',
        'tags' => 'Mobile',
        'bill_to' => "Sweifieh\nAmman, 11818\nJordan",
        'invoice_date' => '2026-09-20',
        'due_date' => '2026-10-20',
        'status' => 'paid',
        'items' => [['item_name' => 'Mobile App Development', 'description' => 'iOS prototype build', 'qty' => 1, 'rate' => 2500, 'tax' => 15, 'amount' => 2875]],
        'sub_total' => 2500, 'total_tax' => 375, 'discount_value' => 0, 'adjustment' => 0, 'total' => 2875,
    ],
    [
        'customer' => 'Sara Nasser',
        'tags' => 'SEO',
        'bill_to' => "Mecca Street, Wadi Saqra\nAmman, 11861\nJordan",
        'invoice_date' => '2026-10-15',
        'due_date' => '2026-11-15',
        'status' => 'unpaid',
        'items' => [['item_name' => 'SEO Service', 'description' => 'Q4 search optimization package', 'qty' => 1, 'rate' => 500, 'tax' => 15, 'amount' => 575]],
        'sub_total' => 500, 'total_tax' => 75, 'discount_value' => 0, 'adjustment' => 0, 'total' => 575,
    ],
    [
        'customer' => 'Layla Mansour',
        'tags' => 'Design, Branding',
        'bill_to' => "Garden's 5th Circle\nAmman, 11195\nJordan",
        'invoice_date' => '2026-10-25',
        'due_date' => '2026-11-25',
        'status' => 'draft',
        'items' => [['item_name' => 'Graphic Design', 'description' => 'Logo and branding identity', 'qty' => 2, 'rate' => 300, 'tax' => 15, 'amount' => 690]],
        'sub_total' => 600, 'total_tax' => 90, 'discount_value' => 0, 'adjustment' => 0, 'total' => 690,
    ],
    [
        'customer' => 'Yousef Kamal',
        'tags' => 'Hosting, Monthly',
        'bill_to' => "Jabal Amman, 1st Circle\nAmman, 11113\nJordan",
        'invoice_date' => '2026-07-01',
        'due_date' => '2026-08-01',
        'status' => 'unpaid',
        'items' => [['item_name' => 'Hosting', 'description' => 'Monthly hosting plan', 'qty' => 6, 'rate' => 50, 'tax' => 15, 'amount' => 345]],
        'sub_total' => 300, 'total_tax' => 45, 'discount_value' => 0, 'adjustment' => 0, 'total' => 345,
    ],
    [
        'customer' => 'Hala Saleh',
        'tags' => 'Support',
        'bill_to' => "Deir Ghbar\nAmman, 11192\nJordan",
        'invoice_date' => '2026-06-10',
        'due_date' => '2026-07-10',
        'status' => 'paid',
        'items' => [['item_name' => 'Support', 'description' => 'Support and maintenance hours', 'qty' => 4, 'rate' => 80, 'tax' => 15, 'amount' => 368]],
        'sub_total' => 320, 'total_tax' => 48, 'discount_value' => 0, 'adjustment' => 0, 'total' => 368,
    ],
];

foreach ($demo as $d) {
    $d['currency'] = 'SAR';
    $d['payment_mode'] = 'Bank';
    $d['sale_agent'] = 'ahmed alzoubi';
    $d['admin_note'] = null;
    $number = $m->saveInvoice($d);
    echo "Seeded invoice #{$number} ({$d['customer']}, {$d['status']})\n";
}

echo 'TOTAL_ROWS=' . $m->getConnection()->query('SELECT COUNT(*) FROM invoices')->fetchColumn() . "\n";
