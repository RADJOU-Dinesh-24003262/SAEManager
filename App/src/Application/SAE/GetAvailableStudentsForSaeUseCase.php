<?php

namespace App\Application\SAE;

use App\Domain\SAE\IRepository\ISaeGroupRepository;
use App\Domain\SAE\IRepository\ISaeRepository;
use App\Domain\User\User;
use App\Domain\SAE\Exception\AccessDeniedException;

/**
 * Use case for retrieving available students for a SAE.
 * 
 * Returns students who are not yet assigned to any group in the SAE.
 *
 * @package App\Application\SAE
 */
class GetAvailableStudentsForSaeUseCase
{
    private ISaeGroupRepository $groupRepository;
    private ISaeRepository $saeRepository;

    public function __construct(
        ISaeGroupRepository $groupRepository,
        ISaeRepository $saeRepository
        )
    {
        $this->groupRepository = $groupRepository;
        $this->saeRepository = $saeRepository;
    }

    /**
     * Gets students available for assignment in a SAE.
     *
     * @param User $user The user (must be responsible professor).
     * @param int $saeId The SAE ID.
     * @return array Array of available student data.
     * @throws AccessDeniedException If user is not the responsible professor.
     * @throws \PDOException If database operation fails.
     */
    public function execute(User $user, int $saeId): array
    {
        if (!$user->isProfessor() || !$this->saeRepository->isResponsibleProfessor($saeId, $user->getUserId())) {
            throw new AccessDeniedException("Vous n'avez pas la permission de voir les étudiants disponibles");
        }

        return $this->groupRepository->getAvailableStudents($saeId);
    }
}