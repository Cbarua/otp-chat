<?php

# Necessary libraries
$root = __DIR__;
require_once $root. '/config.php';
require_once $root. '/logger.php';
require_once $root. '/request.php';

otplog('New log');

$msg = [
	'error' => '<div class="alert alert-danger">Enter valid mobile number</div>',
	'registered' => '<div class="alert alert-success">You are already registered!</div>'
];

if (isset($_POST['mobile'])) {
	$regex_mobile = '/^07\d{8}$/m';
	preg_match($regex_mobile, $_POST['mobile'], $mobile);
	
	if (empty($mobile)) {
		$message = $msg['error'];
	} else {
		$platform = 'ideamart';
		$mobile = 'tel:94'. substr($mobile[0], 1);
		otplog($mobile);
		
		if (in_array($mobile[7], ['0', '1'])) {
			$platform = 'mspace';
		}

		$url = url[$platform];

		# I'm gonna add this later
		// $clientInfo = Detect::systemInfo();
		$metaData = [
			'client' => 'MOBILEAPP',
			'device' => 'Samsung S10',
			'os' => 'android 8',
			'appCode' => "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"
		];

		$response = getOtp($url, $mobile, $metaData);

		otplog($response);

		if (isset($response['referenceNo'])) {
			$params = '?refNo=' . $response['referenceNo'] . "&platform=$platform";
			header("Location: otp.php$params");
		} elseif ($response['statusDetail'] === 'user already registered') {
			$message = $msg['registered'];
		} else {
			$message = $msg['error'];
		}
	}
}

require 'header.php';

?>
<section class="form-section">
	<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
		<span class="form-title">කැමැති කෙල්ලෙක් හෝ කොල්ලෙක් සොයාගන්න</span>
		<span class="form-text">ඔබගේ දුරකතන අංකය ඇතුළත් කරන්න</span>
		<?php if (isset($message)) echo $message; ?>
		<input type="tel" name="mobile" placeholder="0700000000" maxlength="10" minlength="10" required>
		<input type="submit" value="Register">
		<span id="charging">Dialog, Hutch, Airtel, Mobitel Daily Rs <?php echo $_ENV['CHARGE'] ?>+tax</span>
	</form>
</section>
</div>
</body>
</html>