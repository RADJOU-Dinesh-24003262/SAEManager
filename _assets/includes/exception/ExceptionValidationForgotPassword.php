<?php
namespace includes\exception;

class ExceptionValidationForgotPassword extends \Exception{
    public function __construct(
        private string $field,
        private string $type,
        private string $additionalInfo = '') {
        parent::__construct($additionalInfo);
    }

    public function getField(): string {
        return $this->field;
    }

    public function getType(): string {
        return $this->type;
    }

    public function getAdditionalInfo(): string {
        return $this->additionalInfo;
    }
}