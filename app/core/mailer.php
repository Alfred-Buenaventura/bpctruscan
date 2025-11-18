<?php
// Import PHPMailer classes manually
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require_once __DIR__ . '/../../vendor/PHPMailer.php';
require_once __DIR__ . '/../../vendor/SMTP.php';
require_once __DIR__ . '/../../vendor/Exception.php';

class Mailer {
    
    public static function send($to, $subject, $message) {
        $mail = new PHPMailer(true);

        // --- ROBUST CREDENTIAL LOADING ---
        // Try getenv first, then fallback to superglobals
        $username = getenv('SMTP_USER') ?: ($_ENV['SMTP_USER'] ?? ($_SERVER['SMTP_USER'] ?? null));
        $password = getenv('SMTP_PASS') ?: ($_ENV['SMTP_PASS'] ?? ($_SERVER['SMTP_PASS'] ?? null));
        $fromName = getenv('SMTP_FROM_NAME') ?: ($_ENV['SMTP_FROM_NAME'] ?? 'BPC Attendance System');

        // Debug: Log if credentials are missing
        if (empty($username) || empty($password)) {
            self::logError("Credentials missing. User: " . ($username ? 'Set' : 'Missing') . ", Pass: " . ($password ? 'Set' : 'Missing'));
            return false;
        }

        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = $username;
            $mail->Password   = $password;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // 'tls'
            $mail->Port       = 587;

            // Recipients
            $mail->setFrom($username, $fromName);
            $mail->addAddress($to);

            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $message;
            $mail->AltBody = strip_tags($message);

            $mail->send();
            return true;

        } catch (Exception $e) {
            self::logError("Mail Error: {$mail->ErrorInfo}");
            return false;
        }
    }

    private static function logError($msg) {
        // Logs errors to a file named 'email_debug.log' in your root folder
        $logFile = __DIR__ . '/../../email_debug.log';
        $entry = date('Y-m-d H:i:s') . " - " . $msg . "\n";
        file_put_contents($logFile, $entry, FILE_APPEND);
        error_log($msg); // Also log to PHP error log
    }
}
?>