<?php

namespace App\Application\Validation\Rules;

use App\Application\Validation\ValidationRule;

/**
 * Password validation rule.
 * Validates that a password has minimum length of 8 characters.
 *
 * @category Validation
 * @package  App\Application\Validation\Rules
 */
class PasswordRule implements ValidationRule
{
    public function validate(mixed $value): bool
    {
        return strlen($value) >= 8;
    }

    public function getMessage(): string
    {
        return "Le mot de passe doit contenir au moins 8 caractères.";
    }
}