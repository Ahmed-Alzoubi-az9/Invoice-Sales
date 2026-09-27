<?php
namespace App\Core;

use Exception;
use PDO;
use PDOException;

/**
 * Database Connection and Management Class
 * Handles all database operations for the invoice application
 */
class Database
{
    private static $instance = null;
    private $pdo;

    private function __construct()
    {
        try {
            // First connect without specifying a database
            $this->pdo = new PDO("mysql:host=localhost;charset=utf8mb4", "root", "", [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);

            // Create database if it doesn't exist
            $this->pdo->exec("CREATE DATABASE IF NOT EXISTS invoice_app CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;");

            // Now reconnect with the database specified
            $this->pdo = new PDO("mysql:host=localhost;dbname=invoice_app;charset=utf8mb4", "root", "", [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);

            $this->ensureTables();
        } catch (PDOException $e) {
            throw new Exception("Database connection failed: " . $e->getMessage());
        }
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection(): PDO
    {
        return $this->pdo;
    }

    private function ensureTables()
    {
        // Create invoices table
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS invoices (
                invoice_number INT(5) UNSIGNED ZEROFILL AUTO_INCREMENT PRIMARY KEY,
                customer VARCHAR(150) NOT NULL,
                tags VARCHAR(255),
                bill_to TEXT,
                ship_to TEXT,
                invoice_date DATE NOT NULL,
                due_date DATE NOT NULL,
                currency ENUM('USD','SAR','JOD','EUR') DEFAULT 'SAR',
                payment_mode ENUM('Cash','Bank','Visa') DEFAULT 'Bank',
                sale_agent VARCHAR(150),
                discount_type ENUM('Before Tax','After Tax') DEFAULT 'Before Tax',
                recurring_invoice ENUM(
                    'Every 1 month','Every 2 months','Every 3 months',
                    'Every 4 months','Every 5 months','Every 6 months',
                    'Every 7 months','Every 8 months','Every 9 months',
                    'Every 10 months','Every 11 months','Every 12 months','No'
                ) DEFAULT 'No',
                admin_note TEXT,
                sub_total DECIMAL(14,2),
                total_tax DECIMAL(14,2),
                discount_value INT UNSIGNED,
                adjustment INT,
                total DECIMAL(14,2),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            );
        ");

        // Ensure tags column exists
        try {
            $this->pdo->exec("ALTER TABLE invoices ADD COLUMN tags VARCHAR(255)");
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), '1060') === false && strpos($e->getMessage(), 'Duplicate column') === false) {
                throw $e;
            }
        }

        // Create invoice_items table
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS invoice_items (
                id INT AUTO_INCREMENT PRIMARY KEY,
                invoice_number INT(5) UNSIGNED ZEROFILL NOT NULL,
                item_name VARCHAR(150) NOT NULL,
                description TEXT,
                long_description TEXT,
                qty INT(11),
                rate DECIMAL(10,2),
                tax DECIMAL(5,2),
                amount DECIMAL(10,2),
                FOREIGN KEY (invoice_number) REFERENCES invoices(invoice_number) ON DELETE CASCADE
            );
        ");

        // Create products table for dropdown items
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS products (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(150) NOT NULL UNIQUE,
                description TEXT,
                rate DECIMAL(10,2) DEFAULT 0.00,
                tax DECIMAL(10,2) DEFAULT 15.00,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            );
        ");

        // Insert sample products if table is empty
        $count = $this->pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
        if ($count == 0) {
            $this->pdo->exec("
                INSERT INTO products (name, description, rate, tax) VALUES
                ('Web Development', 'Professional web development service', 1000.00, 15.00),
                ('Mobile App Development', 'Mobile application development', 2500.00, 15.00),
                ('SEO Service', 'Search engine optimization', 500.00, 15.00),
                ('Graphic Design', 'Logo and branding design', 300.00, 15.00),
                ('Consulting', 'Technical consulting services', 200.00, 15.00),
                ('Hosting', 'Monthly hosting plan', 50.00, 15.00),
                ('Support', 'Support and maintenance', 80.00, 15.00);
            ");
        }
    }
}
