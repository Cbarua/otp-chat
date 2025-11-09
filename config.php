<?php

ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/log/php_errors.log');

require_once __DIR__ . "/vendor/autoload.php";
require_once __DIR__ . "/logger.php";
require_once __DIR__ . '/CapiService.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$otp_urls = [
    'ideamart' => json_decode($_ENV['IDEAMART'], true),
    'mspace' => json_decode($_ENV['MSPACE'], true)
];

# Case insesitive constants are deprecated notice
define("url", $otp_urls);

// --- CAPI Service Conditional Initialization (New Logic) ---
$pixelId = $_ENV['PIXEL_ID'] ?? null;
$capiToken = $_ENV['FBCAPI_TOKEN'] ?? null;

// Check if BOTH are available before instantiating the service
if ($pixelId && $capiToken) {
    $capi = new CapiService($pixelId, $capiToken);
} else {
    // If config is missing, set $capi to null.
    // This stops initialization errors and lets calling code check for null.
    $capi = null;
    error_log("CAPI initialization skipped: PIXEL_ID or FBCAPI_TOKEN is missing.");
}

?>