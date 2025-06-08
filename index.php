<?php

# Necessary libraries
$root = __DIR__;
require_once $root. '/config.php';
require_once $root. '/logger.php';
require_once $root. '/request.php';
require_once $root. '/userInfo.php';
require_once $root. '/webuserlog.php';

otplog('New log', true, true);

webuserlog();

$msg = [
	'invalid' => '<div class="alert alert-danger">වලංගු ජංගම දුරකථන අංකය ඇතුළත් කරන්න. උදා : 0772221234</div>',
	'registered' => '<div class="alert alert-success">ඔබ දැනටමත් ලියාපදිංචි වී ඇත!</div>',
	'error' => '<div class="alert alert-danger">කරුණාකර පසුව නැවත උත්සාහ කරන්න.</div>'
];

if (isset($_POST['mobile'])) {
	$regex_mobile = '/^0?7\d{8}$/';
	preg_match($regex_mobile, $_POST['mobile'], $mobile);
	
	if (empty($mobile)) {
		$message = $msg['invalid'];
	} else {
		$platform = 'ideamart';
		$mobile = 'tel:94'. ($mobile[0][0] === '0' ? substr($mobile[0], 1) : $mobile[0]);

		otplog($mobile);
		webuserlog($mobile);
		
		if (in_array($mobile[7], ['0', '1'])) {
			$platform = 'mspace';
		}

		$url = url[$platform];

		$user_info = userInfo();
		$metaData = [
			'client' => 'WEBAPP',
			'appCode' => "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"
		];

		$response = getOtp($url, $mobile, array_merge($metaData, $user_info));

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
		<input type="tel" name="mobile" placeholder="0700000000" maxlength="10" minlength="9" required>
		<input type="submit" value="Register">
		<span id="charging">Dialog, Hutch, Airtel, Mobitel Daily Rs <?php echo $_ENV['CHARGE'] ?>+tax</span>
	</form>
</section>
</div>
</body>
</html>