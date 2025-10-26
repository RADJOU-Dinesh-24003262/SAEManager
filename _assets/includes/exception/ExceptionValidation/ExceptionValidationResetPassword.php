<?php

namespace includes\exception\ExceptionValidation;

/**
 * Class ExceptionValidationResetPassword
 *
 * Custom exception thrown during password reset validation failures.
 *
 * This exception is used when specific validation rules fail during a password
 * reset process. It provides contextual information about the field that failed,
 * the type of validation error, and optional additional information for debugging
 * or user feedback.
 *
 * @package includes\exception
 */
class ExceptionValidationResetPassword extends \Exception
{
    /**
     * @var string The name of the field that failed validation.
     */
    private string $field;

    /**
     * @var string The type of validation error.
     */
    private string $type;

    /**
     * @var string Optional. Additional information about the validation error.
     */
    private string $additionalInfo;

    /**
     * ExceptionValidationResetPassword constructor.
     *
     * @param string $field The field name that failed validation.
     * @param string $type The type of validation failure.
     * @param string $additionalInfo Optional. Additional error context or message.
     */
    public function __construct(string $field, string $type, string $additionalInfo = '')
    {
        $this->field = $field;
        $this->type = $type;
        $this->additionalInfo = $additionalInfo;

        // Pass additionalInfo as the exception message (if provided)
        parent::__construct($additionalInfo);
    }

    /**
     * Gets the name of the field that caused the validation error.
     *
     * @return string
     */
    public function getField(): string
    {
        return $this->field;
    }

    /**
     * Gets the type of the validation error.
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Gets additional information about the validation failure.
     *
     * @return string
     */
    public function getAdditionalInfo(): string
    {
        return $this->additionalInfo;
    }
}
