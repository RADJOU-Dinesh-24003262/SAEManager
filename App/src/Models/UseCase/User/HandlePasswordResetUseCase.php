<?php

namespace Models\UseCase\User;

use Core\includes\exception\ExceptionPasswordUpdateFailed;
use Core\includes\exception\ExceptionToken\ExceptionInvalidToken;
use Core\includes\exception\ExceptionValidation\ExceptionValidationResetPassword;
use Models\Repository\User\PdoUserRepository;
use Models\UseCase\User\ResetPasswordUseCase;
use Services\TokenService;

class HandlePasswordResetUseCase
{
    private PdoUserRepository $userRepository;

    public function __construct(PdoUserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Execute the password reset process.
     *
     * @param string $token    The reset token.
     * @param string $password The new password.
     * @return string The email associated with the token.
     * @throws ExceptionInvalidToken
     * @throws ExceptionValidationResetPassword
     * @throws ExceptionPasswordUpdateFailed
     */
    public function execute(string $token, string $password): string
    {
        // Validate token
        $tokenData = TokenService::validateToken($token);
        $email = $tokenData['email'];

        // Reset password using existing UseCase (reusing existing logic adhering to DRY)
        $resetPasswordUseCase = new ResetPasswordUseCase($this->userRepository);
        $resetPasswordUseCase->execute($email, $password);

        // Mark token as used
        TokenService::markTokenAsUsed($token);

        return $email;
    }
}