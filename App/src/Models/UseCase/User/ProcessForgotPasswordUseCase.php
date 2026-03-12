<?php

namespace Models\UseCase\User;

use Core\Includes\Exception\ExceptionEmailAlreadyExists;
use Core\Includes\Exception\ExceptionSpam;
use Core\Includes\Exception\ExceptionToken\ExceptionCreationTokenFailed;
use Models\UseCase\User\InterfaceDB\PasswordResetInterface;
use Models\UseCase\User\InterfaceDB\UserInterface;
use Services\Auth\PasswordResetMailer;
use Services\TokenService;

/**
 * Use Case for processing forgot password requests.
 *
 * @category   UseCase
 * @package    Models\UseCase\User
 * @author     Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license    MIT License https://opensource.org/licenses/MIT
 * @link       https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class ProcessForgotPasswordUseCase
{
    /**
     * @var UserInterface
     */
    private UserInterface $userRepository;

    /**
     * @var PasswordResetInterface
     */
    private PasswordResetInterface $passwordResetInterface;

    /**
     * @var TokenService
     */
    private TokenService $tokenService;

    /**
     * Constructor.
     *
     * @param UserInterface          $userRepository         Repo for users.
     * @param PasswordResetInterface $passwordResetInterface Repo for resets.
     * @param TokenService           $tokenService           Service for tokens.
     */
    public function __construct(
        UserInterface $userRepository,
        PasswordResetInterface $passwordResetInterface,
        TokenService $tokenService
        )
    {
        $this->userRepository = $userRepository;
        $this->passwordResetInterface = $passwordResetInterface;
        $this->tokenService = $tokenService;
    }

    /**
     * Execute the process.
     *
     * @param string $email The email address.
     *
     * @return void
     *
     * @throws ExceptionCreationTokenFailed If token generation fails.
     */
    public function execute(string $email): void
    {
        if ($this->userRepository->existsByEmail($email)) {
            $this->passwordResetInterface->purgeExpired();

            $token = $this->tokenService->generate();
            $expiresAt = new \DateTimeImmutable('+10 minutes');

            $success = $this->passwordResetInterface->insert($email, $token, $expiresAt);

            if (!$success) {
                throw new ExceptionCreationTokenFailed();
            }

            // Send the email.
            PasswordResetMailer::send($email, $token);
        }
    }
}