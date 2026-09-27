<?php

namespace App\Controllers;

use App\Models\InvoiceModel;
use Throwable;

class InvoicePdfController
{
    public function show(): void
    {
        $invoiceNumber = isset($_GET['invoice_number']) ? (int) $_GET['invoice_number'] : 0;
        if ($invoiceNumber < 1) {
            http_response_code(400);
            exit('invoice_number is required');
        }

        $autoload = __DIR__ . '/../vendor/autoload.php';
        if (file_exists($autoload)) {
            require_once $autoload;
        } elseif (file_exists(__DIR__ . '/../vendor/tecnickcom/tcpdf/tcpdf.php')) {
            require_once __DIR__ . '/../vendor/tecnickcom/tcpdf/tcpdf.php';
        } else {
            http_response_code(500);
            exit('TCPDF not found. Please run "composer require tecnickcom/tcpdf".');
        }

        $outputBufferLevel = ob_get_level();
        try {
            $model = new InvoiceModel();
            $invoice = $model->getInvoice($invoiceNumber);
            if (!$invoice) {
                http_response_code(404);
                exit('Invoice not found');
            }
            $items = $model->getInvoiceItems($invoiceNumber);

            $pdf = new \TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);
            $pdf->SetMargins(10, 5, 10);
            $pdf->SetAutoPageBreak(true, 25);
            $pdf->AddPage();

            $forceDownload = isset($_GET['download']) && $_GET['download'] === '1';
            $statusLabel = isset($invoice['status']) ? strtoupper($invoice['status']) : 'DRAFT';
            $escape = static function ($value): string {
                return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
            };
            $formatMoney = static function ($amount, $currency): string {
                $symbol = $currency === 'USD' ? '$' : ($currency === 'EUR' ? '€' : ($currency === 'SAR' ? 'SR' : $currency));
                return $symbol . number_format((float) $amount, 1);
            };

            $renderPdfView = require __DIR__ . '/../views/invoices/pdf.php';
            $html = $renderPdfView($invoice, $items, $invoiceNumber, $statusLabel, $escape, $formatMoney);

            $pdf->writeHTML($html, true, false, true, false, '');
            $pdf->Output('invoice_' . $invoiceNumber . '.pdf', $forceDownload ? 'D' : 'I');
        } catch (Throwable $error) {
            while (ob_get_level() > $outputBufferLevel) {
                ob_end_clean();
            }
            http_response_code(500);
            exit('Error generating PDF: ' . $error->getMessage());
        }
    }
}
