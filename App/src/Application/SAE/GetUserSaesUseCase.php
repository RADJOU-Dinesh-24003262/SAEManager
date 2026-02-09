<?php

namespace App\Application\SAE;

use App\Domain\SAE\IRepository\ISaeRepository;
use App\Domain\SAE\SaeSubject;
use App\Domain\User\User;

/**
 * Use case for retrieving all SAEs accessible to a user.
 * 
 * Returns SAEs based on role: professors (assigned), students (enrolled), clients (commissioned).
 *
 * @package App\Application\SAE
 */
class GetUserSaesUseCase
{
    private ISaeRepository $saeRepository;

    public function __construct(ISaeRepository $saeRepository)
    {
        $this->saeRepository = $saeRepository;
    }

    /**
     * Gets all SAEs accessible to a user.
     *
     * @param User $user The user.
     * @return SaeSubject[] Array of SAE subjects with responsible professor names.
     */
    public function execute(User $user): array
    {
        $saes = [];
        if ($user->isProfessor()) {
            $saes = $this->saeRepository->findByProfessorId($user->getUserId());
        }
        elseif ($user->isStudent()) {
            $saes = $this->saeRepository->findByStudentId($user->getUserId());
        }
        elseif ($user->isClient()) {
            $saes = $this->saeRepository->findByClientId($user->getUserId());
        }

        $saesSubjects = [];
        foreach ($saes as $sae) {
            $profName = null;
            if ($sae->getResponsibleProfessor()) {
                $profName = $sae->getResponsibleProfessor()->getFullName();
            }
            else {
                $profData = $this->saeRepository->getResponsibleProfessor($sae->getId());
                if ($profData) {
                    $profName = $profData['first_name'] . ' ' . $profData['last_name'];
                }
            }

            $saesSubjects[] = $sae;
        }

        return $saesSubjects;
    }
}