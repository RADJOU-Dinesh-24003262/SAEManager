<?php

namespace Models\UseCase\User;

use Core\Includes\Exception\ExceptionEmailAlreadyExists;
use Core\Includes\Exception\ExceptionSpam;
use Core\Includes\Exception\ExceptionToken\ExceptionCreationTokenFailed;
use Models\Repository\User\PdoPasswordResetRepository;
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

    private PasswordResetInterface $passwordResetInterface;

    /**
     * Constructor.
     *
     * @param UserInterface $userRepository Repo for users.
     */
    public function __construct(UserInterface $userRepository, PasswordResetInterface $passwordResetInterface)
    {
        $this->userRepository = $userRepository;
        $this->passwordResetInterface = $passwordResetInterface;
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
            $createTokenUseCase = new CreateTokenResetUseCase($this->passwordResetInterface);
            $token = $createTokenUseCase->execute($email);

            // Send the email.
            PasswordResetMailer::send($email, $token);
        }
    }
}
