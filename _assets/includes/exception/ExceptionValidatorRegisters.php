<?php
namespace _assets\includes\exeption;

class ExeptionValidationRegisters extends \Exception
{
    /** @var ExceptionValidatorRegister[] */
    private array $errors;

    public function __construct(array $errors) {
        parent::__construct("Erreurs de validation lors de l'inscription");
        $this->errors = $errors;
    }
}