<?php

namespace App\Application\Validation\Rules;

use App\Application\Validation\ValidationRule;

/**
 * AMU ID validation rule.
 * Validates AMU student/professor ID format (letter + 8+ digits).
 *
 * @category Validation
 * @package  App\Application\Validation\Rules
 */
class AmuIdRule implements ValidationRule
{
    public function validate(mixed $value): bool
    {
        return (bool)preg_match('/^[a-zA-ZÀ-ÿ\-\'][0-9]{8,}$/', $value);
    }

    public function getMessage(): string
    {
        return "L'identifiant AMU n'est pas valide.";
    }
}