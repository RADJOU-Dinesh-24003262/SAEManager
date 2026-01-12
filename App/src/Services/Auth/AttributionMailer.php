<?php

namespace Services\Auth;

use Core\Utilis\EmailService;
use Core\Utilis\Logger;
use Models\SAE\Repository\SAESubjectRepository;
use Models\User\Student;
use Models\User\User;
use Models\SAE\SAESubject;
use DateTime;
use Models\SAE\Repository\SAEGroupRepository;
use Models\SAE\Repository\ParticipatedInRepository;

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
        $subjectOfMail = 'Attribution SAE - SAE Manager';
        $dateBeginFocus = (new DateTime('+1 days'))->format('Y-m-d');
        Logger::log('MAIL_ATTRIBUTION', "Focus date for attribution: $dateBeginFocus");

        $saeSubjectRepo = SAESubjectRepository::getInstance();
        $saeSubjects = $saeSubjectRepo->findByBeginDate($dateBeginFocus);
        Logger::log('MAIL_ATTRIBUTION', 'Found ' . count($saeSubjects) . ' subjects starting on focus date.');

        $saeGroupRepo = SAEGroupRepository::getInstance();
        $participatedInRepo = ParticipatedInRepository::getInstance();

        foreach ($saeSubjects as $saeSubject) {
            $saeId = $saeSubject->getSaeSubjectId();

            if ($saeId === null) {
                Logger::log('MAIL_ATTRIBUTION', 'Skipping subject with null ID.', null, 'WARNING');
                continue;
            }

            Logger::log('MAIL_ATTRIBUTION', "Processing SAE ID: $saeId ({$saeSubject->getSubjectName()})");
            $studentsGroup = $saeGroupRepo->findBySaeId($saeId);

            if (empty($studentsGroup)) {
                Logger::log('MAIL_ATTRIBUTION', "No groups found for SAE ID: $saeId");
                continue;
            }

            Logger::log('MAIL_ATTRIBUTION', 'Found ' . count($studentsGroup) . " groups for SAE ID: $saeId");

            foreach ($studentsGroup as $studentGroup) {
                $groupId = $studentGroup->getSaeGroupId();
                // Utilisation du bon repository pour récupérer les étudiants du groupe
                $students = $participatedInRepo->getGroupStudents($groupId);
                
                Logger::log('MAIL_ATTRIBUTION', 'Found ' . count($students) . " students in group ID: $groupId");

                foreach ($students as $studentData) {
                    $student = new Student($studentData);

                    // Re-fetching subject seems redundant if we already have $saeSubject, 
                    // but keeping logic close to original while logging.
                    // Optimisation: use existing $saeSubject object.
                    
                    $emailStudent = $student->getEmail();
                    Logger::log('MAIL_ATTRIBUTION', "Preparing to send email to: $emailStudent");

                    try {
                        $htmlMessage = self::getHtmlTemplate($student, $saeSubject);
                        $textMessage = self::getTextTemplate($student, $saeSubject);
                        EmailService::send($emailStudent, $subjectOfMail, $htmlMessage, $textMessage);
                        Logger::log('MAIL_ATTRIBUTION', "Email sent successfully to: $emailStudent");
                    } catch (\Exception $e) {
                        Logger::log('MAIL_ATTRIBUTION', "Failed to send email to $emailStudent: " . $e->getMessage(), null, 'ERROR');
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
        $beginDate = $subject->getBeginDate();
        $title = htmlspecialchars($subject->getSubjectName());
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
            <p>Vous avez été attribué à la SAE <strong>{$title}</strong> approche.</p>
            <p>Elle commencera le <strong>{$beginDate}</strong>.</p>
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
     * @param Student    $student The student.
     * @param SAESubject $subject The SAE subject.
     * @return string
     */
    private static function getTextTemplate(Student $student, SAESubject $subject): string
    {
        $beginDate = $subject->getBeginDate();
        $title = htmlspecialchars($subject->getSubjectName());
        $prenom = htmlspecialchars($student->getFirstName());
        return "
Rappel date limite du rendu - SAE Manager

Bonjour {$prenom},

Vous avez été attribué à la SAE {$title} qui commencera le {$beginDate}.
Elle commencera le {$beginDate}.

© 2026 SAE Manager - Aix-Marseille Université
Ceci est un email automatique, merci de ne pas y répondre.
";
    }

    public static function main(): void
    {
        self::sendAttribution(); // Will be changed later.
    }
}
