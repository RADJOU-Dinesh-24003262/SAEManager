<?php

namespace Models\UseCase\SAE;

use Core\includes\exception\SAE\ExceptionAccessDenied;
use Core\includes\exception\SAE\ExceptionResourceNotFound;
use Models\UseCase\SAE\InterfaceDB\SAEGroupInterface;
use Models\UseCase\SAE\InterfaceDB\SAESubjectInterface;
use Models\Entity\User\User;

/**
 * Use case for deleting a SAE group.
 *
 * @category   Models
 * @package    Src
 * @subpackage Models/UseCase/SAE
 * @author     Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license    MIT License https://opensource.org/licenses/MIT
 * @link       https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class DeleteSAEGroupUseCase
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
     * @param User    $user    The user deleting the group.
     * @param integer $groupId The group ID.
     * @return boolean Success status.
     * @throws ExceptionResourceNotFound If group not found.
     * @throws ExceptionAccessDenied     If user doesn't have permission.
     */
    public function execute(User $user, int $groupId): bool
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
            throw new ExceptionAccessDenied("Vous n'avez pas la permission de supprimer ce groupe");
        }

        return $this->groupInterface->delete($groupId);
    }
}
