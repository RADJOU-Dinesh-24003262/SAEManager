<?php
namespace Utilis\Validator;

use includes\exception\ExceptionValidationResetPassword;

class ResetPasswordValidator extends FormValidator
{
    protected $required = ['pwdnew', 'pwdverif'];

    public function validate(array $data): void
    {
        $errors = [];
        if (!$this->isValidPassword($data['pwdnew'])) {
            throw new ExceptionValidationResetPassword('pwdnew', 'string', "Le mot de passe doit contenir au moins 8 caractères.");
        }
        if (($data['pwdnew'] ?? '') !== ($data['pwdverif'] ?? '')) {
            throw new ExceptionValidationResetPassword('pwdverif', 'string', "Les mots de passe ne correspondent pas.");
        }
    }
}