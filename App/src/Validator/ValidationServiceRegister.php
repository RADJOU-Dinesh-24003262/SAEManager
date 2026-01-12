<?php

namespace Validator;

use Core\includes\exception\ExceptionValidation\ExceptionValidationRegister;
use Core\includes\exception\ExceptionValidation\ExceptionValidationRegisters;
use Override;

/**
 * Class ValidationServiceRegister
 * This class regroup function to validate the registration process of a user.

 * @category Validator

 * @package Src

 * @subpackage Validator

 * @author Alexandre Benhafessa <alexandre.benhafessa@etu.univ-amu.fr>
 * @author François Dargentolle <francois.dargentolle@etu.univ-amu.fr>
 * @author William Edelstein <william.edelstein@etu.univ-amu.fr>
 * @author Nathan Griguer <nathan.griguer@etu.univ-amu.fr>
 * @author Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>

 * @license MIT License https://opensource.org/licenses/MIT

 * @link https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class ValidationServiceRegister extends FormValidator
{
    /**
     * The list of the variables required for the registration process of a user.
     *
     * @var array<string>
     */
    protected $required = ['first_name', 'last_name', 'user_type',
    'email', 'password', 'passwordverif', 'phone', 'terms'];

    /**
     * This method validates the values given in $data to make a new user with.
     *
     * @param array<string, mixed> $data Array, in adequation to the required value fields.
     *
     * @return void
     *
     * @throws ExceptionValidationRegisters All the errors that might have been found.
     */
    #[Override]
    public function validate(array $data): void
    {
        $errors = [];

        $userType = $this->getString($data, 'user_type');
        $email = $this->getString($data, 'email');
        $password = $this->getString($data, 'password');
        $passwordverif = $this->getString($data, 'passwordverif');
        $phone = $this->getString($data, 'phone');

        // Specific validations.
        if (!$this->isValidUserType($userType)) {
            $errors[] = new ExceptionValidationRegister("user_type", "string", "Type d'utilisateur invalide.");
        }

        if ($userType === 'client') {
            if (!$this->isValidEmail($email)) {
                $errors[] = new ExceptionValidationRegister("email", "string", "Email invalide.");
            }
        } else {
            if (empty($email)) {
                $errors[] = new ExceptionValidationRegister("email", "string", "L'identifiant email est requis.");
            }
        }

        if (!$this->isValidPassword($password)) {
            $errors[] = new ExceptionValidationRegister(
                "password",
                "string",
                "Mot de passe trop court (min 8 caractères)."
            );
        }

        if ($password !== $passwordverif) {
            $errors[] = new ExceptionValidationRegister(
                "passwordverif",
                "string",
                "Les mots de passe ne correspondent pas."
            );
        }

        if (!$this->isValidPhone($phone)) {
            $errors[] = new ExceptionValidationRegister("phone", "int", "Numéro de téléphone invalide.");
        }


        // Specific validation for user_type.
        if ($userType === 'student') {
            $studentErrors = $this->validateStudentFields($data);
            $errors = array_merge($errors, $studentErrors);
        } elseif ($userType === 'professor') {
            // Add professor specific validations here if needed.
            $professorErrors = $this->validateProfessorFields($data);
            $errors = array_merge($errors, $professorErrors);
        } elseif ($userType === 'client') {
            $clientErrors = $this->validateClientFields($data);
            $errors = array_merge($errors, $clientErrors);
        }

        if (!empty($errors)) {
            throw new ExceptionValidationRegisters($errors);
        }
    }

    /**
     * This this method validated the values given in $data to make a new student user with.
     *
     * @param array<string, mixed> $data Array, in adequation to the required value fields.
     *
     * @return array<ExceptionValidationRegister> Array of errors.
     *
     * @throws ExceptionValidationRegisters All the errors that might have been found.
     */
    private function validateStudentFields(array $data): array
    {
        $errors = [];

        $email = $this->getString($data, 'email');
        $firstName = $this->getString($data, 'first_name');
        $lastName = $this->getString($data, 'last_name');
        $amuId = $this->getString($data, 'amu_id');
        $year = $this->getString($data, 'year');
        $major = $this->getString($data, 'major');
        $td = $this->getString($data, 'td');
        $tp = $this->getString($data, 'tp');

        if (!$this->isOwnAmuPrefix($email, $firstName, $lastName)) {
            $errors[] = new ExceptionValidationRegister(
                "email",
                "string",
                "L'email doit correspondre au format prenom.nom (minuscules)."
            );
        }

        if (empty($amuId)) {
            $errors[] = new ExceptionValidationRegister('amu_id', 'string', "Identifiant Amu requis.");
        } elseif (!$this->isValidAmuId($amuId)) {
            $errors[] = new ExceptionValidationRegister("amu_id", "string", "Identifiant Amu invalide.");
        }


        if (empty($year)) {
            $errors[] = new ExceptionValidationRegister('year', 'string', "L'année est requise pour les étudiants.");
        } elseif (!$this->isValidYear($year)) {
            $errors[] = new ExceptionValidationRegister('year', 'string', "Année invalide.");
        }

        if (in_array($year, ['2', '3'])) {
            if (empty($major)) {
                $errors[] = new ExceptionValidationRegister('major', 'string', "Parcours requis en BUT 2 et BUT 3.");
            } elseif (!$this->isValidMajor($major)) {
                $errors[] = new ExceptionValidationRegister('major', 'string', "Parcours invalide.");
            } elseif ($td === 'TD4') {
                $errors[] = new ExceptionValidationRegister('td', 'string', "TD4 uniquement disponible en BUT 1.");
            }
        } elseif (!empty($major)) {
            $errors[] = new ExceptionValidationRegister(
                'major',
                'string',
                "Le parcours n'est pas applicable pour cette année."
            );
        }

        if (empty($td)) {
            $errors[] = new ExceptionValidationRegister('td', 'string', "Groupe TD requis pour les étudiants.");
        } elseif (!$this->isValidTD($td)) {
            $errors[] = new ExceptionValidationRegister('td', 'string', "Groupe TD invalide.");
        }

        if (empty($tp)) {
            $errors[] = new ExceptionValidationRegister('tp', 'string', "Groupe TP requis pour les étudiants.");
        } elseif (!$this->isValidTP($tp)) {
            $errors[] = new ExceptionValidationRegister('tp', 'string', "Groupe TP invalide.");
        }

        return $errors;
    }

    /**
     * This this method validated the values given in $data to make a new professor user with.
    *
    * @param array<string, mixed> $data Array, in adequation to the required value fields.
    *
    * @return array<ExceptionValidationRegister> Array of errors.
    *
    * @throws ExceptionValidationRegisters All the errors that might have been found.
    */
    private function validateProfessorFields(array $data): array
    {
        $errors = [];

        $email = $this->getString($data, 'email');
        $firstName = $this->getString($data, 'first_name');
        $lastName = $this->getString($data, 'last_name');
        $amuId = $this->getString($data, 'amu_id');

        if (!$this->isOwnAmuPrefix($email, $firstName, $lastName)) {
            $errors[] = new ExceptionValidationRegister(
                "email",
                "string",
                "L'email doit correspondre au format prenom.nom (minuscules)."
            );
        }

        if (empty($amuId)) {
            $errors[] = new ExceptionValidationRegister('amu_id', 'string', "Identifiant Amu requis.");
        } elseif (!$this->isValidAmuId($amuId)) {
            $errors[] = new ExceptionValidationRegister("amu_id", "string", "Identifiant Amu invalide.");
        }

        return $errors;
    }

    /**
     * This method validates the values given in $data to make a new client user.
     *
     * @param array<string, mixed> $data Array, corresponding to the required value fields.
     *
     * @return array<ExceptionValidationRegister> Array of errors.
     */
    private function validateClientFields(array $data): array
    {
        $errors = [];
        $organisation = $this->getString($data, 'organisation');

        if (empty($organisation)) {
            $errors[] = new ExceptionValidationRegister(
                'organisation',
                'string',
                "Le nom de l'organisation est requis."
            );
        } elseif (strlen($organisation) > 255) {
            $errors[] = new ExceptionValidationRegister(
                'organisation',
                'string',
                "Le nom de l'organisation ne peut pas dépasser 255 caractères."
            );
        }

        return $errors;
    }
}
