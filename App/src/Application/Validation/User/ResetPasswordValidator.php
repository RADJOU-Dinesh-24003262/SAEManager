<?php

namespace App\Application\Validation\User;

use App\Application\Validation\AbstractValidator;
use App\Application\Validation\Exception\ResetPasswordValidationException;
use App\Application\Validation\Rules\PasswordRule;
use Override;

/**
 * Validator for password reset.
 * Validates new password and confirmation match.
 *
 * @category Validation
 * @package  App\Application\Validation\User
 */
class ResetPasswordValidator extends AbstractValidator
{
    protected array $required = ['pwdnew', 'pwdverif'];

    #[Override]
    public function validate(array $data): void
    {
        $this->checkRequired($data);
        
        // Validate password strength
        $passwordRule = new PasswordRule();
        if (!$passwordRule->validate($data['pwdnew'])) {
            throw new ResetPasswordValidationException(
                'pwdnew',
                'Not Valid',
                $passwordRule->getMessage()
            );
        }

        // Verify passwords match
        if (($data['pwdnew'] ?? '') !== ($data['pwdverif'] ?? '')) {
            throw new ResetPasswordValidationException(
                'pwdverif',
                'Mismatch',
                'Les mots de passe ne correspondent pas.'
            );
        }
    }
}