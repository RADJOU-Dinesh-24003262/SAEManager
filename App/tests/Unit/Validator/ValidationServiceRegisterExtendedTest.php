<?php

namespace Validator;

use Core\includes\exception\ExceptionValidation\ExceptionValidationRegisters;
use Core\includes\exception\ExceptionValidation\ExceptionValidationEmptys;
use Core\includes\exception\ExceptionValidation\ExceptionValidationEmpty;
use Core\includes\exception\ExceptionValidation\ExceptionValidationRegister;

class ValidationServiceRegister extends FormValidator
{
    /**
     * Sanitizes data and checks for missing required fields.
     * Throws ExceptionValidationEmptys if fields are empty.
     */
    public function escape(array $data): array
    {
        $escapedData = [];
        $missingFields = [];

        // Define fields that MUST NOT be empty
        $required = ['amu_id', 'first_name', 'last_name', 'user_type', 'email', 'password', 'passwordverif'];

        foreach ($data as $key => $value) {
            $escapedData[$key] = htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
        }

        foreach ($required as $field) {
            if (!isset($escapedData[$field]) || $escapedData[$field] === '') {
                $missingFields[] = $field;
            }
        }

        if (!empty($missingFields)) {
            // Your test expects ExceptionValidationEmptys for missing fields
            throw new ExceptionValidationEmptys("Missing required fields");
        }

        return $escapedData;
    }

    /**
     * Orchestrates the validation logic based on user type.
     */
    public function validate(array $data): void
    {
        $errors = [];

        // 1. Validate User Type
        $validTypes = ['student', 'professor', 'client'];
        if (!in_array($data['user_type'], $validTypes)) {
            $errors[] = "Invalid user type.";
        }

        // 2. Validate Password
        if (strlen($data['password']) < 8) {
            $errors[] = "Password too short.";
        }
        if ($data['password'] !== $data['passwordverif']) {
            $errors[] = "Passwords do not match.";
        }

        // 3. Validate Phone (French format: 06, 07, or 04 landline)
        if (!preg_match('/^0[467][0-9]{8}$/', $data['phone'])) {
            $errors[] = "Invalid phone format.";
        }

        // 4. Conditional Validation for Students
        if ($data['user_type'] === 'student') {
            $this->validateStudentFields($data, $errors);
        }

        if (!empty($errors)) {
            throw new ExceptionValidationRegisters("Validation failed: " . implode(', ', $errors));
        }
    }

    /**
     * Logic specific to student registration
     */
    private function validateStudentFields(array $data, array &$errors): void
    {
        // Check presence of mandatory student fields
        if (!isset($data['year'])) {
            $errors[] = "Year missing.";
            return;
        }
        if (!isset($data['td'])) {
            $errors[] = "TD missing.";
            return;
        }
        if (!isset($data['tp'])) {
            $errors[] = "TP missing.";
            return;
        }

        $year = $data['year'];

        // Validate Year range
        if (!in_array($year, ['1', '2', '3'])) {
            $errors[] = "Invalid year.";
        }

        // Parcours Logic
        if (($year === '2' || $year === '3')) {
            if (!isset($data['parcours']) || !in_array($data['parcours'], ['A', 'B'])) {
                $errors[] = "Parcours A or B required for L2/L3.";
            }
        }

        if ($year === '1' && isset($data['parcours'])) {
            $errors[] = "Year 1 cannot have a parcours.";
        }

        // Specific TD/Year restriction (Year 2 cannot have TD4)
        if ($year === '2' && ($data['td'] ?? '') === 'TD4') {
            $errors[] = "TD4 is not available for Year 2.";
        }
    }
}
