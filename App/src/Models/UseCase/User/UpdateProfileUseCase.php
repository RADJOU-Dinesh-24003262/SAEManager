<?php

namespace Models\UseCase\User;

use Models\Entity\User\User;
use Models\UseCase\User\InterfaceDB\UserInterface;

/**
 * Use Case for updating user profile.
 *
 * Handles logic for updating user information (e.g., phone).
 *
 * @category   Models
 * @package    Src
 * @subpackage Models/UseCase/User
 * @author     Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license    MIT License https://opensource.org/licenses/MIT
 * @link       https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class UpdateProfileUseCase
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
     * Updates the user's phone number.
     *
     * @param integer              $userId The user ID.
     * @param array<string, mixed> $data   The data modified.
     *
     * @return User The updated user entity.
     *
     * @throws \Exception If user not found or update fails.
     */
    public function execute(int $userId, array $data): User
    {
        $user = $this->userInterface->findById($userId);

        if (!$user) {
            throw new \Exception("User not found with ID: $userId");
        }

        $user->setPhone($data['phone']);

        $this->userInterface->update($user);

        return $user;
    }
}
