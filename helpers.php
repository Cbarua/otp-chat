<?php

function getClientIp() {
    $ip = $_SERVER['REMOTE_ADDR'];

    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        // Extract first IP in the list
        $forwardedIps = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        $ip = trim($forwardedIps[0]);
    }

    return $ip;
}

function formatNumberAndIdentifyPlatform(string $rawPhone, string $countryCode = 'LK'): array
{
    // Country-specific regex patterns
    $patterns = [
        'LK' => '/^0?7\d{8}$/',     // Sri Lanka mobile numbers (07XXXXXXXX)
        'BD' => '/^0?1(6|8)\d{8}$/' // Bangladesh (01XXXXXXXXX) Only Robi & Airtel
    ];

    // Try to match the number
    if (!isset($patterns[$countryCode]) || !preg_match($patterns[$countryCode], $rawPhone, $match)) {
        return [];
    }

    // Normalize: remove leading 0, add country code
    $normalized = ltrim($rawPhone, '0');
    $capi = ($countryCode === 'LK' ? '94' : '880') . $normalized;
    $telco = 'tel:' . $capi;

    // Detect platform by operator prefix
    $platform = 'ideamart';

    if ($countryCode === 'BD') {
		$platform = 'bdapps';
    }
    elseif ($countryCode === 'LK' && in_array($normalized[1], ['0', '1'])) {
		$platform = 'mspace';
    }

    return [
        'telco'     => $telco,
        'capi'      => $capi,
        'platform'  => $platform
    ];
}

/**
 * Utility: Get current page URL for event_source_url fallback.
 * @return string
 */
function getCurrentUrl(): string
{
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $uri = $_SERVER['REQUEST_URI'] ?? '';
    return $protocol . $host . $uri;
}

function fbq_track(string $eventName, string $eventId, ?string $test_event = null, array $customData = []): string {
    // Build the options array
    $options = ['eventID' => $eventId];

    if (!empty($test_event)) {
        $options['testEventCode'] = $test_event;
    }

    // Convert to JSON safely
    $options_json = json_encode($options, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    // Encode customData and options as JS objects
    $customData_json = !empty($customData) 
        ? json_encode($customData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) 
        : '{}';

    return "fbq('track', " 
        . json_encode($eventName) . ", "
        . $customData_json . ", "
        . $options_json . ");";
}
