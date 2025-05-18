<?php
require_once __DIR__ . '/functions.php';
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['unsubscribe_email'])) {
        $email = filter_var($_POST['unsubscribe_email'], FILTER_VALIDATE_EMAIL);
        if ($email) {
            $code = generateVerificationCode();
            sendVerificationEmail($email, $code);
            $message = 'A confirmation code has been sent to your email.';
        } else {
            $message = 'Invalid email address.';
        }
    } elseif (isset($_POST['verification_code'], $_POST['email_to_unsub'])) {
        $email = $_POST['email_to_unsub'];
        $code = $_POST['verification_code'];
        if (verifyCode($email, $code)) {
            unsubscribeEmail($email);
            $message = 'You have been unsubscribed.';
        } else {
            $message = 'Verification failed. Incorrect code.';
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><title>Unsubscribe</title></head>
<body>
<h1>Unsubscribe from XKCD Comics</h1>
<p><?php echo $message; ?></p>
<form method="POST" action="">
    <input type="email" name="unsubscribe_email" required placeholder="Enter your email">
    <button id="submit-unsubscribe">Unsubscribe</button>
</form>
<form method="POST" action="">
    <input type="hidden" name="email_to_unsub" value="<?php echo htmlspecialchars($_POST['unsubscribe_email'] ?? ''); ?>">
    <input type="text" name="verification_code" maxlength="6" required placeholder="Enter confirmation code">
    <button id="submit-verification">Verify</button>
</form>
</body>
</html>