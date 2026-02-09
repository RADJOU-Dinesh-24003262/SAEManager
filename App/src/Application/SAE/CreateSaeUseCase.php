<?php

namespace App\Application\SAE;

use App\Domain\SAE\IRepository\ISaeRepository;
use App\Domain\SAE\SaeSubject;
use App\Domain\User\User;
use App\Domain\SAE\Exception\AccessDeniedException;

/**
 * Use case for creating a new SAE subject.
 * 
 * Only professors can create SAE subjects.
 *
 * @package App\Application\SAE
 */
class CreateSaeUseCase
{
    private ISaeRepository $saeRepository;

    public function __construct(ISaeRepository $saeRepository)
    {
        $this->saeRepository = $saeRepository;
    }

    /**
     * Creates a new SAE subject.
     *
     * @param User $creator The professor creating the SAE.
     * @param array $data SAE data (responsible_prof_id, subject_name, begin_date, end_date, client_id?, file_path?).
     * @return SaeSubject The created SAE subject.
     * @throws AccessDeniedException If creator is not a professor.
     * @throws \PDOException If database operation fails.
     */
    public function execute(User $creator, array $data): SaeSubject
    {
        if (!$creator->isProfessor()) {
            throw new AccessDeniedException("Vous n'avez pas la permission de créer une SAE");
        }

        $sae = new SaeSubject(
            (int)$data['responsible_prof_id'],
            $data['subject_name'],
            $data['begin_date'],
            $data['end_date'],
            isset($data['client_id']) ? (int)$data['client_id'] : null,
            $data['file_path'] ?? null
            );

        $id = $this->saeRepository->save($sae);
        // The repository sets the ID on the object, but we can also fetch it fresh if needed.
        // Assuming save modifies the object reference or returns ID.
        // Our repo implementation updates the object ID.
        return $sae;
    }
}