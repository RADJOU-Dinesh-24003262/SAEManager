<?php

namespace Validator\Login;

use Core\Includes\Exception\ExceptionValidation\ExceptionValidationLogin;
use Core\Utils\RateLimiter;
use Override;
use Validator\FormValidator;

/**
 * Class LoginValidator
 * This class regroup function to validate the login process of a user.

 * @category Validator

 * @package    Src
 * @subpackage Validator

 * @author Alexandre Benhafessa <alexandre.benhafessa@etu.univ-amu.fr>
 * @author François Dargentolle <francois.dargentolle@etu.univ-amu.fr>
 * @author William Edelstein <william.edelstein@etu.univ-amu.fr>
 * @author Nathan Griguer <nathan.griguer@etu.univ-amu.fr>
 * @author Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>

 * @license MIT License https://opensource.org/licenses/MIT

 * @link https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class LoginValidator extends FormValidator
{
    /**
     * The list of the variables required for the loggin of a user.
     *
     * @var array<string>
     */
    protected $required = ['email', 'password'];

    /**
     * This this method validated the values given in $data to log a user with.
     *
     * @param array<string, mixed> $data Array, in adequation to the required value fields.
     *
     * @return void
     *
     * @throws ExceptionValidationLogin All the errors that might have been found.
     */
    #[Override]
    public function validate(array $data): void
    {
        [$captchaSuccess, $captchaErrors] = $this->verifyCaptchaToken(
            $data['h-captcha-response'] ?? '',
            $_SERVER['REMOTE_ADDR'] ?? ''
        );

        if (!$captchaSuccess) {
            throw new ExceptionValidationLogin("La validation hCaptcha a échoué.");
        }

        if (!$this->isValidEmail($data['email'])) {
            throw new ExceptionValidationLogin("L'adresse email n'est pas valide.");
        }

        // Anti-Bruteforce: 5 attempts max per 5 minutes (300 seconds).
        if (!RateLimiter::check('login', 5, 300)) {
            throw new ExceptionValidationLogin(
                "Trop de tentatives de connexion échouées. Veuillez réessayer dans 5 minutes."
            );
        }
    }
}
