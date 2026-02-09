<?php

namespace App\Application\Validation;

use App\Application\Validation\Exception\EmptyFieldException;
use App\Application\Validation\Exception\EmptyFieldsException;

/**
 * Abstract base validator for all validation classes.
 * Provides common validation functionality for required fields.
 *
 * @category Validation
 * @package  App\Application\Validation
 * @author   Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license  MIT License https://opensource.org/licenses/MIT
 * @link     https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
abstract class AbstractValidator
{
    /**
     * List of required fields.
     *
     * @var array<string>
     */
    protected array $required = [];

    /**
     * Checks if all required fields are present and not empty.
     *
     * @param array<string, mixed> $data The data to check.
     * @return void
     * @throws EmptyFieldsException If any required field is missing or empty.
     */
    protected function checkRequired(array $data): void
    {
        $errors = [];

        foreach ($this->required as $field) {
            if (empty($data[$field])) {
                $errors[] = new EmptyFieldException($field);
            }
        }

        if (!empty($errors)) {
            throw new EmptyFieldsException($errors);
        }
    }

    /**
     * Validates the given data.
     * This method must be implemented by concrete validators.
     *
     * @param array<string, mixed> $data The data to validate.
     * @return void
     * @throws \Exception If validation fails.
     */
    abstract public function validate(array $data): void;
}