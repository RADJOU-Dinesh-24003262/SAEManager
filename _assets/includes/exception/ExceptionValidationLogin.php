<?php
namespace includes\exception;

class ExceptionValidationLogin extends \Exception{
    public function __construct(
        private string $additionalInfo = 'Credentials not valid.') {
        parent::__construct($additionalInfo);
    }
}