<?php

namespace Models\UseCase\User;

use Core\Includes\Exception\ExceptionPasswordUpdateFailed;
use Core\Includes\Exception\ExceptionToken\ExceptionInvalidToken;
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

    /**
     * @var PasswordResetInterface
     */
    private PasswordResetInterface $passwordResetInterface;

    /**
     * The Validate Token use case.
     *
     * @var ValidateTokenUseCase
     */
    private ValidateTokenUseCase $validateTokenUseCase;

    /**
     * Constructor.
     *
     * @param UserInterface          $userInterface          The User repository.
     * @param PasswordResetInterface $passwordResetInterface The password reset repository.
     * @param ValidateTokenUseCase   $validateTokenUseCase   The Validate token use case.
     */
    public function __construct(
        UserInterface $userInterface,
        PasswordResetInterface $passwordResetInterface,
        ValidateTokenUseCase $validateTokenUseCase
    ) {
        $this->userInterface = $userInterface;
        $this->passwordResetInterface = $passwordResetInterface;
        $this->validateTokenUseCase = $validateTokenUseCase;
    }

    /**
     * Resets the user's password using email.
     *
     * @param string $newPassword The new password.
     * @param string $token       The reset token.
     *
     * @return string The user email.
     *
     * @throws ExceptionPasswordUpdateFailed If password update fails.
     */
    public function execute(string $newPassword, string $token): string
    {
        /* @var array<string, mixed> $tokenData */
        $tokenData = $this->validateTokenUseCase->execute($token);

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
        $this->passwordResetInterface->markAsUsed($token);

        return $email;
    }
}
