<?php

namespace App\Application\SAE;

use App\Domain\SAE\IRepository\ISaeRepository;
use App\Domain\User\User;
use App\Domain\SAE\Exception\AccessDeniedException;
use App\Domain\SAE\Exception\ResourceNotFoundException;

/**
 * Use case for updating an existing SAE subject.
 * 
 * Only the responsible professor can update a SAE.
 *
 * @package App\Application\SAE
 */
class UpdateSaeUseCase
{
    private ISaeRepository $saeRepository;

    public function __construct(ISaeRepository $saeRepository)
    {
        $this->saeRepository = $saeRepository;
    }

    /**
     * Updates a SAE subject.
     *
     * @param User $user The user attempting to update (must be responsible professor).
     * @param int $saeId The SAE ID to update.
     * @param array $data Updated SAE data (subject_name?, responsible_prof_id?, client_id?, begin_date?, end_date?, file_path?).
     * @return bool True on success, false on failure.
     * @throws AccessDeniedException If user is not the responsible professor.
     * @throws ResourceNotFoundException If SAE not found.
     * @throws \PDOException If database operation fails.
     */
    public function execute(User $user, int $saeId, array $data): bool
    {
        if (!$user->isProfessor() || !$this->saeRepository->isResponsibleProfessor($saeId, $user->getUserId())) {
            throw new AccessDeniedException("Vous n'avez pas la permission de modifier cette SAE");
        }

        $sae = $this->saeRepository->findById($saeId);
        if (!$sae) {
            throw new ResourceNotFoundException("SAE non trouvée");
        }

        // Update fields if present in data
        if (isset($data['subject_name'])) {
            $sae->setName($data['subject_name']);
        }
        if (isset($data['responsible_prof_id'])) {
            $sae->setResponsibleProfessorId((int)$data['responsible_prof_id']);
        }
        if (isset($data['client_id'])) {
            $sae->setClientId($data['client_id'] ? (int)$data['client_id'] : null);
        }
        if (isset($data['begin_date'])) {
            $sae->setBeginDate($data['begin_date']);
        }
        if (isset($data['end_date'])) {
            $sae->setEndDate($data['end_date']);
        }
        if (isset($data['file_path'])) {
            $sae->setDescriptionFilePath($data['file_path']);
        }

        return $this->saeRepository->update($sae);
    }
}