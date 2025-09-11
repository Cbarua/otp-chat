<?php

ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/log/php_errors.log');

require_once __DIR__ . "/vendor/autoload.php";
require_once __DIR__ . "/logger.php";
require_once __DIR__ . '/CapiService.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$otp_urls = [
    'ideamart' => $_ENV['IDEAMART'],
    'mspace' => $_ENV['MSPACE']
];

# Case insesitive constants are deprecated notice
define("url", $otp_urls);

$capi = new CapiService($_ENV['PIXEL_ID'], $_ENV['FBCAPI_TOKEN']);

?>