<?php

namespace Models\UseCase\SAE;

use Core\includes\exception\SAE\ExceptionAccessDenied;
use Core\includes\exception\SAE\ExceptionResourceNotFound;
use Models\Entity\SAE\SAEGroup;
use Models\UseCase\SAE\InterfaceDB\SAEGroupInterface;
use Models\UseCase\SAE\InterfaceDB\SAESubjectInterface;
use Models\Entity\User\User;

/**
 * Use case for creating a SAE group.
 *
 * @category   Models
 * @package    Src
 * @subpackage Models/UseCase/SAE
 * @author     Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license    MIT License https://opensource.org/licenses/MIT
 * @link       https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class CreateSAEGroupUseCase
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
     * @param User         $creator     The user creating the group.
     * @param integer      $saeId       The SAE subject ID.
     * @param integer|null $professorId The professor ID (optional).
     * @return SAEGroup The created group.
     * @throws ExceptionAccessDenied      If user doesn't have permission.
     * @throws ExceptionResourceNotFound  If SAE not found.
     */
    public function execute(User $creator, int $saeId, ?int $professorId = null): SAEGroup
    {
        $sae = $this->subjectInterface->findById($saeId);
        if (!$sae) {
            throw new ExceptionResourceNotFound("SAE non trouvée");
        }

        if ($sae->getResponsibleProfId() !== $creator->getUserId()) {
            throw new ExceptionAccessDenied("Vous n'avez pas la permission de créer un groupe pour cette SAE");
        }

        $group = new SAEGroup([
            'sae_subject_id' => $saeId,
            'professor_id' => $professorId
        ]);

        return $this->groupInterface->create($group);
    }
}
