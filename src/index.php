<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/functions.php';

$message = '';
$submittedEmail = '';

// 1. request code
if (isset($_POST['email'])) {
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    if ($email) {
        $code = generateVerificationCode();
        sendVerificationEmail($email, $code);
        $message = '<div class="message success">Verification code sent to your email.</div>';
        $submittedEmail = $email;
    } else {
        $message = '<div class="message error">Invalid email address.</div>';
    }
}

// 2. confirm code
if (isset($_POST['verification_code'], $_POST['email_to_verify'])) {
    $email = $_POST['email_to_verify'];
    $code  = $_POST['verification_code'];
    if (verifyCode($email, $code)) {
        $added = registerEmail($email);
        if ($added) {
            $message = '<div class="message success">Email verified and registered successfully!</div>';
        } else {
            $message = '<div class="message error">You have already subscribed.</div>';
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
  <title>Subscribe to XKCD</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
  <div class="container">
    <h1>Subscribe to XKCD Comics</h1>
    <?php echo $message; ?>

    <form method="POST">
      <input
        type="email"
        name="email"
        required
        placeholder="Enter your email"
        value="<?php echo htmlspecialchars($submittedEmail); ?>"
      >
      <button id="submit-email">Send Verification Code</button>
    </form>

    <form method="POST">
      <input
        type="hidden"
        name="email_to_verify"
        value="<?php echo htmlspecialchars($submittedEmail); ?>"
      >
      <input
        type="text"
        name="verification_code"
        maxlength="6"
        required
        placeholder="Enter verification code"
      >
      <button id="submit-verification">Confirm Verification</button>
    </form>
  </div>
</body>
</html>
