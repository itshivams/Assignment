<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Generate a 6-digit numeric verification code.
 */
function generateVerificationCode(): string {
    return str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
}

/**
 * Send a verification code to an email.
 */
function sendVerificationEmail(string $email, string $code): bool {
    $subject = "Your Verification Code";
    $message = "<p>Your verification code is: <strong>{$code}</strong></p>";
    $headers  = "MIME-Version: 1.0\r\n"
              . "Content-type:text/html;charset=UTF-8\r\n"
              . "From: no-reply@example.com\r\n";
    $_SESSION['verification_codes'][$email] = $code;
    return mail($email, $subject, $message, $headers);
}

/**
 * Register an email by storing it in a file.
 */
function registerEmail(string $email): bool {
    $file = __DIR__ . '/registered_emails.txt';
    $fp = fopen($file, 'c+');
    if (!$fp) return false;
    flock($fp, LOCK_EX);
    // read existing
    $emails = [];
    while (($line = fgets($fp)) !== false) {
        $emails[] = trim($line);
    }
    $lower = array_map('strtolower', $emails);
    if (in_array(strtolower($email), $lower)) {
        flock($fp, LOCK_UN);
        fclose($fp);
        return false;
    }
    // append
    fwrite($fp, $email . "\n");
    flock($fp, LOCK_UN);
    fclose($fp);
    return true;
}

/**
 * Unsubscribe an email by removing it from the list.
 */
function unsubscribeEmail(string $email): bool {
    $file = __DIR__ . '/registered_emails.txt';
    if (!file_exists($file)) return false;
    $fp = fopen($file, 'r+');
    if (!$fp) return false;
    flock($fp, LOCK_EX);
    $emails = [];
    while (($line = fgets($fp)) !== false) {
        $emails[] = trim($line);
    }
    $lower = array_map('strtolower', $emails);
    $target = strtolower($email);
    if (!in_array($target, $lower)) {
        flock($fp, LOCK_UN);
        fclose($fp);
        return false;
    }
    // filter
    $filtered = array_filter($emails, fn($e) => strtolower($e) !== $target);
    // truncate & write
    ftruncate($fp, 0);
    rewind($fp);
    foreach ($filtered as $e) {
        fwrite($fp, $e . "\n");
    }
    flock($fp, LOCK_UN);
    fclose($fp);
    return true;
}

/**
 * Verify a code submitted by the user.
 */
function verifyCode(string $email, string $code): bool {
    if (isset($_SESSION['verification_codes'][$email])
        && $_SESSION['verification_codes'][$email] === $code) {
        unset($_SESSION['verification_codes'][$email]);
        return true;
    }
    return false;
}

/**
 * Fetch random XKCD comic and format data as HTML.
 */
function fetchAndFormatXKCDData(): array {
    $latest = json_decode(file_get_contents('https://xkcd.com/info.0.json'), true);
    $max = $latest['num'] ?? 0;
    $randId = random_int(1, $max);
    $data = json_decode(file_get_contents("https://xkcd.com/{$randId}/info.0.json"), true);
    return [
        'img'   => $data['img'] ?? '',
        'alt'   => $data['alt'] ?? '',
        'title' => $data['title'] ?? '',
    ];
}

/**
 * Send the formatted XKCD updates to registered emails.
 */
function sendXKCDUpdatesToSubscribers(): void {
    $file = __DIR__ . '/registered_emails.txt';
    if (!file_exists($file)) return;
    $emails = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $comic = fetchAndFormatXKCDData();
    foreach ($emails as $email) {
        $subject = "Your XKCD Comic";
        $body    = "<h2>XKCD Comic</h2>"
                 . "<img src=\"{$comic['img']}\" alt=\"XKCD Comic\" />"
                 . "<p><a href=\"unsubscribe.php?unsubscribe_email="
                 . urlencode($email)
                 . "\" id=\"unsubscribe-button\">Unsubscribe</a></p>";
        $headers  = "MIME-Version: 1.0\r\n"
                  . "Content-type:text/html;charset=UTF-8\r\n"
                  . "From: no-reply@example.com\r\n";
        mail($email, $subject, $body, $headers);
    }
}
