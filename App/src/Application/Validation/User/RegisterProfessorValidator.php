<?php

namespace App\Application\Validation\User;

use App\Application\Validation\AbstractValidator;
use App\Application\Validation\Exception\RegisterValidationException;
use App\Application\Validation\Exception\RegisterValidationsException;
use App\Application\Validation\Rules\{
    PasswordRule,
    PhoneRule,
    AmuIdRule,
    AmuPrefixRule
};
use Override;

/**
 * Validator for professor registration.
 * Validates professor-specific fields: AMU ID, AMU email prefix.
 *
 * @category Validation
 * @package  App\Application\Validation\User
 */
class RegisterProfessorValidator extends AbstractValidator
{
    protected array $required = [
        'first_name', 'last_name', 'user_type', 'email',
        'password', 'passwordverif', 'phone', 'terms', 'amu_id'
    ];

    #[Override]
    public function validate(array $data): void
    {
        $this->checkRequired($data);
        
        $errors = [];

        // User type must be 'professor'
        if (($data['user_type'] ?? '') !== 'professor') {
            $errors[] = new RegisterValidationException('user_type', 'string', "Type d'utilisateur invalide.");
        }

        // Email (AMU prefix format)
        $amuPrefixRule = new AmuPrefixRule($data['first_name'], $data['last_name']);
        if (!$amuPrefixRule->validate($data['email'])) {
            $errors[] = new RegisterValidationException('email', 'string', $amuPrefixRule->getMessage());
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

        // AMU ID
        $amuIdRule = new AmuIdRule();
        if (!$amuIdRule->validate($data['amu_id'])) {
            $errors[] = new RegisterValidationException('amu_id', 'string', $amuIdRule->getMessage());
        }

        if (!empty($errors)) {
            throw new RegisterValidationsException($errors);
        }
    }
}