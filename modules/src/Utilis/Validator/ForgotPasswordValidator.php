<?php
namespace Utilis\Validator;

use includes\exception\ExceptionValidationForgotPassword;

class ForgotPasswordValidator extends FormValidator
{
    protected $required = ['email'];

    public function validate(array $data): void
    {
        $errors = [];
        if (!$this->isValidEmail($data['email'])) {
            throw new ExceptionValidationForgotPassword('email', 'string', "L'adresse email n'est pas valide.");
        }
    }
}