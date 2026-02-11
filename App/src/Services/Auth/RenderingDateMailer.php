<?php
namespace Services\Auth;

use Core\Utilis\EmailService;
use Models\SAE\SAE;
use phpDocumentor\Reflection\Types\Boolean;

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
class RenderingDateMailer
{
    /**
     * Sends a password reset email to the user.
     *
     * @param string $toEmail The user's email address.
     * @param string $token   The reset token.
     * @return void
     */
    public static function send(string $toEmail, string $token, SAE $subjectsae): void
    {
        $subject = 'Rappel date de fin du rendu - SAE Manager';
        $date =  time();
        $subjectSAE = $subjectsae::getinstance();

        $htmlMessage = self::getHtmlTemplate();
        $textMessage = self::getTextTemplate();

        EmailService::send($toEmail, $subject, $htmlMessage, $textMessage);
    }

    /**
     * Verify the date for the email of the rendering date.
     *
     * @param int $date The date of the SAE.
     * @param int $date_focus The date for the email.
     * @return bool
     */
    public static function verifyDate(int $date, int $date_focus): bool
    {
        if ($date + 3 === $date_focus) {
            return true;
        }
            return false;
    }

    /**
     * Returns the HTML template.
     *
     * @return string
     */
    private static function getHtmlTemplate(): string
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
            <h2>Rappel date de fin du rendu</h2>
            <p>Bonjour,</p>
            <p>Nous vous rappelons que votre SAE {{titre}} se finis le {$date_focus}.  </p>
            <p>Si vous ne le rendez pas à temps, votre devoir sera compté comme non rendu et donc avec l'obtention d'un zéro.</p>
            <p style='text-align: center;'>
                <a href='' class='button'>Réinitialiser mon mot de passe</a>
            </p>
            <p>Ou copiez ce lien dans votre navigateur :</p>
            <p style='word-break: break-all; color: #0072ce;'></p>
            
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
    private static function getTextTemplate(): string
    {
        return "
Réinitialisation de votre mot de passe - SAE Manager

Bonjour,

Vous avez demandé la réinitialisation de votre mot de passe sur SAE Manager.
Pour créer un nouveau mot de passe, cliquez sur ce lien :


IMPORTANT :
- Ce lien est valide pendant 10 minutes
- Il ne peut être utilisé qu'une seule fois
- Si vous n'avez pas demandé cette réinitialisation, ignorez cet email

© 2025 SAE Manager - Aix-Marseille Université
Ceci est un email automatique, merci de ne pas y répondre.
";
    }
}
