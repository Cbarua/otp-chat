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
			// Store fbp and fbc from form post into session for later CAPI calls
			$_SESSION['fbp'] = $_POST['fbp'] ?? null;
			$_SESSION['fbc'] = $_POST['fbc'] ?? null;
			
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
	<form id="leadForm" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
		<span class="form-text">ඔබගේ දුරකතන අංකය පහතින් ඇතුළත් කරන්න</span>
		<?php if (isset($message)) echo $message; ?>
		<!-- Error alert (hidden by default) -->
		<div id="phoneError" class="alert alert-danger" style="display:none;">
			වලංගු ජංගම දුරකථන අංකය ඇතුළත් කරන්න. උදා : 0772221234
		</div>
		<input type="hidden" id="fbp" name="fbp" value="">
		<input type="hidden" id="fbc" name="fbc" value="">
		<input type="tel" id="mobile" name="mobile" placeholder="0700000000" maxlength="10" minlength="9" required>
		<input type="submit" value="Register">
		<span id="charging"><?php echo $_ENV['CHARGE'] ?></span>
	</form>
</section>
</div>
</body>
<script>
	document.getElementById("leadForm").addEventListener("submit", function(e) {
		const phoneInput = document.getElementById('mobile');
		const errorDiv = document.getElementById('phoneError');
		const phone = phoneInput.value.trim();

		// Only accept 07XXXXXXXX (Sri Lankan mobile numbers)
		const regex = /^07\d{8}$/;

		if (!regex.test(phone)) {
			e.preventDefault(); // stop submission
			errorDiv.style.display = "block";
			phoneInput.focus();
			return false;
		}

		// Helper to read a cookie value by name
        function getCookie(name) {
            const value = `; ${document.cookie}`;
            const parts = value.split(`; ${name}=`);
            if (parts.length === 2) return parts.pop().split(';').shift();
            return '';
        }

        // Populate hidden fields with cookie values before submitting
        document.getElementById('fbp').value = getCookie('_fbp');
        document.getElementById('fbc').value = getCookie('_fbc');

		// If valid → hide error, allow submit
		errorDiv.style.display = "none";
		return true;
	});
</script>
</html>