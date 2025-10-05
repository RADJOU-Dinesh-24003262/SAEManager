<?php
namespace Utilis\Validator;

use includes\exception\ExceptionValidationRegister;
use includes\exception\ExceptionValidationRegisters;

class ResetPasswordValidator extends FormValidator
{
    protected array $required = ['pwdnew', 'pwdverif'];

    public function validate(array $data): void
    {
        $errors = [];
        if (!$this->isValidPassword($data['pwdnew'])) {
            $errors[] = new ExceptionValidationRegister('pwdnew', 'string', "Le mot de passe doit contenir au moins 8 caractères.");
        }
        if (($data['pwdnew'] ?? '') !== ($data['pwdverif'] ?? '')) {
            $errors[] = new ExceptionValidationRegister('pwdverif', 'string', "Les mots de passe ne correspondent pas.");
        }
        if (!empty($errors)) {
            throw new ExceptionValidationRegisters($errors);
        }
    }
}