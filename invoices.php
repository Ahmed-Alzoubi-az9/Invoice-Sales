<?php

require_once __DIR__ . '/Core/bootstrap.php';

$pageController = new App\Controllers\PageController();
$pageController->list();
