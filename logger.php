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

function weblog($data) {
    $data = var_dump_ret($data);
    $data = nl2br($data);
    echo $data."<br><br>";
}

?>