<?php

namespace Services;

use Models\UseCase\SAE\InterfaceDB\SAESubjectInterface;
use Exception;
use PDOException;

/**
 * Service to handle file-related operations for SAE Subjects.
 *
 * Implements the Single Responsibility Principle (SRP) by moving
 * file path logic out of the Repository.
 *
 * @category   Services
 * @package    Src
 * @subpackage Services
 * @author     Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license    MIT License https://opensource.org/licenses/MIT
 * @link       https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class SAESubjectFileService
{
    /**
     * @var SAESubjectInterface
     */
    private SAESubjectInterface $repository;

    /**
     * Constructor.
     *
     * @param SAESubjectInterface $repository The repository to use to get the file name.
     */
    public function __construct(SAESubjectInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Gets the file name (path) of the SAE subject.
     *
     * @param integer $saeId The SAE subject ID.
     * @return string The relative file path.
     * @throws PDOException If query fails.
     */
    public function getFileName(int $saeId): string
    {
        $subject = $this->repository->findById($saeId);

        if (!$subject) {
            throw new PDOException('SAE subject not found');
        }

        return $subject->getFilePath() ?? '';
    }
}
