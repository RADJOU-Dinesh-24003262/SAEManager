<?php
namespace includes\exception;

class ExceptionValidationRegisters extends \Exception{
    /** @var ExceptionValidationRegister[] */
    private array $errors;

    public function __construct(array $errors) {
        parent::__construct("Erreurs de validation lors de l'inscription");
        $this->errors = $errors;
    }

    public function getErrors(): array{
        return $this->errors;
    }
}