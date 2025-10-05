<?php
namespace Utilis\Validator;

use includes\exception\ExceptionValidationRegister;
use includes\exception\ExceptionValidationRegisters;

class ForgotPasswordValidator extends FormValidator
{
    protected array $required = ['email'];

    public function validate(array $data): void
    {
        $errors = [];
        if (!$this->isValidEmail($data['email'])) {
            $errors[] = new ExceptionValidationRegister('email', 'string', "L'adresse email n'est pas valide.");
        }
        if (!empty($errors)) {
            throw new ExceptionValidationRegisters($errors);
        }
    }
}