<?php

namespace App\Application\Validation\Rules;

use App\Application\Validation\ValidationRule;

/**
 * User type validation rule.
 * Validates that a user type is one of: student, professor, client.
 *
 * @category Validation
 * @package  App\Application\Validation\Rules
 */
class UserTypeRule implements ValidationRule
{
    public function validate(mixed $value): bool
    {
        return in_array($value, ['student', 'professor', 'client'], true);
    }

    public function getMessage(): string
    {
        return "Le type d'utilisateur n'est pas valide.";
    }
}