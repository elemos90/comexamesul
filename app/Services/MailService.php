<?php

namespace App\Services;

use PHPMailer\PHPMailer\Exception as MailerException;
use PHPMailer\PHPMailer\PHPMailer;

class MailService
{
    public function send(string $to, string $subject, string $message, string $fromAddress, string $fromName): bool
    {
        $smtpHost = env('MAIL_SMTP_HOST');
        $smtpUser = env('MAIL_SMTP_USER');
        $smtpPass = env('MAIL_SMTP_PASS');
        $smtpPort = (int) env('MAIL_SMTP_PORT', 587);
        $smtpEncryption = strtolower((string) env('MAIL_SMTP_ENCRYPTION', 'tls'));

        if (!$smtpHost || !$smtpUser || !$smtpPass) {
            return $this->logFallback($to, $subject, $message, 'SMTP credentials not configured');
        }

        $mailer = new PHPMailer(true);

        try {
            $mailer->isSMTP();
            $mailer->Host = $smtpHost;
            $mailer->SMTPAuth = true;
            $mailer->Username = $smtpUser;
            $mailer->Password = $smtpPass;
            $mailer->Port = $smtpPort > 0 ? $smtpPort : 587;
            $mailer->CharSet = 'UTF-8';

            if ($smtpEncryption === 'ssl') {
                $mailer->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            } else {
                $mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            }

            $mailer->setFrom($fromAddress, $fromName);
            $mailer->addAddress($to);
            $mailer->isHTML(true);
            $mailer->Subject = $subject;
            $mailer->Body = $message;
            $mailer->AltBody = strip_tags($message);

            $mailer->send();
            return true;
        } catch (MailerException $exception) {
            return $this->logFallback($to, $subject, $message, $exception->getMessage());
        }
    }

    private function logFallback(string $to, string $subject, string $message, string $error = ''): bool
    {
        $logMessage = sprintf("[%s] To:%s Subject:%s\n%s\nErro: %s\n", now(), $to, $subject, $message, $error);
        file_put_contents(storage_path('logs/mail.log'), $logMessage, FILE_APPEND);
        return false;
    }
}