<?php

namespace App\Application\SAE;

use App\Domain\SAE\IRepository\ISaeRepository;
use App\Domain\User\User;

/**
 * Use case for checking if a user can manage SAE operations.
 * 
 * Determines if a user is authorized to create/edit SAEs.
 *
 * @package App\Application\SAE
 */
class CanUserManageSaeUseCase
{
    private ISaeRepository $saeRepository;

    public function __construct(ISaeRepository $saeRepository)
    {
        $this->saeRepository = $saeRepository;
    }

    /**
     * Checks if a user can manage a SAE.
     *
     * @param User $user The user to check.
     * @param int|null $saeId The SAE ID (if null, checks if user can manage any SAE).
     * @return bool True if user can manage, false otherwise.
     */
    public function execute(User $user, ?int $saeId = null): bool
    {
        if (!$user->isProfessor()) {
            return false;
        }

        if ($saeId === null) {
            return true; // Any professor can create a SAE
        }

        return $this->saeRepository->isResponsibleProfessor($saeId, $user->getUserId());
    }
}