<?php

namespace App\Application\User;

use App\Domain\User\IRepository\IUserRepository;

/**
 * Use case for deleting a user by email.
 *
 * @package App\Application\User
 */
class DeleteUserUseCase
{
    private IUserRepository $userRepository;

    public function __construct(IUserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Deletes a user by their email address.
     *
     * @param string $email The email of the user to delete.
     * @return void
     * @throws \PDOException If database operation fails.
     */
    public function execute(string $email): void
    {
        $this->userRepository->deleteByEmail($email);
    }
}