<?php

namespace App\Application\Validation\User;

use App\Application\Validation\AbstractValidator;
use App\Application\Validation\Exception\ForgotPasswordValidationException;
use App\Application\Validation\Rules\EmailRule;
use App\Domain\User\Exception\SpamException;
use App\Infrastructure\Service\SessionService;
use Override;

/**
 * Validator for forgot password requests.
 * Validates email and protects against spam.
 *
 * @category Validation
 * @package  App\Application\Validation\User
 */
class ForgotPasswordValidator extends AbstractValidator
{
    protected array $required = ['email'];

    #[Override]
    public function validate(array $data): void
    {
        $this->checkRequired($data);
        
        // Validate email format
        $emailRule = new EmailRule();
        if (!$emailRule->validate($data['email'])) {
            throw new ForgotPasswordValidationException('email', 'string', $emailRule->getMessage());
        }

        // Spam protection: 2-minute cooldown
        if (SessionService::get('last_forgot_password_request', 0) > (time() - 120)) {
            throw new SpamException("Veuillez attendre au moins 2 minutes avant de refaire une demande.");
        }
    }
}
