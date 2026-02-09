<?php

namespace App\Application\User;

use App\Domain\User\IRepository\IUserRepository;
use App\Infrastructure\Exception\PasswordUpdateException;

/**
 * Use case for updating a user's password.
 *
 * @package App\Application\User
 */
class UpdatePasswordUseCase
{
    private IUserRepository $userRepository;

    public function __construct(IUserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Updates a user's password.
     *
     * @param string $email The user's email.
     * @param string $newPassword The new plain-text password (will be hashed).
     * @return void
     * @throws PasswordUpdateException If user not found.
     * @throws \PDOException If database operation fails.
     */
    public function execute(string $email, string $newPassword): void
    {
        $user = $this->userRepository->findByEmail($email);
        if (!$user) {
            throw new PasswordUpdateException('Aucun utilisateur trouvé avec cet email.');
        }

        $user->setPassword($newPassword);
        $this->userRepository->updatePassword($user);
    }
}