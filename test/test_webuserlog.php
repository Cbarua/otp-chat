<?php

require_once dirname(__DIR__) . '/webuserlog.php';
require_once dirname(__DIR__) . '/logger.php';

function testWebUserLogInsertNew() {
    weblog("Test Insert New Record: ");
    webuserlog('1234567890');

    $db = getDbConnection();
    $result = $db->query("SELECT * FROM logs WHERE phonenumber = '1234567890'");

    if ($result) {
        $row = $result->fetchArray(SQLITE3_ASSOC);
        if ($row && $row['phonenumber'] === '1234567890') {
            weblog("PASS\n");
        } else {
            weblog("FAIL - Record not found or incorrect\n");
        }
    } else {
        weblog("FAIL - Query error\n");
    }
}

function testWebUserLogUpdateTimestamp() {
    weblog("Test Update Timestamp for existing phone: ");
    
    $db = getDbConnection();

    // Insert a record with phone '1234567889' and old timestamp
    $visitorId = ensureVisitorId();
    $ip = getClientIp();
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN_UA';
    $oldTimestamp = date('Y-m-d H:i:s', time() - 3600); // 1 hour ago
    $db->exec("INSERT INTO logs (visitor_id, lastaccesstime, ipaddress, phonenumber, useragent) 
               VALUES ('$visitorId', '$oldTimestamp', '$ip', '1234567889', '$ua')");

    // Call function that should update timestamp
    webuserlog('1234567889');

    // Check if timestamp updated (greater than oldTimestamp)
    $result = $db->query("SELECT lastaccesstime FROM logs WHERE phonenumber = '1234567889' ORDER BY id DESC LIMIT 1");
    if ($result) {
        $row = $result->fetchArray(SQLITE3_ASSOC);
        if ($row && $row['lastaccesstime'] > $oldTimestamp) {
            weblog("PASS\n");
        } else {
            weblog("FAIL - Timestamp not updated\n");
        }
    } else {
        weblog("FAIL - Query error\n");
    }
}

function testWebUserLogUpdatePhoneNumber() {
    weblog("Test Update Missing Phone Number: ");
    
    $db = getDbConnection();
    $visitorId = ensureVisitorId();
    $ip = getClientIp();
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN_UA';
    
    // Insert a record with empty phone number
    $db->exec("INSERT INTO logs (visitor_id, lastaccesstime, ipaddress, phonenumber, useragent) 
               VALUES ('$visitorId', datetime('now', '-1 hour'), '$ip', null, '$ua')");

    // Call function with phone number to update empty phone field
    webuserlog('0987654321');

    // Check if record with empty phone updated with new phone
    $result = $db->query("SELECT phonenumber FROM logs WHERE visitor_id = '$visitorId' AND phonenumber = '0987654321'");
    if ($result) {
        $row = $result->fetchArray(SQLITE3_ASSOC);
        if ($row && $row['phonenumber'] === '0987654321') {
            weblog("PASS\n");
        } else {
            weblog("FAIL - Phone number not updated\n");
        }
    } else {
        weblog("FAIL - Query error\n");
    }
}

function testWebUserLogInsertNewDifferentPhone() {
    weblog("Test Insert New with Different Phone Number: ");
    
    $db = getDbConnection();
    $visitorId = ensureVisitorId();

    // Make sure a record with phone '5555555555' does NOT exist yet for this visitor
    $db->exec("DELETE FROM logs WHERE visitor_id = '$visitorId' AND phonenumber = '5555555555'");

    webuserlog('5555555555');

    $result = $db->query("SELECT phonenumber FROM logs WHERE visitor_id = '$visitorId' AND phonenumber = '5555555555'");
    if ($result) {
        $row = $result->fetchArray(SQLITE3_ASSOC);
        if ($row && $row['phonenumber'] === '5555555555') {
            weblog("PASS\n");
        } else {
            weblog("FAIL - New phone record not inserted\n");
        }
    } else {
        weblog("FAIL - Query error\n");
    }
}

// Run all tests
function runAllTests() {
    testWebUserLogInsertNew();
    testWebUserLogUpdateTimestamp();
    testWebUserLogUpdatePhoneNumber();
    testWebUserLogInsertNewDifferentPhone();
}

runAllTests();

