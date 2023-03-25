<?php

require_once __DIR__ . "/vendor/autoload.php";
require_once __DIR__ . "/logger.php";

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$otp_urls = [
    'ideamart' => $_ENV['IDEAMART'],
    'mspace' => $_ENV['MSPACE']
];

# Case insesitive constants are deprecated notice
define("url", $otp_urls);

?>