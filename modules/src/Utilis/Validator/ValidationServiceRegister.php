<?php
namespace Utilis\Validator;

use includes\exception\ExceptionValidationRegister;
use includes\exception\ExceptionValidationRegisters;

class ValidationServiceRegister extends FormValidator
{   
    protected $required = ['id', 'fname', 'lname', 'user_type', 'email', 'pwd', 'pwdverif', 'tel', 'dob', 'city', 'gender', 'terms'];

    public function validate(array $data): void
    {
        $errors = [];

        // Specific validations
        if (!$this->isValidUserType($data['user_type'])) {
            $errors[] = new ExceptionValidationRegister("user_type", "string", "Type d'utilisateur invalide.");
        }

        if (!$this->isValidEmail($data['email'])) {
            $errors[] = new ExceptionValidationRegister("email", "string", "Email invalide.");
        } elseif (!$this->isOwnAmuEmail($data['email'], $data['lname'], $data['fname'])) {
            $errors[] = new ExceptionValidationRegister("email", "string", "Utilisez votre adresse e-mail universitaire.");
        }

        if (!$this->isValidPassword($data['pwd'])) {
            $errors[] = new ExceptionValidationRegister("pwd", "string", "Mot de passe trop court (min 8 caractères).");
        }
        
        if ($data['pwd'] !== ($data['pwdverif'] ?? '')) {
            $errors[] = new ExceptionValidationRegister("pwdverif", "string", "Les mots de passe ne correspondent pas.");
        }

        if (!$this->isValidPhone($data['tel'])) {
            $errors[] = new ExceptionValidationRegister("tel", "int", "Numéro de téléphone invalide.");
        }


        if (!$this->isValidDate($data['dob'])) {
            $errors[] = new ExceptionValidationRegister("dob", "string", "Date de naissance invalide.");
        } else {
            $age = (new \DateTime())->diff(new \DateTime($data['dob']))->y;
            if ($age < 16) {
                $errors[] = new ExceptionValidationRegister("dob", "string", "Vous devez avoir au moins 16 ans.");
            }
        }

        if (!$this->isValidGender($data['gender'])) {
            $errors[] = new ExceptionValidationRegister("gender", "string", "Veuillez sélectionner un genre valide.");
        }

        // Specific validation for students
        if (($data['user_type'] ?? '') === 'student') {
            $studentErrors = $this->validateStudentFields($data);
            $errors = array_merge($errors, $studentErrors);
        }

        if (!empty($errors)) {
            throw new ExceptionValidationRegisters($errors);
        }
    }

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