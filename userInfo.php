<?php

require_once __DIR__ . "/helpers.php";

function userInfo() {
    $ip = getClientIp();
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

    $os = 'Unknown OS';
    $device = 'Unknown Device';

    // Detect OS
    if (preg_match('/Android\s([\d\.]+)/i', $userAgent, $match)) {
        $os = 'Android ' . $match[1];
    } elseif (preg_match('/iPhone OS\s([\d_]+)/i', $userAgent, $match)) {
        $os = 'iOS ' . str_replace('_', '.', $match[1]);
    } elseif (preg_match('/Windows NT\s([\d\.]+)/i', $userAgent, $match)) {
        $os = 'Windows ' . $match[1];
    } elseif (stripos($userAgent, 'Mac OS X') !== false) {
        $os = 'macOS';
    } elseif (stripos($userAgent, 'Linux') !== false) {
        $os = 'Linux';
    }

    // Detect Device
    if (preg_match('/Android\s[\d\.]+;\s([^;)\[]+)/i', $userAgent, $match)) {
        $device = trim($match[1]);
    } elseif (preg_match('/\((iPhone|iPad|iPod)/i', $userAgent, $match)) {
        $device = $match[1];
    } elseif (preg_match('/\(([^;]+);/i', $userAgent, $match)) {
        $device = trim($match[1]);
    }

    return [
        'os' => $os,
        'device' => $device,
        'ip' => $ip,
        'useragent' => $userAgent
    ];
}
