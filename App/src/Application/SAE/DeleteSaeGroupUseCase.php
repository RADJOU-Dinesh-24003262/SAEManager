<?php

namespace App\Application\SAE;

use App\Domain\SAE\IRepository\ISaeGroupRepository;
use App\Domain\SAE\IRepository\ISaeRepository;
use App\Domain\User\User;
use App\Domain\SAE\Exception\AccessDeniedException;
use App\Domain\SAE\Exception\ResourceNotFoundException;

/**
 * Use case for deleting a SAE group.
 * 
 * Only the responsible professor can delete groups.
 *
 * @package App\Application\SAE
 */
class DeleteSaeGroupUseCase
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
     * Deletes a SAE group.
     *
     * @param User $professor The professor (must be responsible professor).
     * @param int $groupId The group ID to delete.
     * @return bool True on success, false on failure.
     * @throws AccessDeniedException If user is not the responsible professor.
     * @throws ResourceNotFoundException If group not found.
     * @throws \PDOException If database operation fails.
     */
    public function execute(User $professor, int $groupId): bool
    {
        $group = $this->groupRepository->findById($groupId);
        if (!$group) {
            throw new ResourceNotFoundException("Groupe non trouvé");
        }

        if (
        !$professor->isProfessor()
        || !$this->saeRepository->isResponsibleProfessor($group->getSaeId(), $professor->getUserId())
        ) {
            throw new AccessDeniedException("Vous n'avez pas la permission de supprimer des groupes");
        }

        return $this->groupRepository->delete($groupId);
    }
}