<?php

namespace App\Application\Validation\User;

use App\Application\Validation\AbstractValidator;
use App\Application\Validation\Exception\RegisterValidationException;
use App\Application\Validation\Exception\RegisterValidationsException;
use App\Application\Validation\Rules\{
    EmailRule,
    PasswordRule,
    PhoneRule
};
use Override;

/**
 * Validator for client registration.
 * Validates client-specific fields: email, organisation.
 *
 * @category Validation
 * @package  App\Application\Validation\User
 */
class RegisterClientValidator extends AbstractValidator
{
    protected array $required = [
        'first_name', 'last_name', 'user_type', 'email',
        'password', 'passwordverif', 'phone', 'terms', 'organisation'
    ];

    #[Override]
    public function validate(array $data): void
    {
        $this->checkRequired($data);
        
        $errors = [];

        // User type must be 'client'
        if (($data['user_type'] ?? '') !== 'client') {
            $errors[] = new RegisterValidationException('user_type', 'string', "Type d'utilisateur invalide.");
        }

        // Email (standard format, not AMU)
        $emailRule = new EmailRule();
        if (!$emailRule->validate($data['email'])) {
            $errors[] = new RegisterValidationException('email', 'string', $emailRule->getMessage());
        }

        // Password
        $passwordRule = new PasswordRule();
        if (!$passwordRule->validate($data['password'])) {
            $errors[] = new RegisterValidationException('password', 'string', $passwordRule->getMessage());
        }

        // Password verification
        if ($data['password'] !== ($data['passwordverif'] ?? '')) {
            $errors[] = new RegisterValidationException(
                'passwordverif',
                'string',
                'Les mots de passe ne correspondent pas.'
            );
        }

        // Phone
        $phoneRule = new PhoneRule();
        if (!$phoneRule->validate($data['phone'])) {
            $errors[] = new RegisterValidationException('phone', 'string', $phoneRule->getMessage());
        }

        // Organisation
        if (strlen($data['organisation']) > 255) {
            $errors[] = new RegisterValidationException(
                'organisation',
                'string',
                "Le nom de l'organisation ne peut pas dépasser 255 caractères."
            );
        }

        if (!empty($errors)) {
            throw new RegisterValidationsException($errors);
        }
    }
}