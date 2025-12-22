<?php

namespace Validator;

use Core\includes\exception\ExceptionValidation\ExceptionValidationEmpty;
use Core\includes\exception\ExceptionValidation\ExceptionValidationEmptys;

/**
 * Class FormValidator
 * Abstract class for form validation.
 * Provides methods to escape and validate form data.

 * @category Validator

 * @package    Src
 * @subpackage Validator

 * @author Alexandre Benhafessa <alexandre.benhafessa@etu.univ-amu.fr>
 * @author François Dargentolle <francois.dargentolle@etu.univ-amu.fr>
 * @author William Edelstein <william.edelstein@etu.univ-amu.fr>
 * @author Nathan Griguer <nathan.griguer@etu.univ-amu.fr>
 * @author Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>

 * @license MIT License https://opensource.org/licenses/MIT

 * @link https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
abstract class FormValidator
{
    /**
     * List of required fields for form validation.
     * To be defined in child classes.
     *
     * @var array<string>
     */
    protected $required = [];

    /**
     * Escapes form data (HTML special chars).
     *
     * @param  array<string, mixed> $data Data of form to espace.
     * @return array<string, mixed> Data with escaped fields.
     * @throws ExceptionValidationEmptys If a required field is empty.
     */
    public function escape(array $data): array
    {
        $errors = [];

        foreach ($this->required as $field) {
            if (empty($data[$field])) {
                $errors[] = new ExceptionValidationEmpty($field);
            }
        }

        foreach ($data as $key => $value) {
            $data[$key] = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        }

        if (!empty($errors)) {
            throw new ExceptionValidationEmptys($errors);
        }

        return $data;
    }

    /**
     * Validates form data. To be implemented in child classes.
     *
     * @param  array<string, mixed> $data The field to validate.
     * @return void
     * @throws \Exception If the data don't meet the requirement.
     */
    abstract public function validate(array $data): void;

    /**
     * Returns the validity of the userType field
     *
     * @param string $userType The value to validate.
     *
     * @return boolean
     */
    protected function isValidUserType(string $userType): bool
    {
        return in_array($userType, ['student', 'professor', 'client']);
    }

    /**
     * Returns the validity of the email field
     *
     * @param string $email The value to validate.
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
     * @param string $email The value to validate.
     * @param string $lname The last name of the user.
     * @param string $fname The first name of the user.
     *
     * @return boolean
     */
    protected function isOwnAmuEmail(string $email, string $lname, string $fname): bool
    {
        $escapedFname = strtolower(preg_quote($fname, '/'));
        $escapedLname = strtolower(preg_quote($lname, '/'));

        $ownEmailPattern = "/^{$escapedFname}\.{$escapedLname}(\.[0-9]+)?@(etu\.)?univ-amu\.fr$/";
        $genericEmailPattern = '/^[a-zA-ZÀ-ÿ\-\'\.]+@[a-z]+\.[a-z\.]+$/';

        return preg_match($ownEmailPattern, $email)
            && preg_match('/^[a-zA-ZÀ-ÿ\-\']+\.[a-zA-ZÀ-ÿ\-\']+(\.[0-9]+)?@(etu\.)?univ-amu\.fr$/', $email);
    }

    /**
     * Check if Amuid is valid with specific regex.
     *
     * @param  string $amu_id Specific id of a student or a teacher.
     * @return boolean
     */
    protected function isValidAmuId(string $amu_id): bool
    {
        return (bool) preg_match('/^[a-zA-ZÀ-ÿ\-\'][0-9]{8,}$/', $amu_id);
    }

    /**
     * Returns the validity of the password field
     *
     * @param string $password The value to validate.
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
     * @param string $phone The value to validate.
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
     * @param string $date The value to validate.
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
     * @param string $year The value to validate.
     *
     * @return boolean
     */
    protected function isValidYear(string $year): bool
    {
        return in_array($year, ['1', '2', '3']);
    }

    /**
     * Returns the validity of the parcours field.
     *
     * @param string $parcours The value to validate.
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
     * @param string $td The value to validate.
     *
     * @return boolean
     */
    protected function isValidTD(string $td): bool
    {
        return in_array($td, ['TD1', 'TD2', 'TD3', 'TD4']);
    }

    /**
     * Returns the validity of the tp field.
     *
     * @param string $tp The value to validate.
     *
     * @return boolean
     */
    protected function isValidTP(string $tp): bool
    {
        return in_array($tp, ['TPA', 'TPB']);
    }
}
