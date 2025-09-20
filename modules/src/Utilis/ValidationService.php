<?php
namespace Utilis;

class ValidationService
{
    public function validateRegistrationData(array $data): array
    {
        $errors = [];

        // Validation des champs requis
        $required = ['id', 'fname', 'lname', 'gender', 'user_type', 'email', 'pwd', 'pwdverif', 'tel', 'dob', 'city'];
        
        foreach ($required as $field) {
            if (empty($data[$field])) {
                $errors[$field] = "Le champ $field est requis.";
            }
        }

        // Validations spécifiques
        if (!empty($data['gender']) && !$this->isValidGender($data['gender'])) {
            $errors['gender'] = "Civilité invalide.";
        }

        if (!empty($data['user_type']) && !$this->isValidUserType($data['user_type'])) {
            $errors['user_type'] = "Type d'utilisateur invalide.";
        }

        if (!empty($data['email']) && !$this->isValidEmail($data['email'])) {
            $errors['email'] = "Email invalide.";
        } elseif (!$this->isOwnEmail($data['email'], $data['lname'], $data['fname'])) {
            $errors['email'] = "Utilisez votre adresse e-mail universitaire.";
        }


        if (!empty($data['pwd'])) {
            if (!$this->isValidPassword($data['pwd'])) {
                $errors['pwd'] = "Mot de passe trop court (min 8 caractères).";
            }
            
            if ($data['pwd'] !== ($data['pwdverif'] ?? '')) {
                $errors['pwdverif'] = "Les mots de passe ne correspondent pas.";
            }
        }

        if (!empty($data['tel']) && !$this->isValidPhone($data['tel'])) {
            $errors['tel'] = "Numéro de téléphone invalide.";
        }

        if (!empty($data['dob'])) {
            if (!$this->isValidDate($data['dob'])) {
                $errors['dob'] = "Date de naissance invalide.";
            } else {
                $age = (new \DateTime())->diff(new \DateTime($data['dob']))->y;
                if ($age < 16) {
                    $errors['dob'] = "Vous devez avoir au moins 16 ans.";
                }
            }
        }

        // Validation spécifique aux étudiants
        if (($data['user_type'] ?? '') === 'student') {
            $errors = array_merge($errors, $this->validateStudentFields($data));
        }

        return $errors;
    }

    private function validateStudentFields(array $data): array
    {
        $errors = [];

        if (empty($data['year'])) {
            $errors['year'] = "L'année est requise pour les étudiants.";
        } elseif (!$this->isValidYear($data['year'])) {
            $errors['year'] = "Année invalide.";
        }

        if (in_array($data['year'] ?? '', ['2', '3'])) {
            if (empty($data['parcours'])) {
                $errors['parcours'] = "Parcours requis en BUT 2 et BUT 3.";
            } elseif (!$this->isValidParcours($data['parcours'])) {
                $errors['parcours'] = "Parcours invalide.";
            }
        }

        if (empty($data['td'])) {
            $errors['td'] = "Groupe TD requis pour les étudiants.";
        } elseif (!$this->isValidTD($data['td'])) {
            $errors['td'] = "Groupe TD invalide.";
        }

        if (empty($data['tp'])) {
            $errors['tp'] = "Groupe TP requis pour les étudiants.";
        } elseif (!$this->isValidTP($data['tp'])) {
            $errors['tp'] = "Groupe TP invalide.";
        }

        if (in_array($data['year'] ?? '', ['2', '3']) && ($data['td'] ?? '') === 'TD4') {
            $errors['td'] = "TD4 uniquement disponible en BUT 1.";
        }

        return $errors;
    }

    private function isValidGender(string $gender): bool
    {
        return in_array($gender, ['male', 'female', 'other']);
    }

    private function isValidUserType(string $userType): bool
    {
        return in_array($userType, ['student', 'professor', 'companies']);
    }

    private function isValidEmail(string $email): bool
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }
        
        // Validation spécifique AMU
        return preg_match('/^[a-zA-ZÀ-ÿ\-\']+\.[a-zA-ZÀ-ÿ\-\']+(\.[0-9]+)?@(etu\.)?univ-amu\.fr$/', $email);
    }

    function isOwnEmail($email, $lname, $fname) {
        $pattern = '/^' . strtolower(preg_quote($fname, '/')) . '\.' . strtolower(preg_quote($lname, '/')) . '(\.[0-9]+)?@(etu\.)?univ-amu\.fr$/';
        return preg_match($pattern, $email);
    }

    private function isValidPassword(string $password): bool
    {
        return strlen($password) >= 8;
    }

    private function isValidPhone(string $phone): bool
    {
        return preg_match('/^0[467][0-9]{8}$/', $phone);
    }

    private function isValidDate(string $date): bool
    {
        $d = \DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }

    private function isValidYear(string $year): bool
    {
        return in_array($year, ['1', '2', '3']);
    }

    private function isValidParcours(string $parcours): bool
    {
        return in_array($parcours, ['A', 'B']);
    }

    private function isValidTD(string $td): bool
    {
        return in_array($td, ['TD1', 'TD2', 'TD3', 'TD4']);
    }

    private function isValidTP(string $tp): bool
    {
        return in_array($tp, ['TPA', 'TPB']);
    }
}