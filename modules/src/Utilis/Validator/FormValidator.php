<?php

namespace Utilis\Validator;

use includes\exception\ExceptionValidationEmpty;
use includes\exception\ExceptionValidationEmptys;

/**
 * Class FormValidator
 *
 * @package     src

 * @subpackage  Utilis\Validator

 * @author      Benhafessa Alexandre, Dargentolle Francois, Edelstein William, Griguer Nathan, Radjou Dinesh
 *
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
     * @param array $data of form
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

    /**
     * Returns the validity of the userType field
     *
     * @param string $userType the value to validate
     *
     * @return boolean
     */
    protected function isValidUserType(string $userType): bool
    {
        return in_array($userType, ['student', 'professor', 'companies']);
    }

    /**
     * Returns the validity of the email field
     *
     * @param string $email the value to validate
     *
     * @return boolean
     */
    protected function isValidEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Returns the validity of the amUemail with the first and last name
     * as a amU email should be firstname.lastname[numberIfDuplicated]@(etu\.)?univ-amu\.fr$/
     *
     * @param string $email the value to validate
     * @param string $lname the last name of the user
     * @param string $fname the first name of the user
     *
     * @return boolean
     */
    protected function isOwnAmuEmail($email, $lname, $fname): bool
    {
        $escapedFname = strtolower(preg_quote($fname, '/'));
        $escapedLname = strtolower(preg_quote($lname, '/'));

        $ownEmailPattern = "/^{$escapedFname}\.{$escapedLname}(\.[0-9]+)?@(etu\.)?univ-amu\.fr$/";
        $genericEmailPattern = '/^[a-zA-ZÀ-ÿ\-\'\.]+@[a-z]+\.[a-z\.]+$/';

        return preg_match($ownEmailPattern, $email)
            && preg_match('/^[a-zA-ZÀ-ÿ\-\']+\.[a-zA-ZÀ-ÿ\-\']+(\.[0-9]+)?@(etu\.)?univ-amu\.fr$/', $email);
    }

    /**
     * Returns the validity of the password field
     *
     * @param string $password the value to validate
     *
     * @return boolean
     */
    protected function isValidPassword(string $password): bool
    {
        return strlen($password) >= 8;
    }

    /**
     * Returns the validity of the phone field
     *
     * @param string $phone the value to validate
     *
     * @return boolean
     */
    protected function isValidPhone(string $phone): bool
    {
        return (bool) preg_match('/^0[467][0-9]{8}$/', $phone);
    }

    /**
     * Returns the validity of the date field
     *
     * @param string $date the value to validate
     *
     * @return boolean
     */
    protected function isValidDate(string $date): bool
    {
        $d = \DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }

    /**
     * Returns the validity of the year field
     *
     * @param string $year the value to validate
     *
     * @return boolean
     */
    protected function isValidYear(string $year): bool
    {
        return in_array($year, ['1', '2', '3']);
    }

    /**
     * Returns the validity of the parcours field
     *
     * @param string $parcours the value to validate
     *
     * @return boolean
     */
    protected function isValidParcours(string $parcours): bool
    {
        return in_array($parcours, ['A', 'B']);
    }

    /**
     * Returns the validity of the td field
     *
     * @param string $td the value to validate
     *
     * @return boolean
     */
    protected function isValidTD(string $td): bool
    {
        return in_array($td, ['TD1', 'TD2', 'TD3', 'TD4']);
    }

    /**
     * Returns the validity of the tp field
     *
     * @param string $tp the value to validate
     *
     * @return boolean
     */
    protected function isValidTP(string $tp): bool
    {
        return in_array($tp, ['TPA', 'TPB']);
    }

    /**
     * Returns the validity of the gender field
     *
     * @param string $gender the value to validate
     *
     * @return boolean
     */
    protected function isValidGender(string $gender): bool
    {
        return in_array($gender, ['male', 'female']);
    }
}
