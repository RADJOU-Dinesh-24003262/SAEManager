<?php

namespace Models\UseCase\ToDoList;

use Core\Includes\Exception\SAE\ExceptionAccessDenied;
use Models\Entity\User\User;
use Models\UseCase\SAE\InterfaceDB\ParticipatedInInterface;
use Models\UseCase\SAE\InterfaceDB\SAEGroupInterface;
use Models\UseCase\SAE\InterfaceDB\SAESubjectInterface;
use Models\UseCase\User\InterfaceDB\ClientInterface;
use Models\UseCase\User\InterfaceDB\ProfessorInterface;
use Models\UseCase\User\InterfaceDB\StudentInterface;
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
    /** @var SAESubjectInterface */
    private SAESubjectInterface $subjectRepo;
    /** @var SAEGroupInterface */
    private SAEGroupInterface $groupRepo;
    /** @var ParticipatedInInterface */
    private ParticipatedInInterface $participatedInRepo;
    /** @var StudentInterface */
    private StudentInterface $studentRepo;
    /** @var ProfessorInterface */
    private ProfessorInterface $professorRepo;
    /** @var ClientInterface */
    private ClientInterface $clientRepo;

    /**
     * Constructor for ValidateToDoListModifyAccessUseCase.
     *
     * @param SAESubjectInterface     $subjectRepo        Repo for subjects.
     * @param SAEGroupInterface       $groupRepo          Repo for groups.
     * @param ParticipatedInInterface $participatedInRepo Repo for student participations.
     * @param StudentInterface        $studentRepo        Repo for students.
     * @param ProfessorInterface      $professorRepo      Repo for professors.
     * @param ClientInterface         $clientRepo         Repo for clients.
     */
    public function __construct(
        SAESubjectInterface $subjectRepo,
        SAEGroupInterface $groupRepo,
        ParticipatedInInterface $participatedInRepo,
        StudentInterface $studentRepo,
        ProfessorInterface $professorRepo,
        ClientInterface $clientRepo
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
        $groupId = (int)$groupData['group']->getSaeGroupId();

        return $groupId;
    }
}
