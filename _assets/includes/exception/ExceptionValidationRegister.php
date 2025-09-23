<?php
namespace _assets\includes\exeption;

class ExeptionValidationRegister extends \Exception{
    public function __construct(
        private string $field,
        private string $type,
        private string $additionalInfo = '') {
        parent::__construct($message);
    }
}