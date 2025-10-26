<?php

namespace includes\exception\ExceptionValidation;

/**
 * Class ExceptionValidationLogin
 *
 * Custom exception thrown when login credentials validation fails.
 *
 * This exception is intended to be used when user authentication fails due to
 * invalid credentials. It includes an optional additional information string
 * to provide more context about the failure.
 *
 * @package includes\exception
 */
class ExceptionValidationLogin extends \Exception
{
    /**
     * @var string Additional information about the login validation failure.
     */
    private string $additionalInfo;

    /**
     * ExceptionValidationLogin constructor.
     *
     * Initializes the exception with additional information about the login failure.
     *
     * @param string $additionalInfo Optional. Additional details about the error. Defaults to "Credentials not valid."
     */
    public function __construct(string $additionalInfo = 'Credentials not valid.')
    {
        $this->additionalInfo = $additionalInfo;
        parent::__construct($additionalInfo);
    }

    /**
     * Returns additional information about the validation failure.
     *
     * @return string The additional information provided at exception creation.
     */
    public function getAdditionalInfo(): string
    {
        return $this->additionalInfo;
    }
}
