<?php
namespace Utilis\Validator;

use includes\exception\ExceptionValidationLogin;

class LoginValidator extends FormValidator
{
    protected $required = ['email', 'password'];

    public function validate(array $data): void
    {
        if (!$this->isValidEmail($data['email'])) {
            throw new ExceptionValidationLogin( "L'adresse email n'est pas valide.");
        }
    }
}