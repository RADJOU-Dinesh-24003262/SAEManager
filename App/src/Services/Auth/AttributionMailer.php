<?php

namespace Services\Auth;

use Core\Utilis\EmailService;
use Core\Utilis\Logger;
use Models\Entity\SAE\Repository\SAESubjectRepository;
use Models\Entity\User\Student;
use Models\Entity\User\User;
use Models\Entity\SAE\SAESubject;
use DateTime;
use Models\Entity\SAE\Repository\SAEGroupRepository;
use Models\Entity\SAE\Repository\ParticipatedInRepository;
use Models\Repository\SAE\PdoParticipatedInRepository;
use Models\Repository\SAE\PdoSAEGroupRepository;
use Models\Repository\SAE\PdoSAESubjectRepository;

/**
 * Service responsible for sending an attribution emails for SAE Manager.
 *
 * @category Service
 * @package  App
 * @subpackage Services/Auth
 * @author     François Dargentolle <francois.dargentolle@etu.univ-amu.fr>
 * @author     William Edelstein <william.edelstein@etu.univ-amu.fr>
 * @license    https://opensource.org/licenses/MIT MIT License
 * @link       https://github.com/RADJOU-Dinesh-24003262/SAEManager/blob/main/App/src/Services/Auth/PasswordResetMailer.php
 */
class AttributionMailer
{
    /**
     * Sends a attribution email to the user about the SAE submission deadline.
     *
     * @return void
     */
    public static function sendAttribution(): void
    {
        Logger::log('MAIL_ATTRIBUTION', 'Starting attribution email process.');
        $subjectOfMail = 'Nouvelle SAE attribuée - SAE Manager';
        $dateBeginFocus = (new DateTime('+1 days'))->format('Y-m-d');
        Logger::log('MAIL_ATTRIBUTION', "Focus date for attribution: $dateBeginFocus");

        $saeSubjectRepo = new PdoSAESubjectRepository();
        $saeSubjects = $saeSubjectRepo->findByBeginDate($dateBeginFocus);
        Logger::log('MAIL_ATTRIBUTION', 'Found ' . count($saeSubjects) . ' subjects starting on focus date.');

        $saeGroupRepo = new PdoSAEGroupRepository();
        $participatedInRepo = new PdoParticipatedInRepository();

        foreach ($saeSubjects as $saeSubject) {
            $saeId = $saeSubject->getSaeSubjectId();

            if ($saeId === null) {
                Logger::log('MAIL_ATTRIBUTION', 'Skipping subject with null ID.', null, 'WARNING');
                continue;
            }

            Logger::log('MAIL_ATTRIBUTION', "Processing SAE ID: $saeId ({$saeSubject->getSubjectName()})");
            $studentsGroup = $saeGroupRepo->findBySaeSubjectId($saeId);

            if (empty($studentsGroup)) {
                Logger::log('MAIL_ATTRIBUTION', "No groups found for SAE ID: $saeId");
                continue;
            }

            Logger::log('MAIL_ATTRIBUTION', 'Found ' . count($studentsGroup) . " groups for SAE ID: $saeId");

            foreach ($studentsGroup as $studentGroup) {
                $groupId = $studentGroup->getSaeGroupId();

                if ($groupId === null) {
                    continue;
                }

                // Use the updated method signature.
                $students = $saeGroupRepo->getStudentsInGroup($groupId);

                Logger::log('MAIL_ATTRIBUTION', 'Found ' . count($students) . " students in group ID: $groupId");

                foreach ($students as $studentData) {
                    $student = new Student($studentData);

                    $emailStudent = $student->getEmail();
                    Logger::log('MAIL_ATTRIBUTION', "Preparing to send email to: $emailStudent");

                    try {
                        $htmlMessage = self::getHtmlTemplate($student, $saeSubject);
                        $textMessage = self::getTextTemplate($student, $saeSubject);
                        EmailService::send($emailStudent, $subjectOfMail, $htmlMessage, $textMessage);
                        Logger::log('MAIL_ATTRIBUTION', "Email sent successfully to: $emailStudent");
                    } catch (\Exception $e) {
                        Logger::log(
                            'MAIL_ATTRIBUTION',
                            "Failed to send email to $emailStudent: " . $e->getMessage(),
                            null,
                            'ERROR'
                        );
                    }
                }
            }
        }
        Logger::log('MAIL_ATTRIBUTION', 'Attribution email process completed.');
    }

