<?php

namespace App\Application\SAE;

use App\Domain\SAE\SaeGroup;
use App\Domain\SAE\IRepository\ISaeGroupRepository;
use App\Domain\SAE\IRepository\ISaeRepository;
use App\Domain\User\User;
use App\Domain\SAE\Exception\AccessDeniedException;

/**
 * Use case for creating a new SAE group.
 * 
 * Only the responsible professor can create groups.
 *
 * @package App\Application\SAE
 */
class CreateSaeGroupUseCase
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
     * Creates a new SAE group.
     *
     * @param User $user The user creating the group (must be responsible professor).
     * @param int $saeId The SAE ID.
     * @param int|null $professorId Optional professor assigned to manage this group.
     * @return SaeGroup The created group.
     * @throws AccessDeniedException If user is not the responsible professor.
     * @throws \PDOException If database operation fails.
     */
    public function execute(User $user, int $saeId, ?int $professorId): SaeGroup
    {
        if (!$user->isProfessor() || !$this->saeRepository->isResponsibleProfessor($saeId, $user->getUserId())) {
            throw new AccessDeniedException("Vous n'avez pas la permission de créer un groupe");
        }

        $group = new SaeGroup($saeId, $professorId);
        $this->groupRepository->save($group);
        return $group;
    }
}