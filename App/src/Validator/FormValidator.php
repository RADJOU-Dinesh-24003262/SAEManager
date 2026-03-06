<?php

namespace Validator;

use Core\includes\exception\ExceptionValidation\ExceptionValidationEmpty;
use Core\includes\exception\ExceptionValidation\ExceptionValidationEmptys;
use DateTime;
use Exception;

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
     * Escapes form data (HTML special chars) recursively.
     *
     * @param  array<array-key, mixed> $data Data of form to espace.
     * @return array<array-key, mixed> Data with escaped fields.
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

        array_walk_recursive($data, function (&$item) {
            if (is_string($item)) {
                $item = htmlspecialchars($item, ENT_QUOTES, 'UTF-8');
            }
        });

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
     * @throws Exception If the data don't meet the requirement.
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
     * Returns the validity of the AMU prefix with the first and last name.
     * The prefix should follow the format: firstname.lastname[.number]
     *
     * @param string $email The email prefix to validate.
     * @param string $fname The first name of the user.
     * @param string $lname The last name of the user.
     *
     * @return boolean
     */
    protected function isOwnAmuPrefix(string $email, string $fname, string $lname): bool
    {
        $f = mb_strtolower(trim($fname), 'UTF-8');
        $l = mb_strtolower(trim($lname), 'UTF-8');

        $escapedF = preg_quote($f, '/');
        $escapedL = preg_quote($l, '/');

        $pattern = "/^{$escapedF}\.{$escapedL}(\.[0-9]+)?$/i";

        return (bool) preg_match($pattern, $email);
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
        if (mb_strlen($password) < 12) {
            return false;
        }
        $hasUppercase = preg_match('/[A-Z]/', $password);
        $hasLowercase = preg_match('/[a-z]/', $password);
        $hasDigit     = preg_match('/[0-9]/', $password);

        $hasSpecialChar = preg_match('#[!"#$%&\'()*+,\-./:;<=>?@[\\\\\]^_`{|}~£€§µ°]#u', $password);
        return $hasUppercase && $hasLowercase && $hasDigit && $hasSpecialChar;
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
        $d = DateTime::createFromFormat('Y-m-d', $date);
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
     * Returns the validity of the major field.
     *
     * @param string $major The value to validate.
     *
     * @return boolean
     */
    protected function isValidMajor(string $major): bool
    {
        return in_array($major, ['A', 'B']);
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
