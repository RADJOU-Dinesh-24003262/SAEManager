<?php

namespace Models\UseCase\User;

use Core\Includes\Exception\ExceptionPasswordUpdateFailed;
use Core\Includes\Exception\ExceptionToken\ExceptionInvalidToken;
use Core\Includes\Exception\ExceptionValidation\ExceptionValidationResetPassword;
use Models\UseCase\User\InterfaceDB\PasswordResetInterface;
use Models\UseCase\User\InterfaceDB\UserInterface;
use Models\UseCase\User\ResetPasswordUseCase;

/**
 * Use case to handle password reset.
 *
 * @category   Models
 * @package    Src
 * @subpackage Models/UseCase/User
 * @author     Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license    MIT License https://opensource.org/licenses/MIT
 * @link       https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class HandlePasswordResetUseCase
{
    /**
     * The user repository.
     *
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
     * Execute the password reset process.
     *
     * @param string $token    The reset token.
     * @param string $password The new password.
     * @return string The email associated with the token.
     * @throws ExceptionInvalidToken If token is invalid.
     * @throws ExceptionValidationResetPassword If validation fails.
     * @throws ExceptionPasswordUpdateFailed If password update fails.
     */
    public function execute(string $token, string $password): string
    {

        // Reset password using existing UseCase (reusing existing logic adhering to DRY).
        $resetPasswordUseCase = new ResetPasswordUseCase($this->userRepository, $this->passwordResetInterface);
        $email=$resetPasswordUseCase->execute( $password, $token);

        return $email;
    }
}
