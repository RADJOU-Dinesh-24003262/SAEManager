<?php

namespace App\Application\User;

use App\Domain\User\IRepository\IUserRepository;

/**
 * Use case for retrieving all professor users.
 *
 * @package App\Application\User
 */
class GetProfessorsUseCase
{
    private IUserRepository $userRepository;

    public function __construct(IUserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Gets all professor users.
     *
     * @return array Array of Professor users.
     * @throws \PDOException If database operation fails.
     */
    public function execute(): array
    {
        return $this->userRepository->getAllProfessors();
    }
}