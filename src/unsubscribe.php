<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/functions.php';

$message = '';
$submittedEmail = '';

// 1.request code
if (isset($_POST['unsubscribe_email'])) {
    $email = filter_var($_POST['unsubscribe_email'], FILTER_VALIDATE_EMAIL);
    if ($email) {
        $file   = __DIR__ . '/registered_emails.txt';
        $emails = file_exists($file)
            ? file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES)
            : [];
        $lower  = array_map('strtolower', $emails);
        if (!in_array(strtolower($email), $lower)) {
            $message = '<div class="message error">This email is not registered for XKCD subscription.</div>';
        } else {
            $code = generateVerificationCode();
            sendVerificationEmail($email, $code);
            $message = '<div class="message success">A confirmation code has been sent to your email.</div>';
            $submittedEmail = $email;
        }
    } else {
        $message = '<div class="message error">Invalid email address.</div>';
    }
}

// 2.confirm code
if (isset($_POST['verification_code'], $_POST['email_to_unsub'])) {
    $email = $_POST['email_to_unsub'];
    $code  = $_POST['verification_code'];
    if (verifyCode($email, $code)) {
        if (unsubscribeEmail($email)) {
            $message = '<div class="message success">You have been unsubscribed successfully.</div>';
        } else {
            $message = '<div class="message error">This email was not subscribed.</div>';
        }
    } else {
        $message = '<div class="message error">Verification failed. Incorrect code.</div>';
        $submittedEmail = $email;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Unsubscribe from XKCD</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
  <div class="container">
    <h1>Unsubscribe from XKCD Comics</h1>
    <?php echo $message; ?>

    <form method="POST">
      <input
        type="email"
        name="unsubscribe_email"
        required
        placeholder="Enter your email"
        value="<?php echo htmlspecialchars($submittedEmail); ?>"
      >
      <button id="submit-unsubscribe">Send Verification Code</button>
    </form>

    <form method="POST">
      <input
        type="hidden"
        name="email_to_unsub"
        value="<?php echo htmlspecialchars($submittedEmail); ?>"
      >
      <input
        type="text"
        name="verification_code"
        maxlength="6"
        required
        placeholder="Enter confirmation code"
      >
      <button id="submit-verification">Confirm Unsubscription</button>
    </form>
  </div>
</body>
</html>
