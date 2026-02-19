<?php

namespace Models\UseCase\User;

use Core\includes\exception\ExceptionEmailAlreadyExists;
use Core\includes\exception\ExceptionSpam;
use Core\includes\exception\ExceptionToken\ExceptionCreationTokenFailed;
use Models\Repository\User\PdoUserRepository;
use Services\Auth\PasswordResetMailer;
use Services\TokenService;

/**
 * Use Case for processing forgot password requests.
 *
 * @category UseCase
 * @package  Models\UseCase\User
 */
class ProcessForgotPasswordUseCase
{
    /**
     * @var PdoUserRepository
     */
    private PdoUserRepository $userRepository;

    public function __construct(PdoUserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Execute the process.
     *
     * @param string $email The email address.
     * @return void
     * @throws ExceptionCreationTokenFailed
     * @throws ExceptionEmailAlreadyExists
     * @throws ExceptionSpam
     */
    public function execute(string $email): void
    {
        if ($this->userRepository->existsByEmail($email)) {
            // Create the password reset token.
            $token = TokenService::createPasswordResetToken($email);

            // Send the email.
            PasswordResetMailer::send($email, $token);
        }
    }
}