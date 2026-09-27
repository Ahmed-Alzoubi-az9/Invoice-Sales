<?php
namespace App\Models;

use Exception;
use App\Core\Database;
use PDO;

require_once __DIR__ . '/../Core/Database.php';

/**
 * Invoice model for invoice data access and persistence.
 */
class InvoiceModel {
    private PDO $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Get all invoices
     */
    public function getAllInvoices() {
        $stmt = $this->db->query("
            SELECT invoice_number, customer, tags, invoice_date, due_date,
                   currency, total, total_tax, sale_agent, payment_mode,
                   discount_value, sub_total, adjustment, admin_note
            FROM invoices
            ORDER BY invoice_number DESC
        ");
        return $stmt->fetchAll();
    }

    /**
     * Get all products for dropdown
     */
    public function getAllProducts() {
        $stmt = $this->db->query("SELECT id, name as item_name, description, rate, tax FROM products ORDER BY name ASC");
        return $stmt->fetchAll();
    }
    
    /**
     * Get a single invoice by number
     */
    public function getInvoice($invoiceNumber) {
        $stmt = $this->db->prepare("SELECT * FROM invoices WHERE invoice_number = :inv");
        $stmt->execute([':inv' => $invoiceNumber]);
        return $stmt->fetch();
    }
    
    /**
     * Get invoice items
     */
    public function getInvoiceItems($invoiceNumber) {
        $stmt = $this->db->prepare("
            SELECT item_name, description, qty, rate, tax, amount 
            FROM invoice_items 
            WHERE invoice_number = :inv
        ");
        $stmt->execute([':inv' => $invoiceNumber]);
        return $stmt->fetchAll();
    }
    
    /**
     * Save or update an invoice
     */
    public function saveInvoice($data) {
        $this->db->beginTransaction();
        
        try {
            $invoiceNumberProvided = isset($data['invoice_number']) && $data['invoice_number'] !== '';
            $existingInvoice = null;
            
            if ($invoiceNumberProvided) {
                $checkStmt = $this->db->prepare("SELECT invoice_number FROM invoices WHERE invoice_number = :inv");
                $checkStmt->execute([':inv' => $data['invoice_number']]);
                $existingInvoice = $checkStmt->fetchColumn();
            }
            
            if ($existingInvoice) {
                $this->updateInvoice($data);
                $invoiceNumber = $data['invoice_number'];
                // Remove existing items for clean re-insert
                $this->db->prepare("DELETE FROM invoice_items WHERE invoice_number = :inv")
                    ->execute([':inv' => $invoiceNumber]);
            } else {
                $invoiceNumber = $this->createInvoice($data);
            }
            
            // Insert items
            $this->saveInvoiceItems($invoiceNumber, $data['items']);
            
            $this->db->commit();
            return $invoiceNumber;
            
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    
    private function createInvoice($data) {
        $stmt = $this->db->prepare("
            INSERT INTO invoices (
                invoice_number, customer, tags, bill_to, ship_to, invoice_date, due_date,
                currency, payment_mode, sale_agent, discount_type, recurring_invoice,
                admin_note, sub_total, total_tax, discount_value, adjustment, total
            ) VALUES (
                :invoice_number, :customer, :tags, :bill_to, :ship_to, :invoice_date, :due_date,
                :currency, :payment_mode, :sale_agent, :discount_type, :recurring_invoice,
                :admin_note, :sub_total, :total_tax, :discount_value, :adjustment, :total
            )
        ");
        
        $stmt->execute([
            ':invoice_number'    => isset($data['invoice_number']) && $data['invoice_number'] ? $data['invoice_number'] : null,
            ':customer'          => $data['customer'],
            ':tags'              => $data['tags'] ?? null,
            ':bill_to'           => $data['bill_to'] ?? null,
            ':ship_to'           => $data['ship_to'] ?? null,
            ':invoice_date'      => $data['invoice_date'],
            ':due_date'          => $data['due_date'] ?? $data['invoice_date'],
            ':currency'          => $data['currency'] ?? 'SAR',
            ':payment_mode'      => $data['payment_mode'] ?? 'Bank',
            ':sale_agent'        => $data['sale_agent'] ?? null,
            ':discount_type'     => $data['discount_type'] ?? 'Before Tax',
            ':recurring_invoice' => $data['recurring_invoice'] ?? 'No',
            ':admin_note'        => $data['admin_note'] ?? null,
            ':sub_total'         => $data['sub_total'] ?? 0,
            ':total_tax'         => $data['total_tax'] ?? 0,
            ':discount_value'    => $data['discount_value'] ?? 0,
            ':adjustment'        => $data['adjustment'] ?? 0,
            ':total'             => $data['total'] ?? 0,
        ]);
        
        return isset($data['invoice_number']) && $data['invoice_number'] 
            ? $data['invoice_number'] 
            : $this->db->lastInsertId();
    }
    
    private function updateInvoice($data) {
        $stmt = $this->db->prepare("
            UPDATE invoices SET
                customer = :customer,
                tags = :tags,
                bill_to = :bill_to,
                ship_to = :ship_to,
                invoice_date = :invoice_date,
                due_date = :due_date,
                currency = :currency,
                payment_mode = :payment_mode,
                sale_agent = :sale_agent,
                discount_type = :discount_type,
                recurring_invoice = :recurring_invoice,
                admin_note = :admin_note,
                sub_total = :sub_total,
                total_tax = :total_tax,
                discount_value = :discount_value,
                adjustment = :adjustment,
                total = :total
            WHERE invoice_number = :invoice_number
        ");
        
        $stmt->execute([
            ':invoice_number'    => $data['invoice_number'],
            ':customer'          => $data['customer'],
            ':tags'              => $data['tags'] ?? null,
            ':bill_to'           => $data['bill_to'] ?? null,
            ':ship_to'           => $data['ship_to'] ?? null,
            ':invoice_date'      => $data['invoice_date'],
            ':due_date'          => $data['due_date'] ?? $data['invoice_date'],
            ':currency'          => $data['currency'] ?? 'SAR',
            ':payment_mode'      => $data['payment_mode'] ?? 'Bank',
            ':sale_agent'        => $data['sale_agent'] ?? null,
            ':discount_type'     => $data['discount_type'] ?? 'Before Tax',
            ':recurring_invoice' => $data['recurring_invoice'] ?? 'No',
            ':admin_note'        => $data['admin_note'] ?? null,
            ':sub_total'         => $data['sub_total'] ?? 0,
            ':total_tax'         => $data['total_tax'] ?? 0,
            ':discount_value'    => $data['discount_value'] ?? 0,
            ':adjustment'        => $data['adjustment'] ?? 0,
            ':total'             => $data['total'] ?? 0,
        ]);
    }
    
    private function saveInvoiceItems($invoiceNumber, $items) {
        $stmt = $this->db->prepare("
            INSERT INTO invoice_items (
                invoice_number, item_name, description, long_description, qty, rate, tax, amount
            ) VALUES (
                :invoice_number, :item_name, :description, :long_description, :qty, :rate, :tax, :amount
            )
        ");
        
        foreach ($items as $item) {
            if (empty($item['item_name'])) {
                continue; // Skip items without names
            }
            
            $stmt->execute([
                ':invoice_number'  => $invoiceNumber,
                ':item_name'       => $item['item_name'],
                ':description'     => $item['description'] ?? null,
                ':long_description'=> $item['long_description'] ?? null,
                ':qty'             => isset($item['qty']) ? (float)$item['qty'] : 0,
                ':rate'            => isset($item['rate']) ? (float)$item['rate'] : 0,
                ':tax'             => isset($item['tax']) ? (float)$item['tax'] : 0,
                ':amount'          => isset($item['amount']) ? (float)$item['amount'] : 0,
            ]);
        }
    }
    
    /**
     * Delete an invoice
     */
    public function deleteInvoice($invoiceNumber) {
        $stmt = $this->db->prepare("DELETE FROM invoices WHERE invoice_number = :inv");
        $stmt->execute([':inv' => $invoiceNumber]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Get the next invoice number (max + 1)
     */
    public function getNextInvoiceNumber() {
        $stmt = $this->db->query("SELECT MAX(invoice_number) as max_inv FROM invoices");
        $row = $stmt->fetch();
        $next = isset($row['max_inv']) ? (int)$row['max_inv'] + 1 : 1;
        return $next;
    }
}
