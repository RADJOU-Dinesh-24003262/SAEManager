<?php

namespace App\Application\Validation\Rules;

use App\Application\Validation\ValidationRule;

/**
 * TD group validation rule.
 * Validates that TD is one of: TD1, TD2, TD3, TD4.
 *
 * @category Validation
 * @package  App\Application\Validation\Rules
 */
class TDRule implements ValidationRule
{
    public function validate(mixed $value): bool
    {
        return in_array($value, ['TD1', 'TD2', 'TD3', 'TD4'], true);
    }

    public function getMessage(): string
    {
        return "Le groupe TD doit être TD1, TD2, TD3 ou TD4.";
    }
}