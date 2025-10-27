<?php

namespace Core\includes\exception;

/**
 * Class ExceptionEmailSendingFailed
 *
 * Custom exception thrown when an email fails to send.
 *
 * This exception should be used to indicate an error occurred during
 * the process of sending an email. It helps differentiate general
 * exceptions from issues specifically related to email functionality.
 *
 * @category   Exception
 * @package    Core
 * @subpackage Includes\Exception
 * @author     Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license    MIT License https://opensource.org/licenses/MIT
 * @link       https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class ExceptionEmailSendingFailed extends \Exception
{
    /**
     * ExceptionEmailSendingFailed constructor.
     *
     * Initializes the exception with a default or custom error message.
     *
     * @param string  $message Optional. Custom error message.
     *                         Defaults to "Erreur lors de l'envoi de l'email. Veuillez réessayer plus tard.".
     * @param integer $code    Optional. Custom error code (default 0).
     */
    public function __construct(
        string $message = "Erreur lors de l'envoi de l'email. Veuillez réessayer plus tard.",
        int $code = 0
    ) {
        parent::__construct($message, $code);
    }
}
