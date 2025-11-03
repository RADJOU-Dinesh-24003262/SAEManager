<?php

namespace Core\includes\exception\ExceptionValidation;

class ExceptionValidation extends \Exception
{
    /**
     * @var string Additional information about the login validation failure.
     */
    private string $additionalInfo;

    /**
     * ExceptionValidation constructor.
     *
     * Initializes the exception with additional information about the failure.
     *
     * @param string $additionalInfo Optional. Additional details about the error. Defaults to "Content not valid."
     */
    public function __construct(string $additionalInfo = 'Content not valid.')
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
