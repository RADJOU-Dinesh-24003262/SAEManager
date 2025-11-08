<?php

namespace Validator\Registration;

use Core\includes\exception\ExceptionValidation\ExceptionValidationRegister;

/**
 * Validator for student registration
 * Handles all student-specific validation rules
 *
 * @category Validator
 * @package  Src
 * @author Alexandre Benhafessa <alexandre.benhafessa@etu.univ-amu.fr>
 * @author François Dargentolle <francois.dargentolle@etu.univ-amu.fr>
 * @author William Edelstein <william.edelstein@etu.univ-amu.fr>
 * @author Nathan Griguer <nathan.griguer@etu.univ-amu.fr>
 * @author Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license  MIT License https://opensource.org/licenses/MIT
 * @link     https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class StudentRegistrationValidator extends AbstractRegistrationValidator
{
    /**
     * Additional required fields for students
     *
     * @var array<string>
     */
    protected $required = [
        'first_name',
        'last_name',
        'user_type',
        'email',
        'password',
        'passwordverif',
        'phone',
        'terms',
        'amu_id',
        'year',
        'td',
        'tp'
    ];

    /**
     * Validates student-specific fields
     *
     * @param array<string, string> $data The form data.
     * @return array<ExceptionValidationRegister> Array of validation errors.
     */
    protected function validateSpecificFields(array $data): array
    {
        $errors = [];

        // Validate AMU email format for students.
        $errors = array_merge($errors, $this->validateStudentEmail($data));

        // Validate AMU ID.
        $errors = array_merge($errors, $this->validateAmuId($data));

        // Validate academic year.
        $errors = array_merge($errors, $this->validateYear($data));

        // Validate parcours (for BUT 2 and 3).
        $errors = array_merge($errors, $this->validateParcours($data));

        // Validate TD group.
        $errors = array_merge($errors, $this->validateTD($data));

        // Validate TP group.
        $errors = array_merge($errors, $this->validateTP($data));

        return $errors;
    }

    /**
     * Validates student AMU email
     *
     * @param array<string, string> $data The form data.
     * @return array<ExceptionValidationRegister>
     */
    private function validateStudentEmail(array $data): array
    {
        $errors = [];

        if (!$this->isOwnAmuEmail($data['email'], $data['last_name'], $data['first_name'])) {
            $errors[] = new ExceptionValidationRegister(
                "email",
                "string",
                "Utilisez votre adresse e-mail universitaire."
            );
        }

        return $errors;
    }

    /**
     * Validates AMU ID
     *
     * @param array<string, string> $data The form data.
     * @return array<ExceptionValidationRegister>
     */
    private function validateAmuId(array $data): array
    {
        $errors = [];

        if (!$this->isValidAmuId($data['amu_id'])) {
            $errors[] = new ExceptionValidationRegister(
                "amu_id",
                "string",
                "Identifiant Amu invalide."
            );
        }

        return $errors;
    }

    /**
     * Validates academic year
     *
     * @param array<string, string> $data The form data.
     * @return array<ExceptionValidationRegister>
     */
    private function validateYear(array $data): array
    {
        $errors = [];

        if (!$this->isValidYear($data['year'])) {
            $errors[] = new ExceptionValidationRegister(
                'year',
                'string',
                "Année invalide."
            );
        }

        return $errors;
    }

    /**
     * Validates parcours (major/specialization)
     *
     * @param array<string, string> $data The form data.
     * @return array<ExceptionValidationRegister>
     */
    private function validateParcours(array $data): array
    {
        $errors = [];
        $year = $data['year'] ?? '';

        // Parcours is required for BUT 2 and 3.
        if (in_array($year, ['2', '3'])) {
            if (empty($data['parcours'])) {
                $errors[] = new ExceptionValidationRegister(
                    'parcours',
                    'string',
                    "Parcours requis en BUT 2 et BUT 3."
                );
            } elseif (!$this->isValidParcours($data['parcours'])) {
                $errors[] = new ExceptionValidationRegister(
                    'parcours',
                    'string',
                    "Parcours invalide."
                );
            }
        } elseif (!empty($data['parcours'])) {
            // Parcours should not be provided for BUT 1.
            $errors[] = new ExceptionValidationRegister(
                'parcours',
                'string',
                "Le parcours n'est pas applicable pour cette année."
            );
        }

        return $errors;
    }

    /**
     * Validates TD (tutorial group)
     *
     * @param array<string, string> $data The form data.
     * @return array<ExceptionValidationRegister>
     */
    private function validateTD(array $data): array
    {
        $errors = [];

        if (empty($data['td'])) {
            $errors[] = new ExceptionValidationRegister(
                'td',
                'string',
                "Groupe TD requis pour les étudiants."
            );
        } elseif (!$this->isValidTD($data['td'])) {
            $errors[] = new ExceptionValidationRegister(
                'td',
                'string',
                "Groupe TD invalide."
            );
        } elseif (in_array($data['year'] ?? '', ['2', '3']) && $data['td'] === 'TD4') {
            // TD4 only available for BUT 1.
            $errors[] = new ExceptionValidationRegister(
                'td',
                'string',
                "TD4 uniquement disponible en BUT 1."
            );
        }

        return $errors;
    }

    /**
     * Validates TP (lab group)
     *
     * @param array<string, string> $data The form data.
     * @return array<ExceptionValidationRegister>
     */
    private function validateTP(array $data): array
    {
        $errors = [];

        if (!$this->isValidTP($data['tp'])) {
            $errors[] = new ExceptionValidationRegister(
                'tp',
                'string',
                "Groupe TP invalide."
            );
        }

        return $errors;
    }
}
