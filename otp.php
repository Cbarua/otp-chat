<?php

session_start();
# Necessary libraries
$root = __DIR__;
require_once $root. '/config.php';
require_once $root. '/logger.php';
require_once $root. '/request.php';
require_once $root. '/helpers.php';

$msg = [
	'error' => '<div class="alert alert-danger">Enter valid pin number</div>',
	'invalid' => '<div class="alert alert-danger">Invalid OTP</div>',
	'try_again' => '<div class="alert alert-danger">Please try again later</div>'
];

$referenceNo = $_GET['refNo'];
$platform = $_GET['platform'];

#4
if (empty($referenceNo) || empty($platform)) {
	header('Location: index.php');
	exit;
}

if (isset($_POST['otp'])) {
	$regex_otp = '/^\d{6}$/m';
	preg_match($regex_otp, $_POST['otp'], $otp);

	if (empty($otp)) {
		$error = $msg['error'];
	} else {
		$otp = $otp[0];
		$response = verifyOtp(url[$platform], $referenceNo, $otp);
		
		otplog($response);

		if ($response['status'] === 'success') {	
			$_SESSION['reg-id'] = "reg-" . uniqid();
			$thisurl = getCurrentUrl();

			$capi->sendEvent(
				"CompleteRegistration",
				$_SESSION['reg-id'],
				$_SESSION['phone'],  // store phone from first step
				$thisurl,
				$_ENV['TEST_EVENT'],
				['currency' => 'USD', 'value' => 0.01]
			);
			
			header('Location: thanks.php');
			exit();
		} else if ($response['status'] === 'Invalid OTP') {
			$error = $msg['invalid'];
		} else {
			$error = $msg['try_again'];
		}
	}
}

require 'header.php';

// fb pixel code is in header
if (isset($_SESSION['l-id'])) {
    // Add Manual Advanced Matching by re-initializing the pixel with the user's phone number hash
	$phone = $_SESSION['phone'];
    if (!empty($phone)) {
        echo "<script>fbq('init', {$_ENV['PIXEL_ID']}, { ph: '{$phone}' });</script>";
    }
    // User entered phone number and server event sent, now sending pixel event
	echo "<script>fbq('track', 'Lead', { ph: '{$phone}'}, { eventID: `{$_SESSION['l-id']}` });</script>";
	unset($_SESSION['l-id']);
}
?>
<section class="form-section">
	<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'] . '?refNo=' . $referenceNo . "&platform=$platform");?>" method="post">
		<span class="form-title">දුරකතන අංකය තහවුරු කිරීම</span>
		<span class="form-text">ඔබගේ දුරකතන අංකය වෙත ලැබුනු PIN අංකය ඇතුළත් කරන්න</span>
		<?php if (isset($error)) echo $error; ?>
		<input type="number" name="otp" placeholder="123456" required>
		<input type="submit" value="Verify">
		<span>නැවත PIN අංකය ඉල්ලීමට <a href="index.php">මෙතන ඔබන්න.</a></span>
	</form>
</section>
</div>
</body>
</html>