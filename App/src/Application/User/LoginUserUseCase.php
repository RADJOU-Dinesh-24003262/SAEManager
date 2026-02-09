<?php

namespace App\Application\User;

use App\Domain\User\IRepository\IUserRepository;
use App\Application\Validation\Exception\LoginValidationException;
use App\Domain\User\User;

/**
 * Use case for authenticating a user.
 * 
 * Verifies email and password combination.
 *
 * @package App\Application\User
 */
class LoginUserUseCase
{
    private IUserRepository $userRepository;

    public function __construct(IUserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Authenticates a user.
     *
     * @param string $email
     * @param string $password
     * @return User $user
     * @throws LoginValidationException
     */
    public function execute(string $email, string $password): User
    {
        $user = $this->userRepository->findByEmail($email);

        if (!$user || !$user->verifyPassword($password)) {
            throw new LoginValidationException();
        }

        return $user;
    }
}