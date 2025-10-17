<?php

namespace includes\exception;

/**
 * Class ExceptionPasswordUpdateFailed
 *
 * Custom exception thrown when a password update operation fails.
 *
 * This exception should be used to indicate that the process of updating
 * a user's password did not complete successfully. Common causes may include
 * database errors, invalid input, or permission issues.
 *
 * @package includes\exception
 */
class ExceptionPasswordUpdateFailed extends \Exception
{
    /**
     * ExceptionPasswordUpdateFailed constructor.
     *
     * Initializes the exception with a default or custom message and optional error code.
     *
     * @param string $message Optional. A custom error message. Defaults to: "Échec de la mise à jour du mot de passe."
     * @param int $code Optional. A custom error code. Defaults to 0.
     */
    public function __construct(string $message = "Échec de la mise à jour du mot de passe.", int $code = 0)
    {
        parent::__construct($message, $code);
    }
}
