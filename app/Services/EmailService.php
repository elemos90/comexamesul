<?php

namespace App\Services;

/**
 * Email Service
 * Handles all email sending with PHPMailer or fallback to mail()
 */
class EmailService
{
    private string $fromEmail = 'noreply@comexamesul.ac.mz';
    private string $fromName = 'Portal COMEXAMES';

    // SMTP Configuration (edit these for your server)
    private string $smtpHost = 'smtp.gmail.com';
    private int $smtpPort = 587;
    private string $smtpUsername = ''; // Set in .env or config
    private string $smtpPassword = ''; // Set in .env or config
    private bool $smtpEnabled = false;

    /**
     * Send email using PHPMailer if available, otherwise native mail()
     */
    public function send(string $to, string $subject, string $htmlBody, ?string $plainBody = null): bool
    {
        // Try PHPMailer first
        if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
            return $this->sendWithPHPMailer($to, $subject, $htmlBody, $plainBody);
        }

        // Fallback to native mail()
        return $this->sendWithNativeMail($to, $subject, $htmlBody);
    }

    /**
     * Send email with PHPMailer (SMTP)
     */
    private function sendWithPHPMailer(string $to, string $subject, string $htmlBody, ?string $plainBody): bool
    {
        try {
            $mail = new \PHPMailer\PHPMailer\PHPMailer(true);

            // Server settings
            if ($this->smtpEnabled) {
                $mail->isSMTP();
                $mail->Host = $this->smtpHost;
                $mail->SMTPAuth = true;
                $mail->Username = $this->smtpUsername;
                $mail->Password = $this->smtpPassword;
                $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = $this->smtpPort;
            }

            // Recipients
            $mail->setFrom($this->fromEmail, $this->fromName);
            $mail->addAddress($to);

            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $htmlBody;
            $mail->AltBody = $plainBody ?? strip_tags($htmlBody);
            $mail->CharSet = 'UTF-8';

            $mail->send();
            return true;

        } catch (\Exception $e) {
            error_log("PHPMailer Error: {$mail->ErrorInfo}");
            return false;
        }
    }

    /**
     * Send email with native mail() function
     */
    private function sendWithNativeMail(string $to, string $subject, string $htmlBody): bool
    {
        $headers = [];
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-type: text/html; charset=UTF-8';
        $headers[] = "From: {$this->fromName} <{$this->fromEmail}>";
        $headers[] = 'X-Mailer: PHP/' . phpversion();

        $result = @mail($to, $subject, $htmlBody, implode("\r\n", $headers));

        if (!$result) {
            error_log("Native mail() failed for: {$to}");
        }

        return $result;
    }

    /**
     * Send welcome email to new user
     */
    public function sendWelcomeEmail(string $email, string $name, string $tempPassword): bool
    {
        $subject = "Bem-vindo ao Portal COMEXAMES";
        $htmlBody = $this->getWelcomeEmailTemplate($name, $email, $tempPassword);

        return $this->send($email, $subject, $htmlBody);
    }

    /**
     * Get welcome email HTML template
     */
    private function getWelcomeEmailTemplate(string $name, string $email, string $tempPassword): string
    {
        $loginUrl = url('/login');

        return "<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
</head>
<body style='margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f3f4f6;'>
    <table width='100%' cellpadding='0' cellspacing='0' style='background-color: #f3f4f6; padding: 20px 0;'>
        <tr>
            <td align='center'>
                <table width='600' cellpadding='0' cellspacing='0' style='background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1);'>
                    
                    <!-- Header -->
                    <tr>
                        <td style='background-color: #1F2937; padding: 30px 40px; text-align: center;'>
                            <h1 style='margin: 0; color: #ffffff; font-size: 24px;'>Portal COMEXAMES</h1>
                            <p style='margin: 5px 0 0 0; color: #9CA3AF; font-size: 14px;'>Comissão de Exames de Admissão</p>
                        </td>
                    </tr>

                    <!-- Content -->
                    <tr>
                        <td style='padding: 40px;'>
                            <h2 style='margin: 0 0 20px 0; color: #111827; font-size: 20px;'>Olá, {$name}!</h2>
                            
                            <p style='margin: 0 0 20px 0; color: #374151; line-height: 1.6;'>
                                A sua conta no Portal COMEXAMES foi criada com sucesso.
                            </p>
                            
                            <table width='100%' cellpadding='20' cellspacing='0' style='background-color: #F3F4F6; border-radius: 8px; margin: 30px 0;'>
                                <tr>
                                    <td>
                                        <h3 style='margin: 0 0 15px 0; color: #374151; font-size: 16px;'>Credenciais de Acesso:</h3>
                                        <p style='margin: 5px 0; color: #4B5563;'><strong>Email:</strong> {$email}</p>
                                        <p style='margin: 5px 0; color: #4B5563;'><strong>Senha Temporária:</strong> <code style='background: #E5E7EB; padding: 4px 8px; border-radius: 4px; font-family: monospace;'>{$tempPassword}</code></p>
                                    </td>
                                </tr>
                            </table>
                            
                            <p style='margin: 20px 0; color: #DC2626; font-weight: bold;'>
                                ⚠️ IMPORTANTE: Altere a sua senha no primeiro acesso!
                            </p>
                            
                            <table width='100%' cellpadding='0' cellspacing='0'>
                                <tr>
                                    <td align='center' style='padding: 20px 0;'>
                                        <a href='{$loginUrl}' style='display: inline-block; background-color: #4F46E5; color: white; text-decoration: none; padding: 12px 30px; border-radius: 6px; font-weight: bold;'>
                                            Aceder ao Sistema
                                        </a>
                                    </td>
                                </tr>
                            </table>
                            
                            <p style='margin: 30px 0 0 0; color: #6B7280; font-size: 14px; line-height: 1.6;'>
                                Se tiver dúvidas ou problemas de acesso, entre em contacto com a Comissão de Exames.
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style='background-color: #F9FAFB; padding: 20px 40px; border-top: 1px solid #E5E7EB; text-align: center;'>
                            <p style='margin: 0; color: #6B7280; font-size: 12px;'>
                                © 2026 Portal COMEXAMES. Todos os direitos reservados.
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>";
    }
}
