<?php

namespace Models\UseCase\SAE;

use Core\includes\exception\ExceptionBD\ExceptionFetchDataBD;
use Models\Entity\SAE\SAESubject;
use Models\UseCase\SAE\InterfaceDB\SAESubjectInterface;
use Models\Entity\User\User;

/**
 * Use case for retrieving SAEs accessible by a user.
 *
 * @category   Models
 * @package    Src
 * @subpackage Models/UseCase/SAE
 * @author     Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license    MIT License https://opensource.org/licenses/MIT
 * @link       https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class GetUserSAEsUseCase
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
     * Executes the use case.
     *
     * @param User $user The requesting user.
     * @return array<SAESubject> Array of SAE subjects accessible by the user.
     * @throws ExceptionFetchDataBD If data cannot be fetched.
     */
    public function execute(User $user): array
    {
        if ($user->isProfessor()) {
            return $this->subjectInterface->findByProfessorId($user->getUserId());
        }

        if ($user->isStudent()) {
            return $this->subjectInterface->findByStudentId($user->getUserId());
        }

        if ($user->isClient()) {
            return $this->subjectInterface->findByClientId($user->getUserId());
        }

        return [];
    }
}
