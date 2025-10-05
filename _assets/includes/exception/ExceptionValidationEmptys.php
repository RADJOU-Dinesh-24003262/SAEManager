<?php

namespace includes\exception;

class ExceptionValidationEmptys extends \Exception
{
    /** @var ExceptionValidationRegister[] */
    private array $errors;

    public function __construct(array $errors) {
        parent::__construct("Erreurs de validation : champs vides");
        $this->errors = $errors;
    }

    public function getErrors(): array{
        return $this->errors;
    }
}