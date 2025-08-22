<?php

require_once __DIR__ . "/helpers.php";

date_default_timezone_set('Asia/Colombo');

function getDbConnection(): ?SQLite3 {
    $dbFile = __DIR__ . '/log/userlog.sqlite';
    $logDir = dirname($dbFile);

    // Ensure directory exists
    if (!is_dir($logDir) && !mkdir($logDir, 0777, true)) {
        error_log("Failed to create log directory: $logDir");
        return null;
    }

    try {
        $db = new SQLite3($dbFile);
        $createTableSQL = "CREATE TABLE IF NOT EXISTS logs (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            visitor_id TEXT,
            lastaccesstime TEXT,
            ipaddress TEXT,
            phonenumber TEXT,
            useragent TEXT
        )";

        if (!$db->exec($createTableSQL)) {
            error_log("Failed to create logs table: " . $db->lastErrorMsg());
            return null;
        }

        return $db;
    } catch (Exception $e) {
        error_log("Database error: " . $e->getMessage());
        return null;
    }
}

function ensureVisitorId(): string {
    if (session_status() === PHP_SESSION_NONE) {
        if (!session_start()) {
            error_log("Failed to start session");
        }
    }

    if (empty($_SESSION['visitor_id'])) {
        $_SESSION['visitor_id'] = uniqid('v_', true);
    }

    return $_SESSION['visitor_id'];
}

function webuserlog($phoneNumber = null) {
    $db = getDbConnection();
    if (!$db) {
        error_log("webuserlog: Database connection failed.");
        return;
    }

    $visitorId = ensureVisitorId();
    $timestamp = date('Y-m-d H:i:s');
    $ip = getClientIp() ?? 'UNKNOWN_IP';
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN_UA';
    $phone = $phoneNumber;

    try {
        $stmt = $db->prepare("
            SELECT id, phonenumber FROM logs
            WHERE visitor_id = :vid AND ipaddress = :ip AND useragent = :ua
            ORDER BY id DESC
        ");
        $stmt->bindValue(':vid', $visitorId, SQLITE3_TEXT);
        $stmt->bindValue(':ip', $ip, SQLITE3_TEXT);
        $stmt->bindValue(':ua', $ua, SQLITE3_TEXT);
        $result = $stmt->execute();

        if (!$result) {
            throw new Exception("Query failed: " . $db->lastErrorMsg());
        }

        $foundExactMatch = false;
        $foundNullPhone = null;

        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            if ($row['phonenumber'] === $phone) {
                $foundExactMatch = $row['id']; // exact match already exists
                break;
            }
            if (empty($row['phonenumber'])) {
                $foundNullPhone = $row['id']; // candidate to update
            }
        }

        if ($foundExactMatch) {
            // Just update timestamp
            $stmtUpdate = $db->prepare("UPDATE logs SET lastaccesstime = :ts WHERE id = :id");
            $stmtUpdate->bindValue(':ts', $timestamp, SQLITE3_TEXT);
            $stmtUpdate->bindValue(':id', $foundExactMatch, SQLITE3_INTEGER);
            if (!$stmtUpdate->execute()) {
                throw new Exception("Timestamp update failed: " . $db->lastErrorMsg());
            }

        } elseif ($foundNullPhone) {
            // Update missing phone number
            $stmtUpdate = $db->prepare("UPDATE logs SET phonenumber = :phone, lastaccesstime = :ts WHERE id = :id");
            $stmtUpdate->bindValue(':phone', $phone, SQLITE3_TEXT);
            $stmtUpdate->bindValue(':ts', $timestamp, SQLITE3_TEXT);
            $stmtUpdate->bindValue(':id', $foundNullPhone, SQLITE3_INTEGER);
            if (!$stmtUpdate->execute()) {
                throw new Exception("Phone update failed: " . $db->lastErrorMsg());
            }

        } else {
            // No match found — insert new log
            $stmtInsert = $db->prepare("
                INSERT INTO logs (visitor_id, lastaccesstime, ipaddress, phonenumber, useragent)
                VALUES (:vid, :ts, :ip, :phone, :ua)
            ");
            $stmtInsert->bindValue(':vid', $visitorId, SQLITE3_TEXT);
            $stmtInsert->bindValue(':ts', $timestamp, SQLITE3_TEXT);
            $stmtInsert->bindValue(':ip', $ip, SQLITE3_TEXT);
            $stmtInsert->bindValue(':phone', $phone, SQLITE3_TEXT);
            $stmtInsert->bindValue(':ua', $ua, SQLITE3_TEXT);

            if (!$stmtInsert->execute()) {
                throw new Exception("Insert failed: " . $db->lastErrorMsg());
            }
        }

    } catch (Exception $e) {
        error_log("webuserlog error: " . $e->getMessage());
    } finally {
        $db->close();
    }
}

?>