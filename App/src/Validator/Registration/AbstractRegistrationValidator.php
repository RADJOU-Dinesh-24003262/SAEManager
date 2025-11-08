<?php

namespace Validator\Registration;

use Validator\FormValidator;
use Core\includes\exception\ExceptionValidation\ExceptionValidationRegister;
use Core\includes\exception\ExceptionValidation\ExceptionValidationRegisters;

/**
 * Abstract base validator for all registration types
 * Contains common validation logic for all user types
 *
 * @category Validator
 * @package  Src
 * @author   Alexandre Benhafessa <alexandre.benhafessa@etu.univ-amu.fr>
 * @author   François Dargentolle <francois.dargentolle@etu.univ-amu.fr>
 * @author   William Edelstein <william.edelstein@etu.univ-amu.fr>
 * @author   Nathan Griguer <nathan.griguer@etu.univ-amu.fr>
 * @author   Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license  MIT License https://opensource.org/licenses/MIT
 * @link     https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
abstract class AbstractRegistrationValidator extends FormValidator
{
    /**
     * Validates data for registration
     *
     * @param array<string, string> $data The form data to validate.
     * @return void
     * @throws ExceptionValidationRegisters If validation fails.
     */
    public function validate(array $data): void
    {
        $errors = [];

        // Validate common fields.
        $errors = array_merge($errors, $this->validateCommonFields($data));

        // Validate type-specific fields (implemented by child classes).
        $errors = array_merge($errors, $this->validateSpecificFields($data));

        if (!empty($errors)) {
            throw new ExceptionValidationRegisters($errors);
        }
    }

    /**
     * Validates fields common to all user types
     *
     * @param array<string, string> $data The form data.
     * @return array<ExceptionValidationRegister> Array of validation errors.
     */
    protected function validateCommonFields(array $data): array
    {
        $errors = [];

        // Validate user type.
        if (!$this->isValidUserType($data['user_type'])) {
            $errors[] = new ExceptionValidationRegister(
                "user_type",
                "string",
                "Type d'utilisateur invalide."
            );
        }

        // Validate email format.
        if (!$this->isValidEmail($data['email'])) {
            $errors[] = new ExceptionValidationRegister(
                "email",
                "string",
                "Email invalide."
            );
        }

        // Validate password strength.
        if (!$this->isValidPassword($data['password'])) {
            $errors[] = new ExceptionValidationRegister(
                "password",
                "string",
                "Mot de passe trop court (min 8 caractères)."
            );
        }

        // Validate password match.
        if ($data['password'] !== ($data['passwordverif'] ?? '')) {
            $errors[] = new ExceptionValidationRegister(
                "passwordverif",
                "string",
                "Les mots de passe ne correspondent pas."
            );
        }

        // Validate phone number.
        if (!$this->isValidPhone($data['phone'])) {
            $errors[] = new ExceptionValidationRegister(
                "phone",
                "int",
                "Numéro de téléphone invalide."
            );
        }

        return $errors;
    }

    /**
     * Validates user-type-specific fields
     * Must be implemented by child classes
     *
     * @param array<string, string> $data The form data.
     * @return array<ExceptionValidationRegister> Array of validation errors
     */
    abstract protected function validateSpecificFields(array $data): array;
}
