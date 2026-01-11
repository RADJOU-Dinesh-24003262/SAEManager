<?php

namespace Services\Auth;

use Core\Utilis\EmailService;
use Models\SAE\Repository\SAESubjectRepository;
use DateTime;

/**
 * Service responsible for sending reminder emails for SAE Manager.
 *
 * @category Service
 * @package  App
 * @subpackage Services/Auth
 * @author     François Dargentolle <francois.dargentolle@etu.univ-amu.fr>
 * @author     William Edelstein <william.edelstein@etu.univ-amu.fr>
 * @license    https://opensource.org/licenses/MIT MIT License
 * @link       https://github.com/RADJOU-Dinesh-24003262/SAEManager/blob/main/App/src/Services/Auth/PasswordResetMailer.php
 */
class LastDateMailer
{
    /**
     * Sends a reminder email to the user about the SAE submission deadline.
     *
     * @return void
     */
    public static function send(): void
    {
        $subjectOfMail = 'Rappel date limite du rendu - SAE Manager';

        $dateEndFocus = (new DateTime('+3 days'))->format('Y-m-d');
        $repo = SAESubjectRepository::getInstance();
        $studentsrepo = $repo->findStudentsWithSaeEndingOnDate($dateEndFocus);

        foreach ($studentsrepo as $student) {
            $repoSubject = $repo->findById($student->getSaeSubjectId());
            $emailStudent = $student->getEmail();
            $htmlMessage = self::getHtmlTemplate($student, $repoSubject);
            $textMessage = self::getTextTemplate($student, $repoSubject);
            EmailService::send($emailStudent, $subjectOfMail, $htmlMessage, $textMessage);
        }
    }

    /**
     * Returns the HTML template.
     *
     * @param object $student The student.
     * @param object $subject The SAE subject.
     * @return string
     */
    private static function getHtmlTemplate($student, $subject): string
    {
        $year = date('Y');
        $endDate = $subject->getEndDate()->format('Y-m-d');
        $title = htmlspecialchars($subject->getTitle());
        $prenom = htmlspecialchars($student->getFirstName());

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
            <h2>Rappel date limite du rendu</h2>
            <p>Bonjour {$prenom},</p>
            <p>Nous vous rappelons que la date limite du rendu de la SAE <strong>{$title}</strong> approche.</p>
            <p>Vous devez rendre ce devoir le <strong>{$endDate}</strong>.</p>
            <p>Merci de vous assurer que votre travail est bien déposé avant la date limite</p>
            <p style='word-break: break-all; color: #0072ce;'></p>
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
     * @param object $student The student.
     * @param object $subject The SAE subject.
     * @return string
     */
    private static function getTextTemplate($student, $subject): string
    {
        $title = htmlspecialchars($subject->getTitle());
        $prenom = htmlspecialchars($student->getFirstName());
        $endDate = $subject->getEndDate()->format('Y-m-d');
        return "
Rappel date limite du rendu - SAE Manager

Bonjour {$prenom},

Nous vous rappelons que la date limite du rendu de la SAE {$title} approche.
Vous devez rendre ce devoir le {$endDate}.
Merci de vous assurer que votre travail est bien déposé avant la date limite.

© 2026 SAE Manager - Aix-Marseille Université
Ceci est un email automatique, merci de ne pas y répondre.
";
    }

    public static function main(): void
    {
        self::send(); // Will be changed later.
    }
}
