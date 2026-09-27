<?php

namespace App\Controllers;

use App\Models\InvoiceModel;
use Throwable;

class InvoiceController
{
    private $invoices;

    public function handleApi(): void
    {
        try {
            switch ($_SERVER['REQUEST_METHOD'] ?? 'GET') {
                case 'GET':
                    if (isset($_GET['next'])) {
                        $this->respond(true, ['next_invoice_number' => $this->invoices()->getNextInvoiceNumber()]);
                    }
                    if (isset($_GET['products'])) {
                        $this->respond(true, ['products' => $this->invoices()->getAllProducts()]);
                    }
                    if (isset($_GET['invoice_number'])) {
                        $number = (int) $_GET['invoice_number'];
                        $invoice = $this->invoices()->getInvoice($number);
                        if (!$invoice) {
                            $this->respond(false, null, 'Invoice not found', 404);
                        }
                        $this->respond(true, [
                            'invoice' => $invoice,
                            'items' => $this->invoices()->getInvoiceItems($number),
                        ]);
                    }
                    $this->respond(true, ['invoices' => $this->invoices()->getAllInvoices()]);

                case 'POST':
                    $data = $this->readJson();
                    if (empty($data['customer'])) {
                        $this->respond(false, null, 'Customer is required', 400);
                    }
                    if (empty($data['invoice_date'])) {
                        $this->respond(false, null, 'Invoice date is required', 400);
                    }
                    if (empty($data['items']) || !is_array($data['items'])) {
                        $this->respond(false, null, 'At least one item is required', 400);
                    }
                    $number = $this->invoices()->saveInvoice($data);
                    $this->respond(true, ['invoice_number' => (int) $number], 'Invoice saved successfully');

                case 'DELETE':
                    $data = $this->readJson();
                    if (empty($data['invoice_number'])) {
                        $this->respond(false, null, 'invoice_number is required', 400);
                    }
                    if (!$this->invoices()->deleteInvoice((int) $data['invoice_number'])) {
                        $this->respond(false, null, 'Invoice not found', 404);
                    }
                    $this->respond(true, null, 'Invoice deleted successfully');

                default:
                    $this->respond(false, null, 'Method not allowed', 405);
            }
        } catch (Throwable $error) {
            $this->respond(false, null, 'Server error: ' . $error->getMessage(), 500);
        }
    }

    public function saveLegacy(): void
    {
        try {
            $data = $this->readJson(true);
            $number = $this->invoices()->saveInvoice($data);
            $this->respondLegacy(['success' => true, 'invoice_number' => $number]);
        } catch (Throwable $error) {
            $this->respondLegacy(['success' => false, 'message' => $error->getMessage()], 400);
        }
    }

    public function listLegacy(): void
    {
        try {
            $this->respondLegacy(['success' => true, 'invoices' => $this->invoices()->getAllInvoices()]);
        } catch (Throwable $error) {
            $this->respondLegacy(['success' => false, 'message' => $error->getMessage()], 500);
        }
    }

    public function getLegacy(): void
    {
        try {
            $number = isset($_GET['invoice_number']) ? (int) $_GET['invoice_number'] : 0;
            $invoice = $number ? $this->invoices()->getInvoice($number) : false;
            if (!$invoice) {
                $this->respondLegacy(['success' => false, 'message' => 'Invoice not found'], 404);
            }
            $this->respondLegacy([
                'success' => true,
                'invoice' => $invoice,
                'items' => $this->invoices()->getInvoiceItems($number),
            ]);
        } catch (Throwable $error) {
            $this->respondLegacy(['success' => false, 'message' => $error->getMessage()], 500);
        }
    }

    public function deleteLegacy(): void
    {
        try {
            $data = $this->readJson(true);
            if (empty($data['invoice_number'])) {
                $this->respondLegacy(['success' => false, 'message' => 'Invoice number required'], 400);
            }
            if (!$this->invoices()->deleteInvoice((int) $data['invoice_number'])) {
                $this->respondLegacy(['success' => false, 'message' => 'Invoice not found or could not delete'], 404);
            }
            $this->respondLegacy(['success' => true]);
        } catch (Throwable $error) {
            $this->respondLegacy(['success' => false, 'message' => $error->getMessage()], 500);
        }
    }

    private function invoices(): InvoiceModel
    {
        if ($this->invoices === null) {
            $this->invoices = new InvoiceModel();
        }
        return $this->invoices;
    }

    private function readJson(bool $legacy = false): array
    {
        $data = json_decode(file_get_contents('php://input'), true);
        if (!is_array($data)) {
            if ($legacy) {
                $this->respondLegacy(['success' => false, 'message' => 'Invalid JSON payload'], 400);
            }
            $this->respond(false, null, 'Invalid JSON payload', 400);
        }
        return $data;
    }

    private function respond(bool $success, ?array $data = null, ?string $message = null, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        $response = ['success' => $success];
        if ($data !== null) {
            $response['data'] = $data;
        }
        if ($message !== null) {
            $response['message'] = $message;
        }
        echo json_encode($response);
        exit;
    }

    private function respondLegacy(array $response, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
}