<?php

namespace Services\Auth;

use Core\includes\exception\ExceptionEmailSendingFailed;
use Core\Utilis\EmailService;

/**
 * Service responsible for sending registration confirmation emails for SAE Manager.
 *
 * @category Service
 * @package  App
 * @subpackage Services/Auth
 * @author     Dinesh Radjou <dinesh.radjou@univ-amu.fr>
 * @license    https://opensource.org/licenses/MIT MIT License
 * @link       https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class RegistrationMailer
{
    /**
     * Sends a registration confirmation email with an OTP code to the user.
     *
     * @param string $toEmail The user's email address.
     * @param string $token   The reset token.
     * @return void
     * @throws ExceptionEmailSendingFailed If sending fails.
     */
    public static function send(string $toEmail, string $token): void
    {
        $resetLink = self::getResetLink($token);
        $subject = 'Confirmation de votre inscription - SAE Manager';

        $htmlMessage = self::getHtmlTemplate($resetLink);
        $textMessage = self::getTextTemplate($resetLink);

        EmailService::send($toEmail, $subject, $htmlMessage, $textMessage);
    }


    /**
     * Generates the reset link.
     *
     * @param string $token The reset token.
     * @return string The full URL.
     */
    private static function getResetLink(string $token): string
    {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        return "{$protocol}://{$host}/mfa?token={$token}";
    }

    /**
     * Returns the HTML template.
     *
     * @param string $otpCode The OTP confirmation code.
     * @return string
     */
    private static function getHtmlTemplate(string $otpCode): string
    {
        $year = date('Y');

        return "
<!DOCTYPE html>
<html lang='fr'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #0072ce; color: white; padding: 20px; text-align: center; }
        .content { background-color: #f9f9f9; padding: 30px; border: 1px solid #ddd; }
        .otp-box {
            text-align: center;
            margin: 30px 0;
        }
        .otp-code {
            display: inline-block;
            font-size: 36px;
            font-weight: bold;
            letter-spacing: 10px;
            color: #0072ce;
            background-color: #e8f4ff;
            padding: 15px 30px;
            border-radius: 8px;
            border: 2px dashed #0072ce;
        }
        .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #666; }
        .warning { background-color: #fff3cd; border-left: 4px solid #ffc107; padding: 10px; margin: 15px 0; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h1>SAE Manager</h1>
        </div>
        <div class='content'>
            <h2>Confirmez votre inscription</h2>
            <p>Bonjour,</p>
            <p>Merci de vous être inscrit sur SAE Manager. Pour finaliser la création de votre compte, saisissez le code ci-dessous :</p>

            <p>{$otpCode}</p>

            <div class='warning'>
                <strong>⚠️ Important :</strong>
                <ul>
                    <li>Ce code est valide pendant <strong>10 minutes</strong></li>
                    <li>Il ne peut être utilisé qu'<strong>une seule fois</strong></li>
                    <li>Si vous n'avez pas créé de compte, ignorez cet email</li>
                </ul>
            </div>
        </div>
        <div class='footer'>
            <p>© {$year} SAE Manager - Aix-Marseille Université</p>
            <p>Ceci est un email automatique, merci de ne pas y répondre.</p>
        </div>
    </div>
</body>
</html>";
    }

    /**
     * Returns the plain text template.
     *
     * @param string $otpCode The OTP confirmation code.
     * @return string
     */
    private static function getTextTemplate(string $otpCode): string
    {
        return "
Confirmation de votre inscription - SAE Manager

Bonjour,

Merci de vous être inscrit sur SAE Manager.
Pour finaliser la création de votre compte, saisissez ce code :

{$otpCode}

IMPORTANT :
- Ce code est valide pendant 10 minutes
- Il ne peut être utilisé qu'une seule fois
- Si vous n'avez pas créé de compte, ignorez cet email

© " . date('Y') . " SAE Manager - Aix-Marseille Université
Ceci est un email automatique, merci de ne pas y répondre.
";
    }
}