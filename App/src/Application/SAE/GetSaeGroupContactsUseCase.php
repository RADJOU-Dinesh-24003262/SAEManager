<?php

namespace App\Application\SAE;

use App\Domain\SAE\IRepository\ISaeGroupRepository;
use App\Domain\SAE\IRepository\ISaeRepository;
use App\Domain\User\User;

/**
 * Use case for retrieving SAE group contacts.
 * 
 * Returns group members accessible to the user based on their role.
 *
 * @package App\Application\SAE
 */
class GetSaeGroupContactsUseCase
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
     * Gets SAE group contacts grouped by group ID.
     *
     * @param User $user The user requesting contacts.
     * @param int $saeId The SAE ID.
     * @return array Associative array [groupId => [members]].
     */
    public function execute(User $user, int $saeId): array
    {
        $members = $this->getAccessibleGroupMembers($user, $saeId);

        $grouped = [];

        foreach ($members as $member) {
            $groupId = $member['sae_group_id'];
            $grouped[$groupId][] = $member;
        }

        return $grouped;
    }

    /**
     * Gets group members accessible based on user role.
     * 
     * - Responsible professor: all members
     * - Assigned professor: only managed group members
     * - Student: only their group members
     * - Client: all members
     *
     * @param User $user The user.
     * @param int $saeId The SAE ID.
     * @return array Array of member data.
     */
    private function getAccessibleGroupMembers(User $user, int $saeId): array
    {
        if ($user->isProfessor()) {
            if ($this->saeRepository->isResponsibleProfessor($saeId, $user->getUserId())) {
                return $this->groupRepository->getAllMembers($saeId);
            }
            return $this->groupRepository->getAssignedGroupMembers($saeId, $user->getUserId());
        }

        if ($user->isStudent()) {
            return $this->groupRepository->getStudentGroupMembers($saeId, $user->getUserId());
        }

        if ($user->isClient()) {
            return $this->groupRepository->getClientSaeMembers($saeId, $user->getUserId());
        }

        return [];
    }
}