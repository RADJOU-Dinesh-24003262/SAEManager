<?php

namespace Models\UseCase\User;

use Core\Includes\Exception\ExceptionPasswordUpdateFailed;
use Models\UseCase\User\InterfaceDB\PasswordResetInterface;
use Models\UseCase\User\InterfaceDB\UserInterface;
use Models\Entity\User\User;

/**
 * Use Case for resetting user password (forgot password flow).
 *
 * Handles logic for resetting password via email/token flow.
 *
 * @category   Models
 * @package    Src
 * @subpackage Models/UseCase/User
 * @author     Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license    MIT License https://opensource.org/licenses/MIT
 * @link       https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class ResetPasswordUseCase
{
    /**
     * The User repository interface.
     *
     * @var UserInterface
     */
    private UserInterface $userInterface;

    private PasswordResetInterface $passwordResetInterface;

    /**
     * Constructor.
     *
     * @param UserInterface $userInterface The User repository.
     */
    public function __construct(UserInterface $userInterface, PasswordResetInterface $passwordResetInterface)
    {
        $this->userInterface = $userInterface;
        $this->passwordResetInterface = $passwordResetInterface;
    }

    /**
     * Resets the user's password using email.
     *
     * @param string $email       The user's email.
     * @param string $newPassword The new password.
     *
     * @return void
     *
     * @throws ExceptionPasswordUpdateFailed If user not found or update fails.
     */
    public function execute(string $newPassword, string $token): string
    {
        // Validate token.
        $tokenData = $this->passwordResetInterface->validateToken($token);
        $email = $tokenData['email'];

        $user = $this->userInterface->findByEmail($email);


        if (!$user) {
            throw new ExceptionPasswordUpdateFailed("Utilisateur non trouvé.");
        }

        $user->setPassword($newPassword);

        if (!$this->userInterface->updatePassword($user->getUserId(), $user->getPasswordHash())) {
            throw new ExceptionPasswordUpdateFailed("Erreur technique lors de la réinitialisation du mot de passe.");
        }

        // Mark token as used.
        $this->passwordResetInterface->markTokenAsUsed($token);

        return $email;
    }
}
