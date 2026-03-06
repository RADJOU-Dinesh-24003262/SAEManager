<?php

namespace Validator\Register;

use Core\Includes\Exception\ExceptionValidation\ExceptionValidationRegister;
use Core\Includes\Exception\ExceptionValidation\ExceptionValidationRegisters;
use Override;
use Validator\FormValidator;

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

        // Specific validations.
        if (!$this->isValidUserType($data['user_type'])) {
            $errors[] = new ExceptionValidationRegister("user_type", "string", "Type d'utilisateur invalide.");
        }

        if ($data['user_type'] === 'client') {
            if (!$this->isValidEmail($data['email'])) {
                $errors[] = new ExceptionValidationRegister("email", "string", "Email invalide.");
            }
        } else {
            if (empty($data['email'])) {
                $errors[] = new ExceptionValidationRegister("email", "string", "L'identifiant email est requis.");
            }
        }

        if (!$this->isValidPassword($data['password'])) {
            $errors[] = new ExceptionValidationRegister(
                "password",
                "string",
                "Mot de passe trop court (min 8 caractères)."
            );
        }

        if ($data['password'] !== ($data['passwordverif'] ?? '')) {
            $errors[] = new ExceptionValidationRegister(
                "passwordverif",
                "string",
                "Les mots de passe ne correspondent pas."
            );
        }

        if (!$this->isValidPhone($data['phone'])) {
            $errors[] = new ExceptionValidationRegister("phone", "int", "Numéro de téléphone invalide.");
        }


        // Specific validation for user_type.
        if ($data['user_type'] === 'student') {
            $studentErrors = $this->validateStudentFields($data);
            $errors = array_merge($errors, $studentErrors);
        } elseif ($data['user_type'] === 'professor') {
            // Add professor specific validations here if needed.
            $professorErrors = $this->validateProfessorFields($data);
            $errors = array_merge($errors, $professorErrors);
        } elseif ($data['user_type'] === 'client') {
            $clientErrors = $this->validateClientFields($data);
            $errors = array_merge($errors, $clientErrors);
        } else {
            $errors[] = new ExceptionValidationRegister("user_type", "string", "Type d'utilisateur invalide.");
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

        if (!$this->isOwnAmuPrefix($data['email'], $data['first_name'], $data['last_name'])) {
            $errors[] = new ExceptionValidationRegister(
                "email",
                "string",
                "L'email doit correspondre au format prenom.nom (minuscules)."
            );
        }

        if (empty($data['amu_id'])) {
            $errors[] = new ExceptionValidationRegister('amu_id', 'string', "Identifiant Amu requis.");
        } elseif (!$this->isValidAmuId($data['amu_id'])) {
            $errors[] = new ExceptionValidationRegister("amu_id", "string", "Identifiant Amu invalide.");
        }


        if (empty($data['year'])) {
            $errors[] = new ExceptionValidationRegister('year', 'string', "L'année est requise pour les étudiants.");
        } elseif (!$this->isValidYear($data['year'])) {
            $errors[] = new ExceptionValidationRegister('year', 'string', "Année invalide.");
        }

        if (in_array($data['year'] ?? '', ['2', '3'])) {
            if (empty($data['major'])) {
                $errors[] = new ExceptionValidationRegister('major', 'string', "Parcours requis en BUT 2 et BUT 3.");
            } elseif (!$this->isValidMajor($data['major'])) {
                $errors[] = new ExceptionValidationRegister('major', 'string', "Parcours invalide.");
            } elseif (($data['td'] ?? '') === 'TD4') {
                $errors[] = new ExceptionValidationRegister('td', 'string', "TD4 uniquement disponible en BUT 1.");
            }
        } elseif (!empty($data['major'])) {
            $errors[] = new ExceptionValidationRegister(
                'major',
                'string',
                "Le parcours n'est pas applicable pour cette année."
            );
        }

        if (empty($data['td'])) {
            $errors[] = new ExceptionValidationRegister('td', 'string', "Groupe TD requis pour les étudiants.");
        } elseif (!$this->isValidTD($data['td'])) {
            $errors[] = new ExceptionValidationRegister('td', 'string', "Groupe TD invalide.");
        }

        if (empty($data['tp'])) {
            $errors[] = new ExceptionValidationRegister('tp', 'string', "Groupe TP requis pour les étudiants.");
        } elseif (!$this->isValidTP($data['tp'])) {
            $errors[] = new ExceptionValidationRegister('tp', 'string', "Groupe TP invalide.");
        }

        return $errors;
    }

    /**
     * This this method validated the values given in $data to make a new professor user with.
    *
    * @param array<string, mixed> $data Array, in adequation to the required value fields.
    *
    * @return array<mixed> Array of errors.
    *
    * @throws ExceptionValidationRegisters All the errors that might have been found.
    */
    private function validateProfessorFields(array $data): array
    {
        $errors = [];

        if (!$this->isOwnAmuPrefix($data['email'], $data['first_name'], $data['last_name'])) {
            $errors[] = new ExceptionValidationRegister(
                "email",
                "string",
                "L'email doit correspondre au format prenom.nom (minuscules)."
            );
        }

        if (empty($data['amu_id'])) {
            $errors[] = new ExceptionValidationRegister('amu_id', 'string', "Identifiant Amu requis.");
        } elseif (!$this->isValidAmuId($data['amu_id'])) {
            $errors[] = new ExceptionValidationRegister("amu_id", "string", "Identifiant Amu invalide.");
        }

        if (!$this->isOwnAmuPrefix($data['email'], $data['first_name'], $data['last_name'])) {
            $errors[] = new ExceptionValidationRegister(
                "email",
                "string",
                "L'email doit correspondre au format prenom.nom (minuscules)."
            );
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

        if (empty($data['organisation'])) {
            $errors[] = new ExceptionValidationRegister(
                'organisation',
                'string',
                "Le nom de l'organisation est requis."
            );
        } elseif (strlen($data['organisation']) > 255) {
            $errors[] = new ExceptionValidationRegister(
                'organisation',
                'string',
                "Le nom de l'organisation ne peut pas dépasser 255 caractères."
            );
        }

        return $errors;
    }
}
