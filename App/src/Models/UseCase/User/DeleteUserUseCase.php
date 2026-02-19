<?php

namespace Models\UseCase\User;

use Exception;
use Models\UseCase\User\InterfaceDB\UserInterface;
use Models\Entity\User\User;

/**
 * Use Case for deleting a user.
 *
 * Handles logic for deleting a user account.
 *
 * @category   Models
 * @package    Src
 * @subpackage Models/UseCase/User
 * @author     Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license    MIT License https://opensource.org/licenses/MIT
 * @link       https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class DeleteUserUseCase
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
     * Deletes a user by ID.
     *
     * @param integer $userId The user ID.
     *
     * @return void
     *
     * @throws Exception If deletion fails.
     */
    public function execute(int $userId): void
    {
        if (!$this->userInterface->delete($userId)) {
            throw new Exception("Erreur lors de la suppression de l'utilisateur.");
        }
    }

    /**
     * Deletes a user by Email.
     *
     * @param string $email The user email.
     *
     * @return void
     *
     * @throws Exception If deletion fails or user not found.
     */
    public function executeByEmail(string $email): void
    {
        $user = $this->userInterface->findByEmail($email);

        if (!$user) {
            throw new Exception("Utilisateur non trouvé.");
        }

        if (!$this->userInterface->delete($user->getUserId())) {
            throw new Exception("Erreur lors de la suppression de l'utilisateur.");
        }

        // Clear session.
        session_unset();     // Unset all session variables.
        session_destroy();   // Destroy the session.
    }
}
