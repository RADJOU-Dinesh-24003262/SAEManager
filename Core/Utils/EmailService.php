<?php

namespace Core\Utils;

use Core\Includes\Exception\ExceptionEmailSendingFailed;
use Core\Utils\Config;

/**
 * Class EmailService
 * Handles the low-level sending of emails via PHP's mail() function.
 * This class is agnostic of the application logic and email content.
 *
 * @category Service
 * @package  Src
 * @subpackage Core/Utilis

 * @author Alexandre Benhafessa <alexandre.benhafessa@etu.univ-amu.fr>
 * @author François Dargentolle <francois.dargentolle@etu.univ-amu.fr>
 * @author William Edelstein <william.edelstein@etu.univ-amu.fr>
 * @author Nathan Griguer <nathan.griguer@etu.univ-amu.fr>
 * @author Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>

 * @license MIT License https://opensource.org/licenses/MIT

 * @link https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class EmailService
{
    /**
     * Sends an email with both HTML and plain text versions.
     *
     * @param string $to          The recipient's email address.
     * @param string $subject     The subject of the email.
     * @param string $htmlMessage The HTML body content.
     * @param string $textMessage The plain text body content.
     *
     * @return void
     * @throws ExceptionEmailSendingFailed If the email could not be sent.
     */
    public static function send(string $to, string $subject, string $htmlMessage, string $textMessage): void
    {
        // Headers for multipart email (HTML + text).
        $boundary = md5(uniqid('boundary_', true));

        $fromEmail = Config::get('email', 'from_email', 'noreply@saemanager.alwaysdata.net');
        $fromName = Config::get('email', 'from_name', 'SAE Manager');

        $headers = [
            'From' => $fromName . ' <' . $fromEmail . '>',
            'Reply-To' => $fromEmail,
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
