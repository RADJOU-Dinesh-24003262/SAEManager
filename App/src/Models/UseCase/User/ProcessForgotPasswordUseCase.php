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
 * @category   UseCase
 * @package    Models\UseCase\User
 * @author     Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license    MIT License https://opensource.org/licenses/MIT
 * @link       https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class ProcessForgotPasswordUseCase
{
    /**
     * @var PdoUserRepository
     */
    private PdoUserRepository $userRepository;

    /**
     * Constructor.
     *
     * @param PdoUserRepository $userRepository Repo for users.
     */
    public function __construct(PdoUserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Execute the process.
     *
     * @param string $email The email address.
     * @return void
     * @throws ExceptionCreationTokenFailed If token generation fails.
     * @throws ExceptionEmailAlreadyExists If email exists.
     * @throws ExceptionSpam If spam detected.
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
