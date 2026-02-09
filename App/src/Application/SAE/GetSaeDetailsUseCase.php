<?php

namespace App\Application\SAE;

use App\Domain\SAE\IRepository\ISaeRepository;
use App\Domain\SAE\IRepository\ISaeGroupRepository;
use App\Domain\User\User;

/**
 * Use case for retrieving SAE details with role-based access control.
 * 
 * Returns SAE subject, groups (filtered by user role), professors, and client info.
 *
 * @package App\Application\SAE
 */
class GetSaeDetailsUseCase
{
    private ISaeRepository $saeRepository;
    private ISaeGroupRepository $groupRepository;

    public function __construct(
        ISaeRepository $saeRepository,
        ISaeGroupRepository $groupRepository
        )
    {
        $this->saeRepository = $saeRepository;
        $this->groupRepository = $groupRepository;
    }

    /**
     * Gets SAE details with role-based access control.
     *
     * @param int $saeId The SAE subject ID.
     * @param User $user The user requesting the details.
     * @return array|null Array with subject, groups, professors, client data or null if not found.
     */
    public function execute(int $saeId, User $user): ?array
    {
        $subject = $this->saeRepository->findById($saeId);
        if (!$subject) {
            return null;
        }

        // Get groups accessible by the user.
        $groups = $this->getAccessibleGroups($saeId, $user);

        // Get professors info.
        $responsibleProf = $this->saeRepository->getResponsibleProfessor($saeId);
        $allProfs = $this->saeRepository->getAllProfessorsInfo($saeId);

        // Get client info.
        $client = $this->saeRepository->getClientInfo($saeId);

        return [
            'subject' => $subject,
            'groups' => $groups,
            'responsible_professor' => $responsibleProf,
            'all_professors' => $allProfs,
            'client' => $client
        ];
    }

    /**
     * Gets groups accessible to the user based on their role.
     * 
     * Access rules:
     * - Responsible professor: all groups
     * - Assigned professor: only managed groups
     * - Student: only their group
     * - Client: all groups
     *
     * @param int $saeId The SAE ID.
     * @param User $user The user requesting access.
     * @return array[] Array of groups with their students.
     */
    private function getAccessibleGroups(int $saeId, User $user): array
    {
        $allGroups = $this->groupRepository->findBySaeId($saeId);

        // If responsible professor, return all groups.
        if ($user->isProfessor() && $this->saeRepository->isResponsibleProfessor($saeId, $user->getUserId())) {
            return array_map(function ($group) {
                return [
                    'group' => $group,
                    'students' => $this->groupRepository->getGroupStudents((int)$group->getId()),
                ];
            }, $allGroups);
        }

        // If assigned professor (managing specific groups), return only managed groups.
        if ($user->isProfessor()) {
            $managedGroups = $this->groupRepository->findByProfessorId($user->getUserId(), $saeId);

            return array_map(function ($group) {
                return [
                    'group' => $group,
                    'students' => $this->groupRepository->getGroupStudents((int)$group->getId()),
                ];
            }, $managedGroups);
        }

        // If student, return only their group.
        if ($user->isStudent()) {
            $userGroupId = $this->groupRepository->getStudentGroupId($user->getUserId(), $saeId);
            if ($userGroupId) {
                $group = $this->groupRepository->findById($userGroupId);
                if ($group) {
                    return [[
                            'group' => $group,
                            'students' => $this->groupRepository->getGroupStudents($userGroupId),
                        ]];
                }
            }
        }

        // Clients see all groups.
        if ($user->isClient()) {
            return array_map(function ($group) {
                return [
                    'group' => $group,
                    'students' => $this->groupRepository->getGroupStudents((int)$group->getId()),
                ];
            }, $allGroups);
        }

        return [];
    }
}