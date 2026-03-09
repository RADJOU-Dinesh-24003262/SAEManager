<?php

namespace Models\UseCase\User;

use Core\Includes\Exception\ExceptionPasswordUpdateFailed;
use Models\Entity\User\User;
use Models\UseCase\User\InterfaceDB\UserInterface;

/**
 * Use Case for updating user password (authenticated user).
 *
 * Handles logic for changing password including old password verification.
 *
 * @category   Models
 * @package    Src
 * @subpackage Models/UseCase/User
 * @author     Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license    MIT License https://opensource.org/licenses/MIT
 * @link       https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class UpdatePasswordUseCase
{
    /**
     * The User repository interface.
     *
     * @var UserInterface
     */
    private UserInterface $userInterface;

    /**
     * Constructor.
     *
     * @param UserInterface $userInterface The User repository.
     */
    public function __construct(UserInterface $userInterface)
    {
        $this->userInterface = $userInterface;
    }

    /**
     * Updates the user's password.
     *
     * @param User   $user        The user entity.
     * @param string $oldPassword The current password.
     * @param string $newPassword The new password.
     *
     * @return void
     *
     * @throws ExceptionPasswordUpdateFailed If password update fails or old password incorrect.
     */
    public function execute(User $user, string $oldPassword, string $newPassword): void
    {
        $user = $this->userInterface->findById($user->getUserId());

        if (!$user) {
            throw new ExceptionPasswordUpdateFailed("User not found.");
        }

        if (!$user->verifyPassword($oldPassword)) {
            throw new ExceptionPasswordUpdateFailed("L'ancien mot de passe est incorrect.");
        }

        $user->setPassword($newPassword);

        if (!$this->userInterface->updatePassword($user->getUserId(), $user->getPasswordHash())) {
            throw new ExceptionPasswordUpdateFailed("Erreur technique lors de la mise à jour du mot de passe.");
        }
    }
}
