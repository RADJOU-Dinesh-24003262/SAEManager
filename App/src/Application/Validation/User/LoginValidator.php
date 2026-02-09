<?php

namespace App\Application\Validation\User;

use App\Application\Validation\AbstractValidator;
use App\Application\Validation\Exception\LoginValidationException;
use App\Application\Validation\Rules\EmailRule;
use Override;

/**
 * Validator for user login.
 * Validates email format for login.
 *
 * @category Validation
 * @package  App\Application\Validation\User
 */
class LoginValidator extends AbstractValidator
{
    protected array $required = ['email', 'password'];

    #[Override]
    public function validate(array $data): void
    {
        $this->checkRequired($data);
        
        $emailRule = new EmailRule();
        if (!$emailRule->validate($data['email'])) {
            throw new LoginValidationException($emailRule->getMessage());
        }
    }
} 