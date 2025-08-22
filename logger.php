<?php

date_default_timezone_set('Asia/Colombo');

function otplog($data, $isDate = true, $overwrite = false){
    $logfile = __DIR__ . '/log/otp.log';
    $date = $isDate ? "[".date('D M j G:i:s T Y')."]\n" : "";

    ob_start();
    var_dump($data);
    $data = ob_get_clean();

    $flags = $overwrite ? 0 : FILE_APPEND;
    file_put_contents($logfile, $date . $data . "\n\n", $flags | LOCK_EX);
}

function weblog($data) {
    ob_start();
    var_dump($data);
    $data = ob_get_clean();
    $data = nl2br($data);
    echo $data."<br><br>";
}

?>