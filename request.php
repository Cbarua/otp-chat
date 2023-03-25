<?php

class Request
{

    public static function sendRequest($jsonStream, $url)
    {

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonStream);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $res = curl_exec($ch);
        curl_close($ch);
        return $res;
    }
}

function getOtp($url, $subscriberId, $metaData) {
    $url = $url . 'getOtp.php';
    $arrayField = array(
        "subscriberId" => $subscriberId,
        "applicationMetaData" => $metaData
    );

    $jsonObjectFields = json_encode($arrayField);
    $res = Request::sendRequest($jsonObjectFields, $url);
    $response = json_decode($res, true);
    return $response;
}

function verifyOtp($url, $referenceNo, $otp) {
    $url = $url . 'verifyOtp.php';
    $arrayField = array(
        "referenceNo" => $referenceNo,
        "otp" => $otp
    );

    $jsonObjectFields = json_encode($arrayField);
    $res = Request::sendRequest($jsonObjectFields, $url);
    $response = json_decode($res, true);
    return $response;
}

?>