<?php

namespace Utilis\Validator;

use includes\exception\ExceptionValidationResetPassword;

class ResetPasswordValidator extends FormValidator
{
    protected $required = ['pwdnew', 'pwdverif'];

    public function validate(array $data): void
    {
        if (!$this->isValidPassword($data['pwdnew'])) {
            throw new ExceptionValidationResetPassword(
                'pwdnew',
                'Not Valid',
                "Le mot de passe doit contenir au moins 8 caractères."
            );
        }
        if (($data['pwdnew'] ?? '') !== ($data['pwdverif'] ?? '')) {
            throw new ExceptionValidationResetPassword(
                'pwdverif',
                'Mismatch',
                "Les mots de passe ne correspondent pas."
            );
        }
    }
}
