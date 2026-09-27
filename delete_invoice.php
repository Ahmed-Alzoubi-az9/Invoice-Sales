<?php

require_once __DIR__ . '/Core/bootstrap.php';

(new App\Controllers\InvoiceController())->deleteLegacy();
