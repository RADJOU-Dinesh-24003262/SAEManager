<?php
namespace Utilis\Validator;

use includes\exception\ExceptionValidationLogin;

class LoginValidator extends FormValidator
{
    protected $required = ['username', 'password'];

    public function validate(array $data): void
    {
        if (!$this->isValidEmail($data['username'])) {
            throw new ExceptionValidationLogin( "L'adresse email n'est pas valide.");
        }
    }
}