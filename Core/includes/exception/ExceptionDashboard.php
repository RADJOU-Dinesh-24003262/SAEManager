<?php

namespace Core\includes\exception;

/**
 * Class ExceptionDashboard
 *
 * Custom exception thrown when print of profil process fails.
 *
 * This exception should be used to indicate an error occurred during
 * the display of data user
 *
 * @package includes\exception
 */
class ExceptionDashboard extends \Exception
{
    /**
     * ExceptionDashboard constructor.
     *
     * Initializes the exception with a default or custom message.
     *
     * @param string $message Optional. A custom error message.
     * Defaults to "Erreur lors de l'affichage du profil"
     */
    public function __construct(string $message = 'Erreur lors de l\'affichage du profil')
    {
        parent::__construct($message);
    }
}
