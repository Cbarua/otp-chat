<?php

# Necessary libraries
$root = __DIR__;
require_once $root. '/config.php';
require_once $root. '/logger.php';
require_once $root. '/request.php';

otplog('New log');

$msg = [
	'error' => '<div class="alert alert-danger">Enter valid mobile number</div>',
	'operator_error' => '<div class="alert alert-danger">Operator not supported. Robi & Airtel customers only.</div>',
	'registered' => '<div class="alert alert-success">You are already registered!</div>'
];

if (isset($_POST['mobile'])) {
	$regex_mobile = '/^01\d{9}$/m';
	preg_match($regex_mobile, $_POST['mobile'], $mobile);
	$mobile = 'tel:88'. $mobile[0];
	otplog($mobile);
	
	if (empty($mobile)) {
		$message = $msg['error'];
	}
	// 016 and 018 - Robi (016 is mainly used for Airtel)
	elseif (!(in_array($mobile[8], ['6', '8']))) {
		$message = $msg['operator_error'];
	}
	else {
		$platform = 'bdapps';
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
		<span class="form-title"><?php echo $msg_bd['index-form-title'] ?></span>
		<span class="form-text"><?php echo $msg_bd['index-form-text'] ?></span>
		<?php if (isset($message)) echo $message; ?>
		<input type="tel" name="mobile" placeholder="01000000000" maxlength="11" minlength="11" required>
		<input type="submit" value="Register">
		<span id="charging"><?php echo $msg_bd['index-charging'] ?></span>
	</form>
</section>
</div>
</body>
</html>