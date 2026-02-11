<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require_once __DIR__ . '/../../vendor/PHPMailer.php';
require_once __DIR__ . '/../../vendor/SMTP.php';
require_once __DIR__ . '/../../vendor/Exception.php';

class Mailer {
    
    public static function send($to, $subject, $message) {
        $mail = new PHPMailer(true);

        // grab the mail server login info from the environment variables
        $username = getenv('SMTP_USER') ?: ($_ENV['SMTP_USER'] ?? null);
        $password = getenv('SMTP_PASS') ?: ($_ENV['SMTP_PASS'] ?? null);
        $fromName = getenv('SMTP_FROM_NAME') ?: 'BPC Attendance System';

        // app passwords from google usually have spaces that need to be removed to work
        if ($password) {
            $password = str_replace(' ', '', $password);
        }

        if (empty($username) || empty($password)) {
            self::logError("CRITICAL: SMTP credentials missing in .env file.");
            return false;
        }

        try {
            // configure the smtp server settings so we can actually send the mail
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = $username;
            $mail->Password   = $password;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            // bypass ssl verification when running on local xampp setups to avoid errors
            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );

            $mail->setFrom($username, $fromName);
            $mail->addAddress($to);

            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $message;
            $mail->AltBody = strip_tags($message);

            $mail->send();
            self::logError("SUCCESS: Email sent to $to.");
            return true;

        } catch (Exception $e) {
            self::logError("MAILER ERROR: " . $mail->ErrorInfo . " | To: $to");
            return false;
        }
    }

    private static function logError($msg) {
        // keep track of mail successes and failures in a local log file for debugging
        $logFile = __DIR__ . '/../../email_debug.log';
        $entry = date('Y-m-d H:i:s') . " - " . $msg . "\n";
        file_put_contents($logFile, $entry, FILE_APPEND);
    }
}