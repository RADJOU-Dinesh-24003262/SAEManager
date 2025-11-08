<?php

namespace Validator\Registration;

use Core\includes\exception\ExceptionValidation\ExceptionValidationRegister;

/**
 * Validator for client/partner registration
 * Handles all client-specific validation rules
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
class ClientRegistrationValidator extends AbstractRegistrationValidator
{
    /**
     * Additional required fields for clients
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
        'organisation'
    ];

    /**
     * Validates client-specific fields
     *
     * @param array<string, string> $data The form data.
     * @return array<ExceptionValidationRegister> Array of validation errors.
     */
    protected function validateSpecificFields(array $data): array
    {
        $errors = [];

        // Validate organisation.
        if (empty($data['organisation'])) {
            $errors[] = new ExceptionValidationRegister(
                'organisation',
                'string',
                "Le nom de l'organisation est requis."
            );
        } elseif (!$this->isValidOrganisation($data['organisation'])) {
            $errors[] = new ExceptionValidationRegister(
                'organisation',
                'string',
                "Le nom de l'organisation est invalide."
            );
        }

        return $errors;
    }

    /**
     * Validates organisation name
     *
     * @param string $organisation The organisation name.
     * @return boolean
     */
    private function isValidOrganisation(string $organisation): bool
    {
        // Organisation should be at least 2 characters and not contain special chars.
        return strlen($organisation) >= 2
            && preg_match('/^[a-zA-ZÀ-ÿ0-9\s\-\'\.]+$/', $organisation) === 1;
    }
}
