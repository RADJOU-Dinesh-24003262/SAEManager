<?php

namespace App\Application\Validation;

/**
 * Interface for validation rules.
 * Each rule validates a specific constraint and provides an error message.
 *
 * @category Validation
 * @package  App\Application\Validation
 * @author   Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license  MIT License https://opensource.org/licenses/MIT
 * @link     https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
interface ValidationRule
{
    /**
     * Validates the given value.
     *
     * @param mixed $value The value to validate.
     * @return bool True if valid, false otherwise.
     */
    public function validate(mixed $value): bool;

    /**
     * Gets the error message for this rule.
     *
     * @return string The error message.
     */
    public function getMessage(): string;
}