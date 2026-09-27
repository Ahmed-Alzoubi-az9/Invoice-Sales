<?php

require_once __DIR__ . '/Core/Database.php';
require_once __DIR__ . '/Models/InvoiceModel.php';

class_alias(App\Models\InvoiceModel::class, 'InvoiceService');
