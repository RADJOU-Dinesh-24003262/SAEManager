<?php

namespace App\Application\User;

use App\Domain\User\IRepository\IUserRepository;

/**
 * Use case for checking if a user exists by email.
 *
 * @package App\Application\User
 */
class CheckUserExistsUseCase
{
    private IUserRepository $userRepository;

    public function __construct(IUserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Checks if a user exists with the given email.
     *
     * @param string $email The email to check.
     * @return bool True if exists, false otherwise.
     */
    public function execute(string $email): bool
    {
        return $this->userRepository->existsByEmail($email);
    }
}