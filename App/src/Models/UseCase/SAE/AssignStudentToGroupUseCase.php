<?php

namespace Models\UseCase\SAE;

use Core\Includes\Exception\SAE\ExceptionAccessDenied;
use Core\Includes\Exception\SAE\ExceptionResourceNotFound;
use Models\UseCase\SAE\InterfaceDB\ParticipatedInInterface;
use Models\UseCase\SAE\InterfaceDB\SAEGroupInterface;
use Models\UseCase\SAE\InterfaceDB\SAESubjectInterface;
use Models\Entity\User\User;

/**
 * Use case for assigning a student to a SAE group.
 *
 * @category   Models
 * @package    Src
 * @subpackage Models/UseCase/SAE
 * @author     Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license    MIT License https://opensource.org/licenses/MIT
 * @link       https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class AssignStudentToGroupUseCase
{
    /**
     * The SAE group interface.
     *
     * @var SAEGroupInterface
     */
    private SAEGroupInterface $groupInterface;

    /**
     * The participated in interface.
     *
     * @var ParticipatedInInterface
     */
    private ParticipatedInInterface $participatedInInterface;

    /**
     * The SAE subject interface.
     *
     * @var SAESubjectInterface
     */
    private SAESubjectInterface $subjectInterface;

    /**
     * Constructor.
     *
     * @param SAEGroupInterface       $groupInterface          The SAE group interface.
     * @param ParticipatedInInterface $participatedInInterface The participated in interface.
     * @param SAESubjectInterface     $subjectInterface        The SAE subject interface.
     */
    public function __construct(
        SAEGroupInterface $groupInterface,
        ParticipatedInInterface $participatedInInterface,
        SAESubjectInterface $subjectInterface
    ) {
        $this->groupInterface = $groupInterface;
        $this->participatedInInterface = $participatedInInterface;
        $this->subjectInterface = $subjectInterface;
    }

    /**
     * Executes the use case.
     *
     * @param User    $professor The professor.
     * @param integer $studentId The student ID.
     * @param integer $groupId   The group ID.
     * @return boolean Success status.
     * @throws ExceptionResourceNotFound If group not found.
     * @throws ExceptionAccessDenied     If professor doesn't have permission.
     */
    public function execute(User $professor, int $studentId, int $groupId): bool
    {
        $group = $this->groupInterface->findById($groupId);
        if (!$group) {
            throw new ExceptionResourceNotFound("Groupe non trouvé");
        }

        $sae = $this->subjectInterface->findById($group->getSaeSubjectId());
        if (!$sae) {
            throw new ExceptionResourceNotFound("SAE associée non trouvée");
        }

        if ($sae->getResponsibleProfId() !== $professor->getUserId()) {
            throw new ExceptionAccessDenied("Vous n'avez pas la permission d'assigner des étudiants");
        }

        // Test if student is not already in a group for the same SAE.
        $saeId = (int) $sae->getSaeSubjectId();
        $studentGroup = $this->participatedInInterface->getStudentGroupId($studentId, $saeId);
        if ($studentGroup) {
            throw new ExceptionAccessDenied("L'étudiant est déjà dans un groupe pour cette SAE");
        }

        return $this->participatedInInterface->assignStudentToGroup($studentId, $groupId);
    }
}
