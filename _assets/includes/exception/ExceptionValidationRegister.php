<?php
namespace includes\exception;

class ExceptionValidationRegister extends \Exception{
    public function __construct(
        private string $field,
        private string $type,
        private string $additionalInfo = '') {
        parent::__construct($additionalInfo);
    }
}