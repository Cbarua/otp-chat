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

require 'header.php'; 

?>
<!-- Fire the Pixel event for verified users only -->
<script>
  fbq('track', 'CompleteRegistration', {}, { eventID: '<?php echo $_SESSION['reg-id'] ?>' });
</script>

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