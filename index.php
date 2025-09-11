<?php

session_start();
# Necessary libraries
$root = __DIR__;
require_once $root. '/config.php';
require_once $root. '/logger.php';
require_once $root. '/request.php';
require_once $root. '/userInfo.php';
require_once $root. '/helpers.php';
require_once $root. '/webuserlog.php';

otplog('New log', true, true);

webuserlog();

$msg = [
	'invalid' => '<div class="alert alert-danger">වලංගු ජංගම දුරකථන අංකය ඇතුළත් කරන්න. උදා : 0772221234</div>',
	'registered' => '<div class="alert alert-success">ඔබ දැනටමත් ලියාපදිංචි වී ඇත!</div>',
	'error' => '<div class="alert alert-danger">කරුණාකර පසුව නැවත උත්සාහ කරන්න.</div>'
];

if (isset($_POST['mobile'])) {
	$m = formatNumberAndIdentifyPlatform($_POST['mobile']);
	if (empty($m)) {
		$message = $msg['invalid'];
	} else {
		// for fb capi api
		$_SESSION['phone'] = $m['capi'];
		otplog($_SESSION['phone']);

		$mobile = $m['telco'];

		otplog($mobile);
		webuserlog($mobile);
		
		$url = url[$m['platform']];

		$user_info = userInfo();
		$thisurl = getCurrentUrl();
		$metaData = [
			'client' => 'WEBAPP',
			'appCode' => $thisurl
		];

		$response = getOtp($url, $mobile, array_merge($metaData, $user_info));

		otplog($response);

		if (isset($response['referenceNo'])) {
			$_SESSION['l-id'] = "lead-" . uniqid();
			
			$capi->sendEvent(
				"Lead",
				$_SESSION['l-id'],
				$_SESSION['phone'],
				$thisurl,
				$_ENV['TEST_EVENT']
			);
						
			$params = '?refNo=' . $response['referenceNo'] . "&platform=" . $m['platform'];
			header("Location: otp.php$params");
			exit();
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
		<span class="form-text">ඔබගේ දුරකතන අංකය පහතින් ඇතුළත් කරන්න</span>
		<?php if (isset($message)) echo $message; ?>
		<input type="tel" name="mobile" placeholder="0700000000" maxlength="10" minlength="9" required>
		<input type="submit" value="Register">
		<span id="charging"><?php echo $_ENV['CHARGE'] ?></span>
	</form>
</section>
</div>
</body>
</html>