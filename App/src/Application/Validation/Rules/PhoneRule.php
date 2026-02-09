<?php

namespace App\Application\Validation\Rules;

use App\Application\Validation\ValidationRule;

/**
 * Phone validation rule.
 * Validates French phone numbers (mobile: 06/07, landline: 04).
 *
 * @category Validation
 * @package  App\Application\Validation\Rules
 */
class PhoneRule implements ValidationRule
{
    public function validate(mixed $value): bool
    {
        return (bool)preg_match('/^0[467][0-9]{8}$/', $value);
    }

    public function getMessage(): string
    {
        return "Le numéro de téléphone n'est pas valide.";
    }
}