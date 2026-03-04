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

/**
 * Use case to get context data for Todo list.
 *
 * @category   Models
 * @package    Src
 * @subpackage Models/UseCase/ToDoList
 * @author     Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license    MIT License https://opensource.org/licenses/MIT
 * @link       https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class GetToDoListContextUseCase
{
    /**
     * The SAE subject repository.
     *
     * @var PdoSAESubjectRepository
     */
    private PdoSAESubjectRepository $saeSubjectRepo;

    /**
     * The SAE group repository.
     *
     * @var PdoSAEGroupRepository
     */
    private PdoSAEGroupRepository $saeGroupRepo;

    /**
     * The participated in repository.
     *
     * @var PdoParticipatedInRepository
     */
    private PdoParticipatedInRepository $participatedInRepo;

    /**
     * The student repository.
     *
     * @var PdoStudentRepository
     */
    private PdoStudentRepository $studentRepo;

    /**
     * The to-do list repository.
     *
     * @var PdoToDoListRepository
     */
    private PdoToDoListRepository $todoListRepo;

    /**
     * Constructor.
     *
     * @param PdoSAESubjectRepository     $saeSubjectRepo     The SAE subject repository.
     * @param PdoSAEGroupRepository       $saeGroupRepo       The SAE group repository.
     * @param PdoParticipatedInRepository $participatedInRepo The participated in repository.
     * @param PdoStudentRepository        $studentRepo        The student repository.
     * @param PdoToDoListRepository       $todoListRepo       The to-do list repository.
     */
    public function __construct(
        PdoSAESubjectRepository $saeSubjectRepo,
        PdoSAEGroupRepository $saeGroupRepo,
        PdoParticipatedInRepository $participatedInRepo,
        PdoStudentRepository $studentRepo,
        PdoToDoListRepository $todoListRepo
    ) {
        $this->saeSubjectRepo = $saeSubjectRepo;
        $this->saeGroupRepo = $saeGroupRepo;
        $this->participatedInRepo = $participatedInRepo;
        $this->studentRepo = $studentRepo;
        $this->todoListRepo = $todoListRepo;
    }

    /**
     * Execute the use case.
     *
     * @param integer      $saeId            The SAE ID.
     * @param User         $user             The current user.
     * @param integer|null $requestedGroupId The requested group ID (optional).
     * @return array<string, mixed>
     * @throws Exception If SAE is not found.
     * @throws ExceptionAccessDenied If user lacks permission.
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
        } elseif ($user->isProfessor()) {
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
            'groups' => $formattedGroups,
            'current_group_id' => $currentGroupId,
            'tasks' => $tasks,
            'all_groups' => $allGroups
        ];
    }
}
