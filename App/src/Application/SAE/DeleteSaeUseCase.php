<?php

namespace App\Application\SAE;

use App\Domain\SAE\IRepository\ISaeRepository;
use App\Domain\User\User;
use App\Domain\SAE\Exception\AccessDeniedException;

/**
 * Use case for deleting a SAE subject.
 * 
 * Only the responsible professor can delete a SAE.
 *
 * @package App\Application\SAE
 */
class DeleteSaeUseCase
{
    private ISaeRepository $saeRepository;

    public function __construct(ISaeRepository $saeRepository)
    {
        $this->saeRepository = $saeRepository;
    }

    /**
     * Deletes a SAE subject.
     *
     * @param User $user The user attempting to delete (must be responsible professor).
     * @param int $saeId The SAE ID to delete.
     * @return bool True on success, false on failure.
     * @throws AccessDeniedException If user is not the responsible professor.
     * @throws \PDOException If database operation fails.
     */
    public function execute(User $user, int $saeId): bool
    {
        if ($user->isProfessor() && $this->saeRepository->isResponsibleProfessor($saeId, $user->getUserId())) {
            return $this->saeRepository->delete($saeId);
        }
        throw new AccessDeniedException("Seul le responsable peut supprimer la SAE");
    }
}