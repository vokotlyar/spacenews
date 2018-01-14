<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/application/errors/error_handler.php';
set_error_handler("error_handler", E_ALL);
error_reporting(0);

$front = Controllers\FrontController::getInstance();
$front->route();
echo $front->getBody();
