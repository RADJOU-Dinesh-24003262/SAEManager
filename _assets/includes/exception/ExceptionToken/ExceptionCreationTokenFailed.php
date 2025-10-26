<?php

namespace includes\exception\ExceptionToken;

/**
 * Class ExceptionCreationTokenFailed
 *
 * Custom exception thrown when a token creation process fails.
 *
 * This exception should be used to indicate an error occurred during
 * the generation of a token, such as a failure in a cryptographic function,
 * a system error, or an unexpected condition in the token creation logic.
 *
 * @package includes\exception
 */
class ExceptionCreationTokenFailed extends \Exception
{
    /**
     * ExceptionCreationTokenFailed constructor.
     *
     * Initializes the exception with a default or custom message.
     *
     * @param string $message Optional. A custom error message.
     * Defaults to "Erreur lors de la création du token. Veuillez réessayer plus tard."
     */
    public function __construct(string $message = "Erreur lors de la création du token. Veuillez réessayer plus tard.")
    {
        parent::__construct($message);
    }
}
