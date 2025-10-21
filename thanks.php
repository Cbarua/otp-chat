<?php 

session_start();
if (!isset($_SESSION['reg-id'])) {
  // User did not complete OTP, redirect away or show error
  header('Location: index.php');
  exit;
}

$root = __DIR__;
require_once $root. '/config.php';
require_once $root. '/logger.php';
require_once $root. '/helpers.php';

require 'header.php'; 

// Add Manual Advanced Matching by re-initializing the pixel with the user's phone number hash
if (!empty($_SESSION['phone'])) {
  $phone = $_SESSION['phone'];
  echo "<script>fbq('init', {$_ENV['PIXEL_ID']}, { ph: '{$phone}' });</script>";
}

// Fire the Pixel event for verified users only
echo "<script>" . fbq_track(
  'CompleteRegistration', 
  $_SESSION['reg-id'], 
  $testEventCode, 
  [
    'currency' => 'USD', 
    'value' => $_SESSION['value'] ?? 0.01, // Use float instead of string
  ]
) . "</script>";
?>

<?php
// Optional: unset the session flag so page refresh won't resend event
unset($_SESSION['reg-id']);
unset($_SESSION['l-id']);
?>

<section>
	<div class="alert alert-success">ඔබගේ ලියාපදිංචිය තහවුරු කිරීමට ඔබගේ දුරකතන අංකයට කෙටි පණිවිඩයක් මඟින් දැනුම් දෙනු ලැබේ</div>
</section>
</div>
</body>
</html>