<?php

namespace App\Application\Validation\Rules;

use App\Application\Validation\ValidationRule;

/**
 * Major validation rule.
 * Validates that major is 'A' or 'B'.
 *
 * @category Validation
 * @package  App\Application\Validation\Rules
 */
class MajorRule implements ValidationRule
{
    public function validate(mixed $value): bool
    {
        return in_array($value, ['A', 'B'], true);
    }

    public function getMessage(): string
    {
        return "Le parcours doit être A ou B.";
    }
}