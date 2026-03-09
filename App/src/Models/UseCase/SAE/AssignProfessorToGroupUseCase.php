<?php

namespace Models\UseCase\SAE;

use Core\Includes\Exception\SAE\ExceptionAccessDenied;
use Core\Includes\Exception\SAE\ExceptionResourceNotFound;
use Models\UseCase\SAE\InterfaceDB\SAEGroupInterface;
use Models\UseCase\SAE\InterfaceDB\SAESubjectInterface;
use Models\Entity\User\User;

/**
 * Use case for assigning a professor to a SAE group.
 *
 * @category   Models
 * @package    Src
 * @subpackage Models/UseCase/SAE
 * @author     Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license    MIT License https://opensource.org/licenses/MIT
 * @link       https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class AssignProfessorToGroupUseCase
{
    /**
     * The SAE group interface.
     *
     * @var SAEGroupInterface
     */
    private SAEGroupInterface $groupInterface;

    /**
     * The SAE subject interface.
     *
     * @var SAESubjectInterface
     */
    private SAESubjectInterface $subjectInterface;

    /**
     * Constructor.
     *
     * @param SAEGroupInterface   $groupInterface   The SAE group interface.
     * @param SAESubjectInterface $subjectInterface The SAE subject interface.
     */
    public function __construct(SAEGroupInterface $groupInterface, SAESubjectInterface $subjectInterface)
    {
        $this->groupInterface = $groupInterface;
        $this->subjectInterface = $subjectInterface;
    }

    /**
     * Executes the use case.
     *
     * @param User    $user        The user performing the action.
     * @param integer $groupId     The group ID.
     * @param integer $professorId The professor ID.
     * @return boolean Success status.
     * @throws ExceptionResourceNotFound If group not found.
     * @throws ExceptionAccessDenied     If user doesn't have permission.
     */
    public function execute(User $user, int $groupId, int $professorId): bool
    {
        $group = $this->groupInterface->findById($groupId);
        if (!$group) {
            throw new ExceptionResourceNotFound("Groupe non trouvé");
        }

        $sae = $this->subjectInterface->findById($group->getSaeSubjectId());
        if (!$sae) {
            throw new ExceptionResourceNotFound("SAE associée non trouvée");
        }

        if ($sae->getResponsibleProfId() !== $user->getUserId()) {
            throw new ExceptionAccessDenied("Vous n'avez pas la permission de gérer ce groupe");
        }

        $group->setProfessorId($professorId);

        return $this->groupInterface->update($group);
    }
}
