<?php
namespace Utilis\Validator;

use includes\exception\ExceptionValidationRegister;
use includes\exception\ExceptionValidationRegisters;

class LoginValidator extends FormValidator
{
    protected $required = ['email', 'password'];

    public function validate(array $data): void
    {
        //vide car pas de validation spécifique
        //les champs requis sont déjà gérés dans la méthode escape() de la classe parente
    }
}