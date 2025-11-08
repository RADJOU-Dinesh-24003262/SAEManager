<?php

namespace Validator\Registration;

use Core\includes\exception\ExceptionValidation\ExceptionValidationRegister;

/**
 * Validator for professor registration
 * Handles all professor-specific validation rules
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
class ProfessorRegistrationValidator extends AbstractRegistrationValidator
{
    /**
     * Additional required fields for professors
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
        'amu_id'
    ];

    /**
     * Validates professor-specific fields
     *
     * @param array<string, string> $data The form data.
     * @return array<ExceptionValidationRegister> Array of validation errors
     */
    protected function validateSpecificFields(array $data): array
    {
        $errors = [];

        // Validate AMU ID.
        if (!$this->isValidAmuId($data['amu_id'])) {
            $errors[] = new ExceptionValidationRegister(
                'amu_id',
                'string',
                "Identifiant Amu invalide."
            );
        }

        // Validate email is AMU email (not etu).
        if (
            !$this->isOwnAmuEmail($data['email'], $data['last_name'], $data['first_name'])
            || !$this->isValidProfessorEmail($data['email'])
        ) {
            $errors[] = new ExceptionValidationRegister(
                'email',
                'string',
                "Utilisez votre adresse e-mail universitaire professionnelle."
            );
        }

        return $errors;
    }

    /**
     * Validates that the email is a valid professor AMU email
     *
     * @param string $email The email to validate.
     * @return boolean
     */
    private function isValidProfessorEmail(string $email): bool
    {
        // Professor emails should be @univ-amu.fr (NOT @etu.univ-amu.fr).
        return preg_match('/^[a-zA-Z\-\'\.]+@univ-amu\.fr$/', $email) === 1;
    }
}
