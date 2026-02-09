<?php

namespace App\Application\User;

use App\Domain\User\IRepository\IUserRepository;

/**
 * Use case for retrieving all client users.
 *
 * @package App\Application\User
 */
class GetClientsUseCase
{
    private IUserRepository $userRepository;

    public function __construct(IUserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Gets all client users.
     *
     * @return array Array of Client users.
     * @throws \PDOException If database operation fails.
     */
    public function execute(): array
    {
        return $this->userRepository->getAllClients();
    }
}