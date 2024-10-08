<?php

require_once __DIR__ . "/vendor/autoload.php";
require_once __DIR__ . "/logger.php";

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$otp_urls = [
    'bdapps' => $_ENV['BDAPPS']
];

$msg_bd = [
    'index-form-title' => "আপনার পছেন্দের মেয়ে বা ছেলের সাথে চ্যাট করুন",
    'index-form-text'  => "আপনার মোবাইল নম্বর প্রবেশ করুন",
    'index-charging'   => "Robi & Airtel Daily 2tk+tax",
    'otp-form-title'   => "ফোন নম্বর যাচাইকরণ",
    'otp-form-text'    => "আপনার ফোন নম্বরে প্রাপ্ত পিন নম্বরটি প্রবেশ করুন",
    'otp-pin-again'    => "আবার পিন নম্বর অনুরোধ করতে <a href='index.php'>এখানে ক্লিক করুন</a>",
    'otp-success'      => "আপনার রেজিস্ট্রেশন নিশ্চিত করার জন্য আপনার ফোন নম্বরে একটি টেক্সট বার্তা পাঠানো হবে"
];

# Case insesitive constants are deprecated notice
define("url", $otp_urls);

?>