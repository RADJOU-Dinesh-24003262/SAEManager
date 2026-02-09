<?php

namespace App\Application\User;

use App\Domain\User\User;
use App\Domain\User\IRepository\IUserRepository;

/**
 * Use case for registering a new user.
 * 
 * Creates a User entity from registration data and persists it.
 *
 * @package App\Application\User
 */
class RegisterUserUseCase
{
    private IUserRepository $userRepository;

    public function __construct(IUserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Registers a new user.
     *
     * @param array $data User registration data (user_type, first_name, last_name, email, password, etc.).
     * @return User The registered user entity.
     * @throws \PDOException If database operation fails.
     * @throws \App\Domain\User\Exception\EmailAlreadyExistsException If email already exists.
     */
    public function execute(array $data): User
    {
        $user = User::createFromRegistrationData($data);
        $this->userRepository->save($user);
        return $user;
    }
}