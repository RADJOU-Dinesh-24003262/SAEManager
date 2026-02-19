<?php

namespace Models\UseCase\ToDoList;

use Core\includes\exception\SAE\ExceptionAccessDenied;
use Exception;
use Models\Entity\User\User;
use Models\Repository\SAE\PdoParticipatedInRepository;
use Models\Repository\SAE\PdoSAEGroupRepository;
use Models\Repository\SAE\PdoSAESubjectRepository;
use Models\Repository\ToDoList\PdoToDoListRepository;
use Models\Repository\User\PdoStudentRepository;
use Models\UseCase\ToDoList\GetTasksUseCase;

class GetToDoListContextUseCase
{
    private PdoSAESubjectRepository $saeSubjectRepo;
    private PdoSAEGroupRepository $saeGroupRepo;
    private PdoParticipatedInRepository $participatedInRepo;
    private PdoStudentRepository $studentRepo;
    private PdoToDoListRepository $todoListRepo;

    public function __construct(
        PdoSAESubjectRepository $saeSubjectRepo,
        PdoSAEGroupRepository $saeGroupRepo,
        PdoParticipatedInRepository $participatedInRepo,
        PdoStudentRepository $studentRepo,
        PdoToDoListRepository $todoListRepo
        )
    {
        $this->saeSubjectRepo = $saeSubjectRepo;
        $this->saeGroupRepo = $saeGroupRepo;
        $this->participatedInRepo = $participatedInRepo;
        $this->studentRepo = $studentRepo;
        $this->todoListRepo = $todoListRepo;
    }

    /**
     * Execute the use case.
     *
     * @param int $saeId
     * @param User $user
     * @param int|null $requestedGroupId
     * @return array
     * @throws Exception
     */
    public function execute(int $saeId, User $user, ?int $requestedGroupId = null): array
    {
        $currentGroupId = null;
        $tasks = [];

        // Fetch basic SAE data.
        $saeSubject = $this->saeSubjectRepo->findById($saeId);
        if (!$saeSubject) {
            throw new Exception("SAE introuvable.");
        }

        // Reconstruct groups structure expected by view/logic.
        $groups = $this->saeGroupRepo->findBySaeSubjectId($saeId);
        $formattedGroups = [];
        foreach ($groups as $group) {
            $students = $this->saeGroupRepo->getStudentsInGroup((int)$group->getSaeGroupId());
            $formattedGroups[] = ['group' => $group, 'students' => $students];
        }
        $allGroups = $formattedGroups;

        if ($user->isStudent()) {
            if (!$this->studentRepo->canAccessSAE($user->getUserId(), $saeId)) {
                throw new ExceptionAccessDenied("Accès refusé à cette SAE.");
            }
            $currentGroupId = $this->participatedInRepo->getStudentGroupId($user->getUserId(), $saeId);
        }
        elseif ($user->isProfessor()) {
            // Check if a specific group is selected via GET parameter.
            if ($requestedGroupId !== null) {
                // Verify if the professor has access to this group (exists in this SAE).
                foreach ($allGroups as $groupData) {
                    if ($groupData['group']->getSaeGroupId() == $requestedGroupId) {
                        $currentGroupId = $requestedGroupId;
                        break;
                    }
                }
            }
        }

        // Fetch tasks if a group is identified.
        if ($currentGroupId) {
            $getTasksUseCase = new GetTasksUseCase($this->todoListRepo);
            $tasks = $getTasksUseCase->execute($currentGroupId);
        }

        return [
            'subject' => $saeSubject,
            'groups' => $formattedGroups, // Keeping key as 'groups' in intermediate array but controller might use 'all_groups'
            'current_group_id' => $currentGroupId,
            'tasks' => $tasks,
            'all_groups' => $allGroups
        ];
    }
}