<?php

namespace includes\exception;

/**
 * Class ExceptionInvalidToken
 *
 * Custom exception thrown when a token is invalid or has expired.
 *
 * This exception should be used to indicate authentication or security
 * failures related to token validation, such as expired, malformed,
 * or unauthorized tokens.
 *
 * @package includes\exception
 */
class ExceptionInvalidToken extends \Exception
{
    /**
     * ExceptionInvalidToken constructor.
     *
     * Initializes the exception with a default or custom message and optional error code.
     *
     * @param string $message Optional. A custom error message. Defaults to: "Token invalide ou expiré."
     * @param int $code Optional. A custom error code. Defaults to 0.
     */
    public function __construct(string $message = "Token invalide ou expiré.", int $code = 0)
    {
        parent::__construct($message, $code);
    }
}
