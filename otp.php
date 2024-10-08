<?php

# Necessary libraries
$root = __DIR__;
require_once $root. '/config.php';
require_once $root. '/logger.php';
require_once $root. '/request.php';

$msg = [
	'error' => '<div class="alert alert-danger">Enter valid pin number</div>',
	'invalid' => '<div class="alert alert-danger">Invalid OTP</div>',
	'try_again' => '<div class="alert alert-danger">Please try again later</div>'
];

$referenceNo = $_GET['refNo'];
$platform = $_GET['platform'];

#4
if (empty($referenceNo) && empty($platform)) {
	header('Location: index.php');
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

		# I need to add code for invalid otp
		if ($response['status'] === 'success') {	
			header('Location: thanks.php');	
		} else if ($response['status'] === 'Invalid OTP') {
			$error = $msg['invalid'];
		} else {
			$error = $msg['try_again'];
		}
	}
}

require 'header.php';

?>
<section class="form-section">
	<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'] . '?refNo=' . $referenceNo . "&platform=$platform");?>" method="post">
		<span class="form-title"><?php echo $msg_bd['otp-form-title'] ?></span>
		<span class="form-text"><?php echo $msg_bd['otp-form-text'] ?></span>
		<?php if (isset($error)) echo $error; ?>
		<input type="number" name="otp" placeholder="123456" required>
		<input type="submit" value="Verify">
		<span><?php echo $msg_bd['otp-pin-again'] ?></span>
	</form>
</section>
</div>
</body>
</html>