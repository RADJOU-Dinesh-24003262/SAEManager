<?php

namespace App\Application\Email;

use App\Infrastructure\Service\EmailService;
use App\Infrastructure\Service\Logger;
use App\Infrastructure\Persistence\Pdo\PdoSaeRepository;
use App\Infrastructure\Persistence\Pdo\PdoSaeGroupRepository;
use App\Domain\User\Student;
use App\Domain\SAE\Sae;
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
        Logger::log('MAIL_LAST_DATE', 'Starting last date reminder email process.');
        $subjectOfMail = 'URGENT : Rappel date limite de rendu - SAE Manager';

        $dateEndFocus = (new DateTime('+3 days'))->format('Y-m-d');
        Logger::log('MAIL_LAST_DATE', "Focus date for reminder: $dateEndFocus");

        $saeSubjectRepo = new PdoSaeRepository();
        $saeSubjects = $saeSubjectRepo->findByEndDate($dateEndFocus);
        Logger::log('MAIL_LAST_DATE', 'Found ' . count($saeSubjects) . ' subjects ending on focus date.');

        $saeGroupRepo = new PdoSaeGroupRepository();

        foreach ($saeSubjects as $saeSubject) {
            $saeId = $saeSubject->getId();

            if ($saeId === null) {
                Logger::log('MAIL_LAST_DATE', 'Skipping subject with null ID.', null, 'WARNING');
                continue;
            }

            Logger::log('MAIL_LAST_DATE', "Processing SAE ID: $saeId ({$saeSubject->getName()})");
            $studentsGroup = $saeGroupRepo->findBySaeId($saeId);

            if (empty($studentsGroup)) {
                Logger::log('MAIL_LAST_DATE', "No groups found for SAE ID: $saeId");
                continue;
            }

            Logger::log('MAIL_LAST_DATE', 'Found ' . count($studentsGroup) . " groups for SAE ID: $saeId");

            foreach ($studentsGroup as $studentGroup) {
                $groupId = $studentGroup->getId();

                if ($groupId === null) {
                    continue;
                }

                $students = $saeGroupRepo->getGroupStudents($groupId);

                Logger::log('MAIL_LAST_DATE', 'Found ' . count($students) . " students in group ID: $groupId");

                foreach ($students as $studentData) {
                    $student = new Student($studentData);

                    $emailStudent = $student->getEmail();
                    Logger::log('MAIL_LAST_DATE', "Preparing to send email to: $emailStudent");

                    try {
                        $htmlMessage = self::getHtmlTemplate($student, $saeSubject);
                        $textMessage = self::getTextTemplate($student, $saeSubject);
                        EmailService::send($emailStudent, $subjectOfMail, $htmlMessage, $textMessage);
                        Logger::log('MAIL_LAST_DATE', "Email sent successfully to: $emailStudent");
                    } catch (\Exception $e) {
                        Logger::log(
                            'MAIL_LAST_DATE',
                            "Failed to send email to $emailStudent: " . $e->getMessage(),
                            null,
                            'ERROR'
                        );
                    }
                }
            }
        }
        Logger::log('MAIL_LAST_DATE', 'Last date reminder email process completed.');
    }

    /**
     * Returns the HTML template.
     *
     * @param Student $student The student.
     * @param Sae     $subject The SAE subject.
     * @return string
     */
    private static function getHtmlTemplate(Student $student, Sae $subject): string
    {
        $year = date('Y');
        $endDate = (new DateTime($subject->getEndDate()))->format('d/m/Y');
        $title = htmlspecialchars($subject->getName());
        $prenom = htmlspecialchars($student->getFirstName());
        $dashboardUrl = "https://saemanager.alwaysdata.net/";

        return "
<!DOCTYPE html>
<html lang='fr'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #333; " .
            "background-color: #f4f4f4; margin: 0; padding: 0; }
        .wrapper { width: 100%; background-color: #f4f4f4; padding: 20px 0; }
        .container { max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; " .
            "overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .header { background-color: #d32f2f; color: white; padding: 30px 20px; text-align: center; } 
        /* Red for urgency */
        .header h1 { margin: 0; font-size: 24px; font-weight: 600; letter-spacing: 1px; }
        .content { padding: 40px 30px; }
        .content h2 { color: #d32f2f; font-size: 20px; margin-top: 0; border-bottom: 2px solid #f0f0f0; " .
            "padding-bottom: 10px; }
        .warning-box { background-color: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0; " .
            "border-radius: 4px; color: #856404; }
        .info-item { margin-bottom: 5px; }
        .info-label { font-weight: bold; }
        .button-container { text-align: center; margin-top: 30px; }
        .button { display: inline-block; padding: 12px 30px; background-color: #d32f2f; color: white; " .
            "text-decoration: none; border-radius: 50px; font-weight: bold; transition: background-color 0.3s; }
        .button:hover { background-color: #b71c1c; }
        .footer { background-color: #333; color: #aaa; text-align: center; padding: 20px; font-size: 12px; }
        .footer p { margin: 5px 0; }
    </style>
</head>
<body>
    <div class='wrapper'>
        <div class='container'>
            <div class='header'>
                <h1>SAE Manager - Rappel</h1>
            </div>
            <div class='content'>
                <p>Bonjour <strong>{$prenom}</strong>,</p>
                
                <p>La date limite de rendu pour votre Situation d'Apprentissage et d'Évaluation (SAE) " .
                    "approche à grands pas.</p>
                
                <div class='warning-box'>
                    <div class='info-item'><span class='info-label'>Intitulé :</span> {$title}</div>
                    <div class='info-item'><span class='info-label'>Date limite de rendu :</span> " .
                        "<strong>{$endDate}</strong></div>
                </div>
                
                <p>Merci de vous assurer que votre travail est bien déposé avant cette date. " .
                    "Tout retard pourrait entraîner des pénalités.</p>
                
                <div class='button-container'>
                    <a href='{$dashboardUrl}' class='button'>Déposer mon rendu</a>
                </div>
            </div>
            <div class='footer'>
                <p>&copy; {$year} SAE Manager - Aix-Marseille Université</p>
                <p>Ceci est un email automatique, merci de ne pas y répondre.</p>
            </div>
        </div>
    </div>
</body>
</html>";
    }

    /**
     * Returns the plain text template.
     *
     * @param Student $student The student.
     * @param Sae     $subject The SAE subject.
     * @return string
     */
    private static function getTextTemplate(Student $student, Sae $subject): string
    {
        $endDate = (new DateTime($subject->getEndDate()))->format('d/m/Y');
        $title = $subject->getName();
        $prenom = $student->getFirstName();
        $dashboardUrl = "https://saemanager.alwaysdata.net/login";

        return "
URGENT : RAPPEL DATE LIMITE - SAE MANAGER
--------------------------------------------------

Bonjour {$prenom},

Nous vous rappelons que la date limite de rendu pour votre SAE approche.

Détails :
- Intitulé : {$title}
- Date limite : {$endDate}

Assurez-vous de déposer votre travail à temps en vous connectant à votre espace :
{$dashboardUrl}

--------------------------------------------------
© " . date('Y') . " SAE Manager - Aix-Marseille Université
Ceci est un email automatique, merci de ne pas y répondre.
";
    }

    /**
     * Main method to execute the mailer.
     *
     * @return void
     */
    public static function main(): void
    {
        self::send(); // Will be changed later.
    }
}

// phpcs:disable PSR1.Files.SideEffects
// Execute if run directly.
if (basename(__FILE__) == basename($_SERVER["SCRIPT_FILENAME"])) {
    require_once __DIR__ . '/../../../../Core/Autoloader.php';
    \Core\Autoloader::register();
    LastDateMailer::main();
}
// phpcs:enable
