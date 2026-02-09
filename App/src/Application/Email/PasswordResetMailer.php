<?php

namespace App\Application\Email;

use App\Infrastructure\Exception\EmailSendingException;
use App\Infrastructure\Service\EmailService;
use App\Infrastructure\Service\TokenService;

// Also need the concrete repo for TokenService constructor

/**
 * Service responsible for sending password reset emails for SAE Manager.
 *
 * @category Service
 * @package  App
 * @subpackage Services/Auth
 * @author     Dinesh Radjou <dinesh.radjou@univ-amu.fr>
 * @license    https://opensource.org/licenses/MIT MIT License
 * @link       https://github.com/RADJOU-Dinesh-24003262/SAEManager/blob/main/App/src/Services/Auth/PasswordResetMailer.php
 */
class PasswordResetMailer
{
    private TokenService $tokenService;

    public function __construct(TokenService $tokenService)
    {
        $this->tokenService = $tokenService;
    }

    /**
     * Sends a password reset email to the user.
     *
     * @param string $toEmail The user's email address.
     * @param string $token   The reset token.
     * @return void
     * @throws EmailSendingException If sending fails.
     */
    public function send(string $toEmail, string $token): void
    {
        $resetLink = $this->getResetLink($token);
        $subject = 'Réinitialisation de votre mot de passe - SAE Manager';

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
    private function getResetLink(string $token): string
    {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        return "{$protocol}://{$host}/reset-password?token={$token}";
    }

    /**
     * Returns the HTML template.
     *
     * @param string $resetLink The reset link.
     * @return string
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
            <h1>SAE Manager</h1>
        </div>
        <div class='content'>
            <h2>Réinitialisation de votre mot de passe</h2>
            <p>Bonjour,</p>
            <p>Vous avez demandé la réinitialisation de votre mot de passe sur SAE Manager.</p>
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
     * @param string $resetLink The reset link.
     * @return string
     */
    private static function getTextTemplate(string $resetLink): string
    {
        return "
Réinitialisation de votre mot de passe - SAE Manager

Bonjour,

Vous avez demandé la réinitialisation de votre mot de passe sur SAE Manager.
Pour créer un nouveau mot de passe, cliquez sur ce lien :
{$resetLink}

IMPORTANT :
- Ce lien est valide pendant 10 minutes
- Il ne peut être utilisé qu'une seule fois
- Si vous n'avez pas demandé cette réinitialisation, ignorez cet email

© 2025 SAE Manager - Aix-Marseille Université
Ceci est un email automatique, merci de ne pas y répondre.
";
    }
}
