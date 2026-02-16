<?php

namespace Models\Repository\SAE;

use Core\includes\Database;
use Models\UseCase\SAE\InterfaceDB\ParticipatedInInterface;
use PDO;
use PDOException;

/**
 * PDO implementation of ParticipatedInInterface.
 *
 * This is the Infrastructure layer implementation of the Interface.
 *
 * @category   Models
 * @package    Src
 * @subpackage Models/Repository/SAE
 * @author     Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license    MIT License https://opensource.org/licenses/MIT
 * @link       https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class PdoParticipatedInRepository implements ParticipatedInInterface
{
    /**
     * The singleton instance.
     * @var PdoParticipatedInRepository|null
     */
    protected static ?PdoParticipatedInRepository $instance = null;

    /**
     * The database connection.
     * @var PDO
     */
    protected PDO $connection;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->connection = Database::getInstance();
    }


    /**
     * Assigns a student to a group.
     *
     * @param integer $studentId The student ID.
     * @param integer $groupId   The SAE group ID.
     * @return boolean True on success.
     */
    public function assignStudentToGroup(int $studentId, int $groupId): bool
    {
        try {
            $stmt = $this->connection->prepare(
                'INSERT INTO participated_in (student_id, sae_group_id) 
                 VALUES (:student_id, :group_id)'
            );
            return $stmt->execute(['group_id' => $groupId, 'student_id' => $studentId]);
        } catch (PDOException $e) {
            error_log('Erreur assignation étudiant : ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Removes a student from a group.
     *
     * @param integer $studentId The student ID.
     * @param integer $groupId   The SAE group ID.
     * @return boolean True on success.
     */
    public function removeStudentFromGroup(int $studentId, int $groupId): bool
    {
        try {
            $stmt = $this->connection->prepare(
                'DELETE FROM participated_in 
                 WHERE student_id = :student_id AND sae_group_id = :group_id'
            );
            return $stmt->execute(['student_id' => $studentId, 'group_id' => $groupId]);
        } catch (PDOException $e) {
            error_log('Erreur désassignation étudiant : ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Gets the group ID for a student in a specific SAE.
     *
     * @param integer $studentId    The student ID.
     * @param integer $saeSubjectId The SAE subject ID.
     * @return integer|null The group ID or null if not found.
     */
    public function getStudentGroupId(int $studentId, int $saeSubjectId): ?int
    {
        try {
            $stmt = $this->connection->prepare(
                'SELECT pi.sae_group_id 
                FROM participated_in pi
                JOIN sae_groups sg ON pi.sae_group_id = sg.sae_group_id
                WHERE pi.student_id = :student_id AND sg.sae_subject_id = :sae_id'
            );
            $stmt->execute(['student_id' => $studentId, 'sae_id' => $saeSubjectId]);
            $result = $stmt->fetchColumn();
            return $result ? intval($result) : null;
        } catch (PDOException $e) {
            error_log('Erreur récupération groupe étudiant : ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Checks if a student is in a group.
     *
     * @param integer $studentId The student ID.
     * @param integer $groupId   The SAE group ID.
     * @return boolean True if student is in the group.
     */
    public function isStudentInGroup(int $studentId, int $groupId): bool
    {
        try {
            $stmt = $this->connection->prepare(
                'SELECT COUNT(*) FROM participated_in 
                 WHERE student_id = :student_id AND sae_group_id = :group_id'
            );
            $stmt->execute(['student_id' => $studentId, 'group_id' => $groupId]);
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log('Erreur vérification participation : ' . $e->getMessage());
            return false;
        }
    }
}
