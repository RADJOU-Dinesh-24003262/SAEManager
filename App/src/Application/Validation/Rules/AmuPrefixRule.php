<?php

namespace App\Application\Validation\Rules;

use App\Application\Validation\ValidationRule;

/**
 * AMU email prefix validation rule.
 * Validates that an AMU email prefix matches the user's first and last name.
 * Expected format: firstname.lastname[.number]
 *
 * @category Validation
 * @package  App\Application\Validation\Rules
 */
class AmuPrefixRule implements ValidationRule
{
    private string $firstName;
    private string $lastName;

    public function __construct(string $firstName, string $lastName)
    {
        $this->firstName = $firstName;
        $this->lastName = $lastName;
    }

    public function validate(mixed $value): bool
    {
        $f = mb_strtolower(trim($this->firstName), 'UTF-8');
        $l = mb_strtolower(trim($this->lastName), 'UTF-8');

        $escapedF = preg_quote($f, '/');
        $escapedL = preg_quote($l, '/');

        $pattern = "/^{$escapedF}\\.{$escapedL}(\\.[0-9]+)?$/i";

        return (bool)preg_match($pattern, $value);
    }

    public function getMessage(): string
    {
        return "Le préfixe de l'email AMU ne correspond pas au prénom et nom.";
    }
}