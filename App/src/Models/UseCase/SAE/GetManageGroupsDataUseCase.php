<?php

namespace Models\UseCase\SAE;

use Core\includes\exception\SAE\ExceptionAccessDenied;
use Models\Entity\User\Professor;
use Models\Repository\SAE\PdoParticipatedInRepository;
use Models\Repository\SAE\PdoSAEGroupRepository;
use Models\Repository\SAE\PdoSAESubjectRepository;
use Models\Repository\User\PdoClientRepository;
use Models\Repository\User\PdoProfessorRepository;
use Models\Repository\User\PdoStudentRepository;
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
     * @var PdoSAESubjectRepository
     */
    private PdoSAESubjectRepository $subjectRepo;

    /**
     * The SAE group repository.
     *
     * @var PdoSAEGroupRepository
     */
    private PdoSAEGroupRepository $groupRepo;

    /**
     * The repository linking students to groups.
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
     * The professor repository.
     *
     * @var PdoProfessorRepository
     */
    private PdoProfessorRepository $professorRepo;

    /**
     * The client repository.
     *
     * @var PdoClientRepository
     */
    private PdoClientRepository $clientRepo;

    /**
     * Constructor.
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
        $availableStudents = array_map(fn($s) => $s->toArray(), $students);

        // Get available professors.
        $profsAvailable = [];
        $profs = $this->professorRepo->findAll();
        $profsAvailable = array_map(fn($p) => $p->toArray(), $profs);

        return [
            'sae' => $saeData,
            'available_students' => $availableStudents,
            'all_professors' => $profsAvailable
        ];
    }
}
