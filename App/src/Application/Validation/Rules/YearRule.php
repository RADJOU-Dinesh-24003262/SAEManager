<?php

namespace App\Application\Validation\Rules;

use App\Application\Validation\ValidationRule;

/**
 * Academic year validation rule.
 * Validates that year is 1, 2, or 3 (BUT 1, 2, 3).
 *
 * @category Validation
 * @package  App\Application\Validation\Rules
 */
class YearRule implements ValidationRule
{
    public function validate(mixed $value): bool
    {
        return in_array($value, ['1', '2', '3'], true);
    }

    public function getMessage(): string
    {
        return "L'année doit être 1, 2 ou 3.";
    }
}