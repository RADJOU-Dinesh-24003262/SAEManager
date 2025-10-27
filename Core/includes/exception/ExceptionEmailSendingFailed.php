<?php

namespace Core\includes\exception;

/**
 * Class ExceptionEmailSendingFailed
 *
 * Custom exception thrown when an email fails to send.
 *
 * This exception should be used to indicate an error occurred during
 * the process of sending an email. It can help differentiate between
 * general exceptions and issues specifically related to email functionality.
 *
 * @package includes\exception
 */
class ExceptionEmailSendingFailed extends \Exception
{
    /**
     * ExceptionEmailSendingFailed constructor.
     *
     * Initializes the exception with a default or custom error message.
     *
     * @param string $message Optional. A custom error message.
     * Defaults to: "Erreur lors de l'envoi de l'email. Veuillez réessayer plus tard."
     */
    public function __construct(string $message = "Erreur lors de l'envoi de l'email. Veuillez réessayer plus tard.")
    {
        parent::__construct($message);
    }
}
