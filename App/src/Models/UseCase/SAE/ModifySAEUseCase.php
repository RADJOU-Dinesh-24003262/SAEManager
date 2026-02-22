<?php

namespace Models\UseCase\SAE;

use Exception;
use Models\Entity\User\User;
use Models\UseCase\SAE\InterfaceDB\SAESubjectInterface;
use Services\FileService;

/**
 * Use case for modifying an SAE.
 *
 * @category   Models
 * @package    Src
 * @subpackage Models/UseCase/SAE
 * @author     Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license    MIT License https://opensource.org/licenses/MIT
 * @link       https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class ModifySAEUseCase
{
    /**
     * The SAE subject interface.
     *
     * @var SAESubjectInterface
     */
    private SAESubjectInterface $subjectInterface;

    /**
     * Constructor.
     *
     * @param SAESubjectInterface $subjectInterface The SAE subject interface.
     */
    public function __construct(SAESubjectInterface $subjectInterface)
    {
        $this->subjectInterface = $subjectInterface;
    }

    /**
     * Executes the use case to modify an SAE.
     *
     * @param integer              $saeId       The SAE subject ID.
     * @param array<string, mixed> $data        The array mapping subject details.
     * @param string               $description The SAE description markdown content.
     * @param User                 $user        The user requesting the modification.
     * @return void
     * @throws Exception If modification fails or user doesn't have permission.
     */
    public function execute(int $saeId, array $data, string $description, User $user): void
    {
        $subject = $this->subjectInterface->findById($saeId);
        if (!$subject) {
            throw new Exception("SAE non trouvée.");
        }

        if ($subject->getResponsibleProfId() !== $user->getUserId()) {
            throw new Exception("Vous n'avez pas la permission de modifier cette SAE.");
        }

        $fileName = $this->subjectInterface->getFileName($saeId);

        if (!empty($fileName)) {
            try {
                FileService::updateSaeDescription($fileName, $description);
            } catch (Exception $e) {
                throw new Exception("Erreur lors de la mise à jour du fichier de description.");
            }
        }

        $subject->setSubjectName($data['subject_name']);

        if (isset($data['client_id']) && $data['client_id'] !== null) {
            $subject->setClientId($data['client_id']);
        } else {
            $subject->setClientId(null);
        }

        $subject->setBeginDate($data['begin_date']);
        $subject->setEndDate($data['end_date']);

        $this->subjectInterface->update($subject);
    }
}
