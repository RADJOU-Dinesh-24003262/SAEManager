<?php

namespace App\Application\SAE;

use App\Domain\SAE\IRepository\ISaeRepository;
use App\Domain\User\User;

/**
 * Use case for checking if a user has access to a SAE.
 * 
 * Checks based on role: professors (assigned), students (enrolled), clients (commissioned).
 *
 * @package App\Application\SAE
 */
class CanUserAccessSaeUseCase
{
    private ISaeRepository $saeRepository;

    public function __construct(ISaeRepository $saeRepository)
    {
        $this->saeRepository = $saeRepository;
    }

    /**
     * Checks if a user can access a SAE.
     *
     * @param User $user The user to check.
     * @param int $saeId The SAE ID.
     * @return bool True if user has access, false otherwise.
     */
    public function execute(User $user, int $saeId): bool
    {
        $saes = [];
        if ($user->isProfessor()) {
            $saes = $this->saeRepository->findByProfessorId($user->getUserId());
        }
        elseif ($user->isStudent()) {
            $saes = $this->saeRepository->findByStudentId($user->getUserId());
        }
        elseif ($user->isClient()) {
            $saes = $this->saeRepository->findByClientId($user->getUserId());
        }

        foreach ($saes as $sae) {
            if ($sae->getId() === $saeId) {
                return true;
            }
        }
        return false;
    }
}