<?php

namespace Utilis;

use Exception;
use includes\exception\ExceptionEmailSendingFailed;

/**
 * Class EmailService
 * This class regroup function to manage the email service.

 * @category Service

 * @package Src

 * @subpackage Utilis

 * @author  Alexandre Benhafessa <alexandre.benhafessa@etu.univ-amu.fr>
 * @author  François Dargentolle <francois.dargentolle@etu.univ-amu.fr>
 * @author  William Edelstein <william.edelstein@etu.univ-amu.fr>
 * @author  Nathan Griguer <nathan.griguer@etu.univ-amu.fr>
 * @author  Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>

 * @license MIT License https://opensource.org/licenses/MIT

 * @link https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class EmailService
{
    /**
     * The email to send mails from.
     *
     * @var string
     */
    private static string $fromEmail = 'noreply@saemanager.alwaysdata.net';
    /**
     * The sender of the mails.
     *
     * @var string
     */
    private static string $fromName = 'SAEManager';

    /**
     * Send a mail to the user, in order to reset their password.
     *
     * This method creates a mail with a built reset password link (self::getResetLink) using the token and email
     * given in parametters.
     *
     * @param string $toEmail The email of the user whom want their password reset.
     * @param string $token   The token created for the password reset.
     *
     * @return void
     */
    public static function sendPasswordResetEmail(string $toEmail, string $token): void
    {
        $resetLink = self::getResetLink($token);

        $subject = 'Réinitialisation de votre mot de passe - SAEManager';

        $htmlMessage = self::getHtmlTemplate($resetLink);
        $textMessage = self::getTextTemplate($resetLink);

        self::sendEmail($toEmail, $subject, $htmlMessage, $textMessage);
    }

    /**
     * Returns the reset password link.
     *
     * This method builds a password reset link based on the server parametters
     * and the token given in parametters and returns it.
     *
     * @param string $token The token created for the password reset to create to link with.
     *
     * @return string
     */
    private static function getResetLink(string $token): string
    {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        return "{$protocol}://{$host}/reset-password?token={$token}";
    }

    /**
     * Returns the HTML content for the password reset email template.
     *
     * This template includes a reset link that the user can click to reset their password.
     *
     * @param string $resetLink The URL the user will visit to reset their password.
     * @return string The HTML content of the password reset email.
     */
    private static function getHtmlTemplate(string $resetLink): string
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
        .button { 
            display: inline-block; 
            padding: 12px 30px; 
            background-color: #0072ce; 
            color: white; 
            text-decoration: none; 
            border-radius: 5px;
            margin: 20px 0;
        }
        .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #666; }
        .warning { background-color: #fff3cd; border-left: 4px solid #ffc107; padding: 10px; margin: 15px 0; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h1>SAEManager</h1>
        </div>
        <div class='content'>
            <h2>Réinitialisation de votre mot de passe</h2>
            <p>Bonjour,</p>
            <p>Vous avez demandé la réinitialisation de votre mot de passe sur SAEManager.</p>
            <p>Cliquez sur le bouton ci-dessous pour créer un nouveau mot de passe :</p>
            <p style='text-align: center;'>
                <a href='{$resetLink}' class='button'>Réinitialiser mon mot de passe</a>
            </p>
            <p>Ou copiez ce lien dans votre navigateur :</p>
            <p style='word-break: break-all; color: #0072ce;'>{$resetLink}</p>
            
            <div class='warning'>
                <strong>⚠️ Important :</strong>
                <ul>
                    <li>Ce lien est valide pendant <strong>10 minutes</strong></li>
                    <li>Il ne peut être utilisé qu'<strong>une seule fois</strong></li>
                    <li>Si vous n'avez pas demandé cette réinitialisation, ignorez cet email</li>
                </ul>
            </div>
        </div>
        <div class='footer'>
            <p>© {$year} SAEManager - Aix-Marseille Université</p>
            <p>Ceci est un email automatique, merci de ne pas y répondre.</p>
        </div>
    </div>
</body>
</html>";
    }

    /**
     * Returns the plain text content for the password reset email template.
     *
     * This template includes a reset link that the user can use to reset their password.
     *
     * @param string $resetLink The URL the user will visit to reset their password.
     * @return string The plain text content of the password reset email.
     */
    private static function getTextTemplate(string $resetLink): string
    {
        return "
Réinitialisation de votre mot de passe - SAEManager

Bonjour,

Vous avez demandé la réinitialisation de votre mot de passe sur SAEManager.

Pour créer un nouveau mot de passe, cliquez sur ce lien :
{$resetLink}

IMPORTANT :
- Ce lien est valide pendant 10 minutes
- Il ne peut être utilisé qu'une seule fois
- Si vous n'avez pas demandé cette réinitialisation, ignorez cet email

© 2025 SAEManager - Aix-Marseille Université
Ceci est un email automatique, merci de ne pas y répondre.
";
    }

    /**
     * Throws an error if sending the mail fail
     *
     * This method tries to send an email to the user using all the parametters
     * given and filling the templates with variables, fixing chartsets and other content types.
     *
     * @param string $to          The email to send to.
     * @param string $subject     The subject of the mail.
     * @param string $htmlMessage The html message.
     * @param string $textMessage The plain text message.
     *
     * @return void
     * @throws ExceptionEmailSendingFailed If the email can't be send.
     */
    private static function sendEmail(string $to, string $subject, string $htmlMessage, string $textMessage): void
    {
        // Headers for multipart email (HTML + text).
        $boundary = md5(uniqid('boundary_', true));

        $headers = [
            'From' => self::$fromName . ' <' . self::$fromEmail . '>',
            'Reply-To' => self::$fromEmail,
            'MIME-Version' => '1.0',
            'Content-Type' => 'multipart/alternative; boundary="' . $boundary . '"'
        ];

        $message = "--{$boundary}\r\n";
        $message .= "Content-Type: text/plain; charset=UTF-8\r\n";
        $message .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
        $message .= $textMessage . "\r\n\r\n";

        $message .= "--{$boundary}\r\n";
        $message .= "Content-Type: text/html; charset=UTF-8\r\n";
        $message .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
        $message .= $htmlMessage . "\r\n\r\n";

        $message .= "--{$boundary}--";

        $headerString = '';
        foreach ($headers as $key => $value) {
            $headerString .= "{$key}: {$value}\r\n";
        }

        // Send the email.
        if (mail($to, $subject, $message, $headerString)) {
            error_log("Email envoyé avec succès à: {$to}");
        } else {
            error_log("Échec d'envoi d'email à: {$to}");
            throw new ExceptionEmailSendingFailed();
        }
    }
}
