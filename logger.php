<?php

date_default_timezone_set('Asia/Colombo');

// variable parameter ...operator
function var_dump_ret($mixed = null) {
    ob_start();
    var_dump($mixed);
    $content = ob_get_contents();
    ob_end_clean();
    return $content;
}

function otplog($data, $isDate = true){
    $root = __DIR__;
    $logfile = $root . '/log/otp.log';
    $date = $isDate ? "[".date('D M j G:i:s T Y')."]\n" : "";
    $method = 'a';

    $data = var_dump_ret($data);
    
    strpos($data, 'New log') !== false ? $method = 'w': $method = 'a';
    
    $file = fopen($logfile, $method);
    fwrite($file, $date . $data . "\n\n");
    fclose($file);
}

function getClientIp() {
    $ip = $_SERVER['REMOTE_ADDR'];

    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        // Extract first IP in the list
        $forwardedIps = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        $ip = trim($forwardedIps[0]);
    }

    return $ip;
}

function webuserlog($phoneNumber = "") {
    $root = __DIR__;
    
    // Create the log directory if it doesn't exist
    $logDir = $root . '/log/userlog';
    if (!file_exists($logDir)) {
        mkdir($logDir, 0777, true);
    }

    // Use current date to name the log file, e.g., userlog_2025-06-06.csv
    $logfile = $logDir . '/userlog_' . date('Y-m-d') . '.csv';

    if ($phoneNumber) {
        appendToLastLine($logfile, $phoneNumber);
        return;
    }

    // Gather metadata
    $entryTime = date('Y-m-d H:i:s');
    $ip = getClientIp() ?? 'UNKNOWN_IP';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN_UA';

    // Check if file exists to determine whether to write headers
    $isNewFile = !file_exists($logfile);

    // Open file in append mode
    $file = fopen($logfile, 'a');

    // Write headers if it's a new file
    if ($isNewFile) {
        fputcsv($file, ['timestamp', 'ipaddress', 'phonenumber', 'useragent']);
    }

    // Write the log entry
    fputcsv($file, [$entryTime, $ip, "", $userAgent]);

    fclose($file);
}

function appendToLastLine($logfile, $phoneNumber) {
    $lines = file($logfile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    if (count($lines) <= 1) {
        // No data rows to update
        return;
    }

    $lastIndex = count($lines) - 1;

    // Parse the last row into columns
    $lastRow = str_getcsv($lines[$lastIndex]);

    // Ensure the array has at least 4 columns
    while (count($lastRow) < 4) {
        $lastRow[] = '';
    }

    // If phone number already exists at index 2, skip
    if (isset($lastRow[2]) && trim($lastRow[2]) !== '') {
        return;
    }

    // Insert the phone number at index 2 (overwrite or add)
    $lastRow[2] = $phoneNumber;

    // Rebuild the row into CSV format
    $lines[$lastIndex] = '"' . implode('","', array_map('addslashes', $lastRow)) . '"';

    // Rewrite the file
    file_put_contents($logfile, implode("\n", $lines));
}

function weblog($data) {
    $data = var_dump_ret($data);
    $data = nl2br($data);
    echo $data."<br><br>";
}

?>