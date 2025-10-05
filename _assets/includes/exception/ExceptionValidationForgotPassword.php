<?php
namespace includes\exception;

class ExceptionValidationForgotPassword extends \Exception{
    public function __construct(
        private string $field,
        private string $type,
        private string $additionalInfo = '') {
        parent::__construct($additionalInfo);
    }
}