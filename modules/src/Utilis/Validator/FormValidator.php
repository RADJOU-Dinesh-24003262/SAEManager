<?php
namespace Utilis\Validator;

use includes\exception\ExceptionValidationEmpty;
use includes\exception\ExceptionValidationEmptys;

/**
 * Abstract class for form validation.
 * Provides methods to escape and validate form data.
 */
abstract class FormValidator
{
    /**
     * List of required fields for form validation.
     * To be defined in child classes.
     * @var array
     */
    protected $required = [];

    /**
     * Escapes form data (HTML special chars).
     * @param array $data
     * @param array $fields List of fields to escape
     * @return array Data with escaped fields
     * @throws ExceptionValidationEmptys if a required field is empty
     */
    public function escape(array $data): array
    {
        $errors = [];
        foreach ($this->required as $field) {
            if (empty($data[$field])) {
                $errors[] = new ExceptionValidationEmpty($field);
            } else {
                $data[$field] = htmlspecialchars($data[$field], ENT_QUOTES, 'UTF-8');
            }
        }

        if (!empty($errors)) {
            throw new ExceptionValidationEmptys($errors);
        }

        return $data;
    }

    /**
     * Validates form data. To be implemented in child classes.
     * @param array $data
     * @throws \Exception
     */
    abstract public function validate(array $data): void;

    protected function isValidUserType(string $userType): bool
    {
        return in_array($userType, ['student', 'professor', 'companies']);
    }

    protected function isValidEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    protected function isOwnAmuEmail($email, $lname, $fname) {
        $pattern = '/^' . strtolower(preg_quote($fname, '/')) . '\.' . strtolower(preg_quote($lname, '/')) . '(\.[0-9]+)?@(etu\.)?univ-amu\.fr$/';
        return preg_match($pattern, $email) && preg_match('/^[a-zA-ZÀ-ÿ\-\']+\.[a-zA-ZÀ-ÿ\-\']+(\.[0-9]+)?@(etu\.)?univ-amu\.fr$/', $email);
    }

    protected function isValidPassword(string $password): bool
    {
        return strlen($password) >= 8;
    }

    protected function isValidPhone(string $phone): bool
    {
        return preg_match('/^0[467][0-9]{8}$/', $phone);
    }

    protected function isValidDate(string $date): bool
    {
        $d = \DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }

    protected function isValidYear(string $year): bool
    {
        return in_array($year, ['1', '2', '3']);
    }

    protected function isValidParcours(string $parcours): bool
    {
        return in_array($parcours, ['A', 'B']);
    }

    protected function isValidTD(string $td): bool
    {
        return in_array($td, ['TD1', 'TD2', 'TD3', 'TD4']);
    }

    protected function isValidTP(string $tp): bool
    {
        return in_array($tp, ['TPA', 'TPB']);
    }

    protected function isValidGender(string $gender): bool
    {
        return in_array($gender, ['male', 'female']);
    }
}