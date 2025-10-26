<?php
namespace includes\exception\ExceptionValidation;

class ExceptionValidationEmpty extends \Exception
{
    public function __construct(string $fieldName = "", int $code = 0)
    {
        $message = $fieldName
            ? "Le champ '$fieldName' ne doit pas être vide."
            : "Un champ obligatoire est vide.";
        parent::__construct($message, $code );
    }
}