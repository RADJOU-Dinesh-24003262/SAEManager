<?php

namespace Models\UseCase\SAE;

use Core\includes\exception\SAE\ExceptionAccessDenied;
use Models\Entity\SAE\SAESubject;
use Models\UseCase\SAE\InterfaceDB\SAESubjectInterface;
use Models\Entity\User\User;

/**
 * Use case for creating a new SAE.
 *
 * @category   Models
 * @package    Src
 * @subpackage Models/UseCase/SAE
 * @author     Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license    MIT License https://opensource.org/licenses/MIT
 * @link       https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class CreateSAEUseCase
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
     * @param User                 $creator The professor creating the SAE.
     * @param array<string, mixed> $data    SAE data.
     * @return SAESubject The created SAE.
     * @throws ExceptionAccessDenied If user doesn't have permission.
     */
    public function execute(User $creator, array $data): SAESubject
    {
        // Check permission.
        if (!$creator->isProfessor()) {
            throw new ExceptionAccessDenied("Vous n'avez pas la permission de créer une SAE");
        }

        // Create SAE subject.
        $subject = new SAESubject($data);

        return $this->subjectInterface->create($subject);
    }
}
