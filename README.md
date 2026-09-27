# Invoice Sales Management System

A PHP MVC invoice management system for creating, viewing, editing, and deleting sales invoices, managing products and invoice items, and generating PDF invoices.

## Features

- Create and manage sales invoices
- Add and manage invoice items and products
- View and list invoices
- Edit and delete invoices
- Generate PDF invoices
- MySQL database integration
- AJAX-based client/server communication
- MVC-based PHP structure
- Service layer for invoice-related business logic

## Technologies

- PHP
- MySQL
- JavaScript
- AJAX
- HTML
- CSS
- Bootstrap
- Composer
- TCPDF

## Project Structure

```text
Controllers/
Core/
Models/
views/
InvoiceService.php
api.php
index.php
invoices.php
app.js
script.js
style.css
composer.json
```

## Installation

1. Clone the repository:

   ```bash
   git clone https://github.com/Ahmed-Alzoubi-az9/Invoice-Sales.git
   ```

2. Install PHP dependencies:

   ```bash
   composer install
   ```

3. Create the required MySQL database and configure the database connection in `Core/Database.php`.
4. Run the project using a local PHP/Apache environment such as XAMPP.

## Notes

This project was developed as a practical PHP MVC invoice management application and demonstrates CRUD operations, database integration, client-server communication, PDF generation, and basic application architecture.
