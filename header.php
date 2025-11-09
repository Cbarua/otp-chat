<?php

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/helpers.php';

$pg_view_id = "pgview-" . uniqid(); // unique event_id
$thisurl = getCurrentUrl();
$testEventCode = $_ENV['TEST_EVENT'] ?? null;

$capi->sendEvent(
    'PageView',
    $pg_view_id,
    null,
    $thisurl,
    $testEventCode
);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="assets/images/icons/two-hearts.png" type="image/x-icon">
    <link rel="stylesheet" href="assets/css/blog.css">
    <!-- Meta Pixel Code -->
    <script>
        ! function(f, b, e, v, n, t, s) {
            if (f.fbq) return;
            n = f.fbq = function() {
                n.callMethod ?
                    n.callMethod.apply(n, arguments) : n.queue.push(arguments)
            };
            if (!f._fbq) f._fbq = n;
            n.push = n;
            n.loaded = !0;
            n.version = '2.0';
            n.queue = [];
            t = b.createElement(e);
            t.async = !0;
            t.src = v;
            s = b.getElementsByTagName(e)[0];
            s.parentNode.insertBefore(t, s)
        }(window, document, 'script',
            'https://connect.facebook.net/en_US/fbevents.js');
        fbq('init', '<?php echo $_ENV['PIXEL_ID'] ?>');
        <?php echo fbq_track('PageView', $pg_view_id, $testEventCode); ?>
    </script>
    <noscript><img height="1" width="1" style="display:none"
            src="https://www.facebook.com/tr?id=<?php echo $_ENV['PIXEL_ID'] ?>&ev=PageView&noscript=1" /></noscript>
    <!-- End Meta Pixel Code -->
    <title>Welcome</title>
</head>
<body>
    <div class="box-container">
        <section class="img-section">
            <div class="img-container">
                <img src="<?php echo $_ENV['IMG_URL']; ?>" alt="<?php echo $_ENV['IMG_ALT']; ?>">
            </div>
        </section>