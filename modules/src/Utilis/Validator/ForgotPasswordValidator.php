<?php
namespace Utilis\Validator;

use includes\exception\ExceptionValidationForgotPassword;
use includes\exception\ExceptionSpam;

class ForgotPasswordValidator extends FormValidator
{
    protected $required = ['email'];

    public function validate(array $data): void
    {
        $errors = [];
        if (!$this->isValidEmail($data['email'])) {
            throw new ExceptionValidationForgotPassword('email', 'string', "L'adresse email n'est pas valide.");
        }if(($_SESSION['last_forgot_password_request'] ?? 0) > (time() - 120)) {
            throw new ExceptionSpam("Veuillez attendre au moins 2 minutes avant de refaire une demande.");
        }
    }
}