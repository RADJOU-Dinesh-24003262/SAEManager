<?php

namespace App\Application\Validation\User;

use App\Application\Validation\AbstractValidator;
use App\Application\Validation\Exception\RegisterValidationException;
use App\Application\Validation\Rules\PhoneRule;
use Override;

/**
 * Validator for profile editing.
 * Validates phone number update.
 *
 * @category Validation
 * @package  App\Application\Validation\User
 */
class EditProfileValidator extends AbstractValidator
{
    protected array $required = ['phone'];

    #[Override]
    public function validate(array $data): void
    {
        $this->checkRequired($data);
        
        $phoneRule = new PhoneRule();
        if (!$phoneRule->validate($data['phone'])) {
            throw new RegisterValidationException('phone', 'string', $phoneRule->getMessage());
        }
    }
}