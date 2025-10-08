<?php
// CapiService.php
// Re-usable CAPI service class using official Facebook PHP Business SDK.
// Requires: composer require facebook/php-business-sdk

require_once __DIR__ . '/vendor/autoload.php';

use FacebookAds\Api;
use FacebookAds\Object\ServerSide\CustomData;
use FacebookAds\Object\ServerSide\Event;
use FacebookAds\Object\ServerSide\EventRequest;
use FacebookAds\Object\ServerSide\UserData;

/**
 * CapiService
 * - Initialize the Facebook SDK (server-side)
 * - Build user data for advanced matching
 * - Send Event with event deduplication support (event_id)
 *
 * Notes:
 * - We include client_ip_address and client_user_agent for better matching.
 * - We include _fbp and _fbc cookies if available (improves matching between browser pixel and server-side events).
 * - User identifiers (phone) are hashed with SHA-256 as required by Meta before sending.
 */
class CapiService
{
    private $pixelId;
    private $accessToken;
    private $apiInitialized = false;

    /**
     * Constructor
     * @param string $pixelId     Your Meta Pixel ID (string)
     * @param string $accessToken Your Conversions API access token (string)
     */
    public function __construct(string $pixelId, string $accessToken)
    {
        $this->pixelId = $pixelId;
        $this->accessToken = $accessToken;

        // Initialize SDK for server-side calls.
        // App ID and Secret are optional for CAPI calls; access token is required.
        Api::init(null, null, $this->accessToken);
        $this->apiInitialized = true;
    }

    /**
     * Hash a user identifier (phone/email) using SHA-256 in lowercase hex.
     * Meta expects SHA-256 hashes for these identifiers when sending server-side.
     *
     * @param string $value
     * @return string
     */
    private function hashIdentifier(string $value): string
    {
        // trim and lowercase for emails; for phone keep digits only (already normalized)
        $v = strtolower(trim($value));
        return hash('sha256', $v);
    }

    /**
     * Attempt to read or build an fbc value.
     *
     * @return string|null The formatted fbc value for CAPI, or null if unavailable.
     */
    private function getFbc()
    {
        // 1. Return existing _fbc cookie if present
        if (!empty($_COOKIE['_fbc'])) {
            return $_COOKIE['_fbc'];
        }

        // 2. Else, look for fbclid URL param to generate
        if (!empty($_GET['fbclid'])) {
            $fbclid = $_GET['fbclid'];
            $subdomainIndex = 1; // default for example.com
            $creationTimeMs = round(microtime(true) * 1000); // in ms
            $fbc = "fb.{$subdomainIndex}.{$creationTimeMs}.{$fbclid}";

            // Set cookie for 90 days
            setcookie('_fbc', $fbc, time() + 90 * 86400, '/');

            return $fbc;
        }

        // None found, return null
        return null;
    }

    /**
     * Builds a UserData object for the SDK.
     * Includes: hashed phone (ph), client_ip_address, client_user_agent, fbp, fbc
     *
     * @param string $phoneNormalized digits-only phone (e.g., 94771234567)
     * @return UserData
     */
    private function buildUserData(?string $phoneNormalized = null): UserData
    {
        $userData = new UserData();

        if (!empty($phoneNormalized)) {
            // Hash the phone before sending
            $hashedPhone = $this->hashIdentifier($phoneNormalized);
            $userData->setPhone($hashedPhone);
        }

        // Add client IP and user agent for improved matching
        $clientIp = $this->getClientIp();
        if ($clientIp) {
            $userData->setClientIpAddress($clientIp);
        }
        if (!empty($_SERVER['HTTP_USER_AGENT'])) {
            $userData->setClientUserAgent($_SERVER['HTTP_USER_AGENT']);
        }

        // Include fbp and fbc cookies if present (best practice to tie pixel & server events)
        // Prioritize fbp from session (from post), fallback to cookie
        $fbp = $_SESSION['fbp'] ?? $_COOKIE['_fbp'] ?? null;
        if (!empty($fbp)) {
            $userData->setFbp($fbp);
        }

        // Prioritize fbc from session, fallback to getFbc() which checks cookies and URL params
        $fbc = $_SESSION['fbc'] ?? $this->getFbc();
        if (!empty($fbc)) {
            $userData->setFbc($fbc);
        }

        return $userData;
    }

    /**
     * Send a single server-side event to Facebook using the SDK.
     *
     * IMPORTANT: event_id must be unique per logical event and must match the browser pixel eventID (if browser event is fired),
     *           to allow Facebook to deduplicate events between pixel and CAPI.
     *
     * @param string $eventName e.g. 'Lead' or 'CompleteRegistration'
     * @param string $eventId   Unique event id for deduplication (must be the same as browser eventID)
     * @param string $phoneNormalized normalized phone (digits-only, country code included)
     * @param string $eventSourceUrl page URL where event happened (optional)
     * @param string|null $testEventCode optional test_event_code for Test Events tool
     * @param array|null $customData optional custom data array (value, currency, etc.)
     * @return array decoded response from Facebook or throws exception on failure
     * @throws Exception
     */
    public function sendEvent(
        string $eventName,
        string $eventId,
        ?string $phoneNormalized = null,
        string $eventSourceUrl = '',
        ?string $testEventCode = null,
        ?array $customData = null
    ): array {
        if (!$this->apiInitialized) {
            throw new Exception('Facebook API not initialized.');
        }

        // Build user data for advanced matching
        $userData = $this->buildUserData($phoneNormalized);

        // Create event with required fields
        $event = new Event();
        $event->setEventName($eventName);
        $event->setEventTime(time()); // Unix timestamp in seconds (GMT/UTC)
        $event->setUserData($userData);
        $event->setEventSourceUrl($eventSourceUrl ?: ($this->getCurrentUrl()));
        $event->setActionSource('website');
        $event->setEventId($eventId); // IMPORTANT for deduplication

        // attach custom data if provided
        if ($customData !== null && is_array($customData)) {
            // Create a CustomData object, which is required by the SDK
            $customDataObject = new CustomData();

            // Set value and currency from the passed-in array
            if (isset($customData['value'])) {
                $customDataObject->setValue(floatval($customData['value']));
            }
            if (isset($customData['currency'])) {
                $customDataObject->setCurrency($customData['currency']);
            }

            // Pass the prepared object to the event
            $event->setCustomData($customDataObject);
        }

        // Build the event request
        $events = [$event];
        $eventRequest = new EventRequest($this->pixelId);
        $eventRequest->setEvents($events);

        // Add test_event_code if provided (use only for debugging)
        if ($testEventCode) {
            $eventRequest->setTestEventCode($testEventCode);
        }

        // Execute request
        $response = $eventRequest->execute();

        // Convert to array and return
        $decoded = json_decode($response, true);
        // Optional: log response for debugging (fbtrace_id etc.)
        file_put_contents(__DIR__ . '/log/fb_capi_resp.log', 
            date('c') . " event_id:$eventId" . 
            " userdata:" . json_encode($userData->normalize()) .
            " response:" . $response . 
            " url: $eventSourceUrl" . PHP_EOL, 
            FILE_APPEND
        );

        return $decoded;
    }

    /**
     * Utility: Get current page URL for event_source_url fallback.
     * @return string
     */
    private function getCurrentUrl(): string
    {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST'] ?? '';
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        return $protocol . $host . $uri;
    }

    /**
     * Utility: Get client IP for client_ip_address.
     * @return string
     */
    private function getClientIp(): string
    {
        $ip = $_SERVER['REMOTE_ADDR'];
        return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : '';
    }
}
