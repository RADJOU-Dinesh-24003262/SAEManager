<?php

namespace App\Application\Validation\Rules;

use App\Application\Validation\ValidationRule;

/**
 * Email validation rule.
 * Validates that a value is a valid email address.
 *
 * @category Validation
 * @package  App\Application\Validation\Rules
 */
class EmailRule implements ValidationRule
{
    public function validate(mixed $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }

    public function getMessage(): string
    {
        return "L'adresse email n'est pas valide.";
    }
}