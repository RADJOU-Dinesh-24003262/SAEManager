<?php

namespace App\Application\User;

use App\Domain\User\User;
use App\Domain\User\IRepository\IUserRepository;

/**
 * Use case for updating a user's profile information.
 *
 * @package App\Application\User
 */
class UpdateUserProfileUseCase
{
    private IUserRepository $userRepository;

    public function __construct(IUserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Updates a user's profile (currently phone number).
     *
     * @param string $email The user's email.
     * @param string $phone The new phone number.
     * @return User|null The updated user or null if not found.
     * @throws \PDOException If database operation fails.
     */
    public function execute(string $email, string $phone): ?User
    {
        $user = $this->userRepository->findByEmail($email);
        if ($user) {
            $user->setPhone($phone);
            $this->userRepository->update($user);
            return $user;
        }
        return null;
    }
}