    /**
     * Returns the HTML template.
     *
     * @param Student    $student The student.
     * @param SAESubject $subject The SAE subject.
     * @return string
     */
    private static function getHtmlTemplate(Student $student, SAESubject $subject): string
    {
        $year = date('Y');
        $beginDate = (new DateTime($subject->getBeginDate()))->format('d/m/Y');
        $title = htmlspecialchars($subject->getSubjectName());
        $prenom = htmlspecialchars($student->getFirstName());
        $dashboardUrl = "https://saemanager.alwaysdata.net/login"; // URL placeholder.

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
        .header { background-color: #0072ce; color: white; padding: 30px 20px; text-align: center; }
        .header h1 { margin: 0; font-size: 24px; font-weight: 600; letter-spacing: 1px; }
        .content { padding: 40px 30px; }
        .content h2 { color: #0072ce; font-size: 20px; margin-top: 0; border-bottom: 2px solid #f0f0f0; " .
            "padding-bottom: 10px; }
        .info-box { background-color: #eef7ff; border-left: 4px solid #0072ce; padding: 15px; margin: 20px 0; " .
            "border-radius: 4px; }
        .info-item { margin-bottom: 5px; }
        .info-label { font-weight: bold; color: #555; }
        .button-container { text-align: center; margin-top: 30px; }
        .button { display: inline-block; padding: 12px 30px; background-color: #0072ce; color: white; " .
            "text-decoration: none; border-radius: 50px; font-weight: bold; transition: background-color 0.3s; }
        .button:hover { background-color: #005bb5; }
        .footer { background-color: #333; color: #aaa; text-align: center; padding: 20px; font-size: 12px; }
        .footer p { margin: 5px 0; }
    </style>
</head>
<body>
    <div class='wrapper'>
        <div class='container'>
            <div class='header'>
                <h1>SAE Manager</h1>
            </div>
            <div class='content'>
                <p>Bonjour <strong>{$prenom}</strong>,</p>
                
                <p>Une nouvelle Situation d'Apprentissage et d'Évaluation (SAE) vous a été attribuée.</p>
                
                <div class='info-box'>
                    <div class='info-item'><span class='info-label'>Intitulé :</span> {$title}</div>
                    <div class='info-item'><span class='info-label'>Date de démarrage :</span> {$beginDate}</div>
                </div>
                
                <p>Vous pouvez dès à présent consulter les détails de ce projet et contacter votre groupe sur " .
                    "votre espace étudiant.</p>
                
                <div class='button-container'>
                    <a href='{$dashboardUrl}' class='button'>Accéder à mon espace</a>
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
     * @param Student    $student The student.
     * @param SAESubject $subject The SAE subject.
     * @return string
     */
    private static function getTextTemplate(Student $student, SAESubject $subject): string
    {
        $beginDate = (new DateTime($subject->getBeginDate()))->format('d/m/Y');
        $title = $subject->getSubjectName();
        $prenom = $student->getFirstName();
        $dashboardUrl = "https://saemanager.alwaysdata.net/";

        return "
NOUVELLE SAE ATTRIBUÉE - SAE MANAGER
--------------------------------------------------

Bonjour {$prenom},

Une nouvelle Situation d'Apprentissage et d'Évaluation (SAE) vous a été attribuée.

Détails du projet :
- Intitulé : {$title}
- Date de démarrage : {$beginDate}

Connectez-vous à votre espace étudiant pour consulter les détails et votre groupe :
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
        self::sendAttribution(); // Will be changed later.
    }
}

// phpcs:disable PSR1.Files.SideEffects
// Execute if run directly.
if (basename(__FILE__) == basename($_SERVER["SCRIPT_FILENAME"])) {
    require_once __DIR__ . '/../../../../Core/includes/Autoloader.php';
    \Core\includes\Autoloader::register();
    AttributionMailer::main();
}
// phpcs:enable
