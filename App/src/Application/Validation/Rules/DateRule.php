<?php

namespace App\Application\Validation\Rules;

use App\Application\Validation\ValidationRule;
use DateTime;

/**
 * Date validation rule.
 * Validates that a value is a valid date in Y-m-d format.
 *
 * @category Validation
 * @package  App\Application\Validation\Rules
 */
class DateRule implements ValidationRule
{
    public function validate(mixed $value): bool
    {
        $date = DateTime::createFromFormat('Y-m-d', $value);
        return $date && $date->format('Y-m-d') === $value;
    }

    public function getMessage(): string
    {
        return "La date n'est pas valide (format attendu: AAAA-MM-JJ).";
    }
}