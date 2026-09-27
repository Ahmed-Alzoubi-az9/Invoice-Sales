<?php

namespace App\Controllers;

class PageController
{
    public function create(): void
    {
        require __DIR__ . '/../views/invoices/create.php';
    }

    public function list(): void
    {
        require __DIR__ . '/../views/invoices/list.php';
    }
}