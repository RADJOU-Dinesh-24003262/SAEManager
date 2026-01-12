<?php

namespace Services\Auth;

use Core\Utilis\EmailService;
use Core\Utilis\Logger;
use Models\SAE\Repository\SAESubjectRepository;
use Models\SAE\Repository\SAEGroupRepository;
use Models\SAE\Repository\ParticipatedInRepository;
use Models\User\Student;
use DateTime;
use Models\SAE\SAESubject;

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
        $subjectOfMail = 'Rappel date limite du rendu - SAE Manager';

        $dateEndFocus = (new DateTime('+3 days'))->format('Y-m-d');
        Logger::log('MAIL_LAST_DATE', "Focus date for reminder: $dateEndFocus");

        $saeSubjectRepo = SAESubjectRepository::getInstance();
        $saeSubjects = $saeSubjectRepo->findByEndDate($dateEndFocus);
        Logger::log('MAIL_LAST_DATE', 'Found ' . count($saeSubjects) . ' subjects ending on focus date.');

        $saeGroupRepo = SAEGroupRepository::getInstance();
        $participatedInRepo = ParticipatedInRepository::getInstance();

        foreach ($saeSubjects as $saeSubject) {
            $saeId = $saeSubject->getSaeSubjectId();

            if ($saeId === null) {
                Logger::log('MAIL_LAST_DATE', 'Skipping subject with null ID.', null, 'WARNING');
                continue;
            }

            Logger::log('MAIL_LAST_DATE', "Processing SAE ID: $saeId ({$saeSubject->getSubjectName()})");
            $studentsGroup = $saeGroupRepo->findBySaeId($saeId);

            if (empty($studentsGroup)) {
                Logger::log('MAIL_LAST_DATE', "No groups found for SAE ID: $saeId");
                continue;
            }
            
            Logger::log('MAIL_LAST_DATE', 'Found ' . count($studentsGroup) . " groups for SAE ID: $saeId");

            foreach ($studentsGroup as $studentGroup) {
                $groupId = $studentGroup->getSaeGroupId();
                $students = $participatedInRepo->getGroupStudents($groupId);
                
                Logger::log('MAIL_LAST_DATE', 'Found ' . count($students) . " students in group ID: $groupId");

                foreach ($students as $studentData) {
                    $student = new Student($studentData);

                    // Re-fetching subject is redundant, using existing $saeSubject
                    // but keeping logic structure similar for now, just optimized slightly.
                    
                    $emailStudent = $student->getEmail();
                    Logger::log('MAIL_LAST_DATE', "Preparing to send email to: $emailStudent");

                    try {
                        $htmlMessage = self::getHtmlTemplate($student, $saeSubject);
                        $textMessage = self::getTextTemplate($student, $saeSubject);
                        EmailService::send($emailStudent, $subjectOfMail, $htmlMessage, $textMessage);
                        Logger::log('MAIL_LAST_DATE', "Email sent successfully to: $emailStudent");
                    } catch (\Exception $e) {
                        Logger::log('MAIL_LAST_DATE', "Failed to send email to $emailStudent: " . $e->getMessage(), null, 'ERROR');
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
     * @param SAESubject $subject The SAE subject.
     * @return string
     */
    private static function getHtmlTemplate(Student $student, SAESubject $subject): string
    {
        $year = date('Y');
        $endDate = $subject->getEndDate();
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
     * @param Student $student The student.
     * @param SAESubject $subject The SAE subject.
     * @return string
     */
    private static function getTextTemplate(Student $student, SAESubject $subject): string
    {
        $title = htmlspecialchars($subject->getSubjectName());
        $prenom = htmlspecialchars($student->getFirstName());
        $endDate = DateTime::createFromFormat('Y-m-d', $subject->getEndDate());
        $endDate = $endDate->format('Y-m-d');
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
