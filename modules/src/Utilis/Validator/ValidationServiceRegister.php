<?php

namespace Utilis\Validator;

use includes\exception\ExceptionValidationRegister;
use includes\exception\ExceptionValidationRegisters;

/**
 * Class ValidationServiceRegister

 * @package     src

 * @subpackage  Utilis\Validator

 * @author      Benhafessa Alexandre, Dargentolle Francois, Edelstein William, Griguer Nathan, Radjou Dinesh

 * This class regroup function to validate the registration process of a user.
 */
class ValidationServiceRegister extends FormValidator
{
    /**
     * The list of the variables required for the registration process of a user.
     * @var array
     */
    protected $required = ['amuId', 'firstName', 'lastName', 'userType', 'email', 'password', 'passwordverif', 'phone', 'dateOfBirth', 'city', 'gender', 'terms'];

    /**
     *
     *
     * This method validates the values given in $data to make a new user with.
     *
     * @param array $data array, in adequation to the required value fields.
     *
     * @return void
     *
     * @throws ExceptionValidationRegisters all the errors that might have been found
     */
    public function validate(array $data): void
    {
        $errors = [];

        // Specific validations
        if (!$this->isValidUserType($data['userType'])) {
            $errors[] = new ExceptionValidationRegister("userType", "string", "Type d'utilisateur invalide.");
        }

        if (!$this->isValidEmail($data['email'])) {
            $errors[] = new ExceptionValidationRegister("email", "string", "Email invalide.");
        } elseif (!$this->isOwnAmuEmail($data['email'], $data['lastName'], $data['firstName'])) {
            $errors[] = new ExceptionValidationRegister("email", "string", "Utilisez votre adresse e-mail universitaire.");
        }

        if (!$this->isValidPassword($data['password'])) {
            $errors[] = new ExceptionValidationRegister("password", "string", "Mot de passe trop court (min 8 caractères).");
        }

        if ($data['password'] !== ($data['passwordverif'] ?? '')) {
            $errors[] = new ExceptionValidationRegister("passwordverif", "string", "Les mots de passe ne correspondent pas.");
        }

        if (!$this->isValidPhone($data['phone'])) {
            $errors[] = new ExceptionValidationRegister("phone", "int", "Numéro de téléphone invalide.");
        }


        if (!$this->isValidDate($data['dateOfBirth'])) {
            $errors[] = new ExceptionValidationRegister("dateOfBirth", "string", "Date de naissance invalide.");
        } else {
            $age = (new \DateTime())->diff(new \DateTime($data['dateOfBirth']))->y;
            if ($age < 16) {
                $errors[] = new ExceptionValidationRegister("dateOfBirth", "string", "Vous devez avoir au moins 16 ans.");
            }
        }

        if (!$this->isValidGender($data['gender'])) {
            $errors[] = new ExceptionValidationRegister("gender", "string", "Veuillez sélectionner un genre valide.");
        }

        // Specific validation for students
        if (($data['userType'] ?? '') === 'student') {
            $studentErrors = $this->validateStudentFields($data);
            $errors = array_merge($errors, $studentErrors);
        }

        if (!empty($errors)) {
            throw new ExceptionValidationRegisters($errors);
        }
    }

    /**
     *
     *
     * This this method validated the values given in $data to make a new student user with.
     *
     * @param array $data array, in adequation to the required value fields.
     *
     * @return void
     *
     * @throws ExceptionValidationRegisters all the errors that might have been found
     */
    private function validateStudentFields(array $data): array
    {
        $errors = [];

        if (empty($data['year'])) {
            $errors[] = new ExceptionValidationRegister('year', 'string', "L'année est requise pour les étudiants.");
        } elseif (!$this->isValidYear($data['year'])) {
            $errors[] = new ExceptionValidationRegister('year', 'string', "Année invalide.");
        }

        if (in_array($data['year'] ?? '', ['2', '3'])) {
            if (empty($data['parcours'])) {
                $errors[] = new ExceptionValidationRegister('parcours', 'string', "Parcours requis en BUT 2 et BUT 3.");
            } elseif (!$this->isValidParcours($data['parcours'])) {
                $errors[] = new ExceptionValidationRegister('parcours', 'string', "Parcours invalide.");
            } elseif (($data['td'] ?? '') === 'TD4') {
                $errors[] = new ExceptionValidationRegister('td', 'string', "TD4 uniquement disponible en BUT 1.");
            }
        } elseif (!empty($data['parcours'])) {
            $errors[] = new ExceptionValidationRegister('parcours', 'string', "Le parcours n'est pas applicable pour cette année.");
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
}
