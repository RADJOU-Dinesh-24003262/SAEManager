<?php

namespace Models\UseCase\SAE;

use Exception;
use Models\Entity\User\User;
use Models\UseCase\SAE\InterfaceDB\SAESubjectInterface;
use Services\FileService;
use Services\SAESubjectFileService;

/**
 * Use case for deleting an SAE.
 *
 * @category   Models
 * @package    Src
 * @subpackage Models/UseCase/SAE
 * @author     Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license    MIT License https://opensource.org/licenses/MIT
 * @link       https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class DeleteSAEUseCase
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
     * Executes the use case to delete an SAE.
     *
     * @param integer $saeId The SAE subject ID.
     * @param User    $user  The user requesting the deletion.
     * @return void
     * @throws Exception If deletion fails or user doesn't have permission.
     */
    public function execute(int $saeId, User $user): void
    {
        $subject = $this->subjectInterface->findById($saeId);
        if (!$subject) {
            throw new Exception("SAE non trouvée.");
        }

        if ($subject->getResponsibleProfId() !== $user->getUserId()) {
            throw new Exception("Vous n'avez pas la permission de modifier cette SAE.");
        }

        $fileService = new SAESubjectFileService($this->subjectInterface);
        $fileName = $fileService->getFileName($saeId);

        if (!empty($fileName)) {
            try {
                FileService::removeFile($fileName);
            } catch (Exception $e) {
            // Ignore file not found errors if we are deleting.
            }
        }

        $this->subjectInterface->delete($saeId);
    }
}
