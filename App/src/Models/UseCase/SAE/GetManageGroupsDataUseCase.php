<?php

namespace Models\UseCase\SAE;

use Core\Includes\Exception\SAE\ExceptionAccessDenied;
use Models\Entity\User\Professor;
use Models\UseCase\SAE\InterfaceDB\ParticipatedInInterface;
use Models\UseCase\SAE\InterfaceDB\SAEGroupInterface;
use Models\UseCase\SAE\InterfaceDB\SAESubjectInterface;
use Models\UseCase\User\InterfaceDB\ClientInterface;
use Models\UseCase\User\InterfaceDB\ProfessorInterface;
use Models\UseCase\User\InterfaceDB\StudentInterface;
use Models\Entity\User\User;

/**
 * Use case for getting data needed to manage SAE groups.
 *
 * @category   Models
 * @package    Src
 * @subpackage Models/UseCase/SAE
 * @author     Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license    MIT License https://opensource.org/licenses/MIT
 * @link       https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class GetManageGroupsDataUseCase
{
    /**
     * The SAE subject repository.
     *
     * @var SAESubjectInterface
     */
    private SAESubjectInterface $subjectRepo;

    /**
     * The SAE group repository.
     *
     * @var SAEGroupInterface
     */
    private SAEGroupInterface $groupRepo;

    /**
     * The repository linking students to groups.
     *
     * @var ParticipatedInInterface
     */
    private ParticipatedInInterface $participatedInRepo;

    /**
     * The student repository.
     *
     * @var StudentInterface
     */
    private StudentInterface $studentRepo;

    /**
     * The professor repository.
     *
     * @var ProfessorInterface
     */
    private ProfessorInterface $professorRepo;

    /**
     * The client repository.
     *
     * @var ClientInterface
     */
    private ClientInterface $clientRepo;

    /**
     * Constructor.
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
     * Execute the use case.
     *
     * @param integer $saeId The SAE ID.
     * @param User    $user  The current user (must be professor).
     * @return array<string, mixed> The data needed for the view.
     * @throws ExceptionAccessDenied If access is denied.
     */
    public function execute(int $saeId, User $user): array
    {
        $getCompleteSAEDataUseCase = new GetCompleteSAEDataUseCase(
            $this->subjectRepo,
            $this->groupRepo,
            $this->participatedInRepo,
            $this->studentRepo,
            $this->professorRepo,
            $this->clientRepo
        );

        $saeData = $getCompleteSAEDataUseCase->execute($saeId, $user);

        if (!$saeData) {
            throw new ExceptionAccessDenied("Accès refusé ou SAE introuvable.");
        }

        // Verify ownership.
        $responsibleProfId = $saeData['responsible_professor']['user_id'] ?? null;
        if ($user->getUserId() !== (int)$responsibleProfId) {
            throw new ExceptionAccessDenied("Vous n'avez pas la permission de gérer les groupes.");
        }

        // Get available students.
        $students = $this->studentRepo->findStudentsNotInSAE($saeId);
        $availableStudents = array_map(fn ($s) => $s->toArray(), $students);

        // Get available professors.
        $profsAvailable = [];
        $profs = $this->professorRepo->findAll();
        $profsAvailable = array_map(fn ($p) => $p->toArray(), $profs);

        return [
            'sae' => $saeData,
            'available_students' => $availableStudents,
            'all_professors' => $profsAvailable
        ];
    }
}
