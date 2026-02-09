<?php

namespace App\Application\SAE;

use App\Domain\SAE\IRepository\ISaeGroupRepository;
use App\Domain\SAE\IRepository\ISaeRepository;
use App\Domain\User\User;
use App\Domain\SAE\Exception\AccessDeniedException;
use App\Domain\SAE\Exception\ResourceNotFoundException;

/**
 * Use case for assigning a professor to manage a SAE group.
 * 
 * Only the responsible professor can assign other professors to groups.
 *
 * @package App\Application\SAE
 */
class AssignProfessorToGroupUseCase
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
     * Assigns a professor to manage a group.
     *
     * @param User $responsibleProf The responsible professor.
     * @param int $groupId The group ID.
     * @param int|null $professorId The professor ID to assign (or null to unassign).
     * @return bool True on success, false on failure.
     * @throws AccessDeniedException If user is not the responsible professor.
     * @throws ResourceNotFoundException If group not found.
     * @throws \PDOException If database operation fails.
     */
    public function execute(User $responsibleProf, int $groupId, ?int $professorId): bool
    {
        $group = $this->groupRepository->findById($groupId);
        if (!$group) {
            throw new ResourceNotFoundException("Groupe non trouvé");
        }

        if (
        $responsibleProf->isProfessor()
        && $this->saeRepository->isResponsibleProfessor($group->getSaeId(), $responsibleProf->getUserId())
        ) {
            $group->setProfessorId($professorId);
            return $this->groupRepository->update($group);
        }
        else {
            throw new AccessDeniedException("Seul le responsable peut assigner des professeurs aux groupes");
        }
    }
}