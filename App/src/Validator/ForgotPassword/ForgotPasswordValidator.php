<?php

namespace Validator\ForgotPassword;

use Core\Includes\Exception\ExceptionValidation\ExceptionValidationForgotPassword;
use Core\Includes\Exception\ExceptionSpam;
use Core\Utils\RateLimiter;
use Validator\FormValidator;
use Core\Utils\SessionService;
use Override;

/**
 * Class ForgotPasswordValidator
 * Validates input data for the forgot password form.
 * This validator ensures:
 * - The "email" field is present and valid.
 * - Password reset requests are not sent too frequently (spam protection).

 * @category Validator

 * @package Src

 * @author Alexandre Benhafessa <alexandre.benhafessa@etu.univ-amu.fr>
 * @author François Dargentolle <francois.dargentolle@etu.univ-amu.fr>
 * @author William Edelstein <william.edelstein@etu.univ-amu.fr>
 * @author Nathan Griguer <nathan.griguer@etu.univ-amu.fr>
 * @author Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>

 * @license MIT License https://opensource.org/licenses/MIT

 * @link https://github.com/RADJOU-Dinesh-24003262/SAEManager
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
     * @param array<string, mixed> $data The form data to validate.
     *
     * @throws ExceptionValidationForgotPassword If the email field is invalid.
     * @throws ExceptionSpam If a reset request was made less than 2 minutes ago.
     *
     * @return void
     */
    #[Override]
    public function validate(array $data): void
    {
        if (!$this->isValidEmail($data['email'])) {
            throw new ExceptionValidationForgotPassword('email', 'string', "L'adresse email n'est pas valide.");
        }
        if (!RateLimiter::check('forgot_password', 2, 120)) {
            throw new ExceptionSpam("Veuillez attendre au moins 2 minutes avant de refaire une demande.");
        }
    }
}
