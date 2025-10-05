<?php
namespace Utilis\Validator;

use includes\exception\ExceptionValidationEmpty;
use includes\exception\ExceptionValidationEmptys;

/**
 * Classe abstraite pour la validation de formulaires.
 */
abstract class FormValidator
{
    /**
     * Liste des champs requis pour la validation du formulaire.
     * À définir dans les classes enfants.
     * @var array
     */
    protected $required = [];

    /**
     * Échappe les données du formulaire (HTML special chars).
     * @param array $data
     * @param array $fields Liste des champs à échapper
     * @return array Données échappées
     * @throws ExceptionValidationEmptys si un champ requis est vide
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
     * Valide les données du formulaire. À implémenter dans les classes enfants.
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