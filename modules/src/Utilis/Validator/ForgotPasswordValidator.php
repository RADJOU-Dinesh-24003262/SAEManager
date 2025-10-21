<?php

namespace Utilis\Validator;

use includes\exception\ExceptionValidationForgotPassword;
use includes\exception\ExceptionSpam;

/**
 * Class ForgotPasswordValidator
 *
 * @package     src

 * @subpackage  Utilis\Validator

 * @author      Benhafessa Alexandre, Dargentolle Francois, Edelstein William, Griguer Nathan, Radjou Dinesh
 *
 * Validates input data for the forgot password form.
 *
 * This validator ensures:
 * - The "email" field is present and valid.
 * - Password reset requests are not sent too frequently (spam protection).
 */
class ForgotPasswordValidator extends FormValidator
{
    /**
     * Fields that are required for validation.
     *
     * @var array<string>
     */
    protected $required = ['email'];

    /**
     * Validates the provided data for a forgot password request.
     *
     * Checks:
     * - Whether the email is syntactically valid.
     * - Whether the user is allowed to make another reset request (2-minute cooldown).
     *
     * @param array $data The form data to validate.
     *
     * @throws ExceptionValidationForgotPassword If the email field is invalid.
     * @throws ExceptionSpam If a reset request was made less than 2 minutes ago.
     *
     * @return void
     */
    public function validate(array $data): void
    {
        $errors = [];
        if (!$this->isValidEmail($data['email'])) {
            throw new ExceptionValidationForgotPassword('email', 'string', "L'adresse email n'est pas valide.");
        }if (($_SESSION['last_forgot_password_request'] ?? 0) > (time() - 120)) {
            throw new ExceptionSpam("Veuillez attendre au moins 2 minutes avant de refaire une demande.");
        }
    }
}
