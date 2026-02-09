<?php

namespace App\Application\Validation\Rules;

use App\Application\Validation\ValidationRule;

/**
 * TP group validation rule.
 * Validates that TP is 'TPA' or 'TPB'.
 *
 * @category Validation
 * @package  App\Application\Validation\Rules
 */
class TPRule implements ValidationRule
{
    public function validate(mixed $value): bool
    {
        return in_array($value, ['TPA', 'TPB'], true);
    }

    public function getMessage(): string
    {
        return "Le groupe TP doit être TPA ou TPB.";
    }
}