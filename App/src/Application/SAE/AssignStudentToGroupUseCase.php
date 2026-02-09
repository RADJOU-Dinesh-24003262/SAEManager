<?php

namespace App\Application\SAE;

use App\Domain\SAE\IRepository\ISaeGroupRepository;
use App\Domain\SAE\IRepository\ISaeRepository;
use App\Domain\User\User;
use App\Domain\SAE\Exception\AccessDeniedException;
use App\Domain\SAE\Exception\ResourceNotFoundException;

/**
 * Use case for assigning a student to a SAE group.
 * 
 * Only the responsible professor can assign students.
 *
 * @package App\Application\SAE
 */
class AssignStudentToGroupUseCase
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
     * Assigns a student to a group.
     *
     * @param User $professor The professor (must be responsible professor).
     * @param int $studentId The student ID to assign.
     * @param int $groupId The group ID.
     * @return bool True on success, false on failure.
     * @throws AccessDeniedException If user is not the responsible professor.
     * @throws ResourceNotFoundException If group not found.
     * @throws \PDOException If database operation fails.
     */
    public function execute(User $professor, int $studentId, int $groupId): bool
    {
        $group = $this->groupRepository->findById($groupId);
        if (!$group) {
            throw new ResourceNotFoundException("Groupe non trouvé");
        }

        $profId = $professor->getUserId();

        if (!$professor->isProfessor() || !$this->saeRepository->isResponsibleProfessor($group->getSaeId(), $profId)) {
            throw new AccessDeniedException("Vous n'avez pas la permission d'assigner des étudiants");
        }

        return $this->groupRepository->assignStudent($studentId, $groupId);
    }
}