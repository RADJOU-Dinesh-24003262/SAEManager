<?php

namespace Models\UseCase\ToDoList;

use Core\includes\exception\SAE\ExceptionAccessDenied;
use Models\Entity\User\User;
use Models\Repository\SAE\PdoParticipatedInRepository;
use Models\Repository\SAE\PdoSAEGroupRepository;
use Models\Repository\SAE\PdoSAESubjectRepository;
use Models\Repository\User\PdoClientRepository;
use Models\Repository\User\PdoProfessorRepository;
use Models\Repository\User\PdoStudentRepository;
use Models\UseCase\SAE\GetCompleteSAEDataUseCase;

/**
 * Use case to validate if a user can modify a TO-DO list.
 *
 * @category   Models
 * @package    Src
 * @subpackage Models/UseCase/ToDoList
 * @author     Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license    MIT License https://opensource.org/licenses/MIT
 * @link       https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class ValidateToDoListModifyAccessUseCase
{
    /** @var PdoSAESubjectRepository */
    private PdoSAESubjectRepository $subjectRepo;
    /** @var PdoSAEGroupRepository */
    private PdoSAEGroupRepository $groupRepo;
    /** @var PdoParticipatedInRepository */
    private PdoParticipatedInRepository $participatedInRepo;
    /** @var PdoStudentRepository */
    private PdoStudentRepository $studentRepo;
    /** @var PdoProfessorRepository */
    private PdoProfessorRepository $professorRepo;
    /** @var PdoClientRepository */
    private PdoClientRepository $clientRepo;

    /**
     * Constructor for ValidateToDoListModifyAccessUseCase.
     *
     * @param PdoSAESubjectRepository     $subjectRepo        Repo for subjects.
     * @param PdoSAEGroupRepository       $groupRepo          Repo for groups.
     * @param PdoParticipatedInRepository $participatedInRepo Repo for student participations.
     * @param PdoStudentRepository        $studentRepo        Repo for students.
     * @param PdoProfessorRepository      $professorRepo      Repo for professors.
     * @param PdoClientRepository         $clientRepo         Repo for clients.
     */
    public function __construct(
        PdoSAESubjectRepository $subjectRepo,
        PdoSAEGroupRepository $groupRepo,
        PdoParticipatedInRepository $participatedInRepo,
        PdoStudentRepository $studentRepo,
        PdoProfessorRepository $professorRepo,
        PdoClientRepository $clientRepo
    ) {
        $this->subjectRepo = $subjectRepo;
        $this->groupRepo = $groupRepo;
        $this->participatedInRepo = $participatedInRepo;
        $this->studentRepo = $studentRepo;
        $this->professorRepo = $professorRepo;
        $this->clientRepo = $clientRepo;
    }

    /**
     * Determine Target Group ID and Validate Access.
     *
     * @param User    $user  User requesting access.
     * @param integer $saeId The SAE ID.
     * @return integer Target Group ID
     * @throws ExceptionAccessDenied If user lacks permission.
     */
    public function execute(User $user, int $saeId): int
    {
        // 1. Check SAE Access
        if ($user->isStudent()) {
            if (!$this->studentRepo->canAccessSAE($user->getUserId(), $saeId)) {
                throw new ExceptionAccessDenied("Vous n'avez pas accès à cette SAE.");
            }
        }

        if (!$user->isStudent()) {
            throw new ExceptionAccessDenied("Vous n'avez pas le droit de modifier cette To-Do List.");
        }

        // 2. Determine Target Group ID using GetCompleteSAEDataUseCase
        // Reuse GetCompleteSAEDataUseCase as in original code
        $useCase = new GetCompleteSAEDataUseCase(
            $this->subjectRepo,
            $this->groupRepo,
            $this->participatedInRepo,
            $this->studentRepo,
            $this->professorRepo,
            $this->clientRepo
        );
        $saeData = $useCase->execute($saeId, $user);

        if (!$saeData) {
            throw new ExceptionAccessDenied("Accès non autorisé à cette SAE.", 403);
        }

        if (empty($saeData['groups'])) {
            throw new ExceptionAccessDenied("Vous n'êtes assigné à aucun groupe.", 403);
        }

        $groupData = reset($saeData['groups']);
        $groupId = (int) $groupData['group']->getSaeGroupId();

        return $groupId;
    }
}
