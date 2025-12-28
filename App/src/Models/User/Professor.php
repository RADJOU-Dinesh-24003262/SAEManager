<?php

namespace Models\User;

use PDO;
use Models\SAE\SAE;
use Core\includes\Database;

/**
 * Represents a professor user in the system.
 *
 * Provides operations specific to professors (saving, fetching
 * specific data, managing SAEs and groups).
 *
 * @category   Models
 * @package    Src
 * @subpackage Models/User
 * @author     Alexandre Benhafessa <alexandre.benhafessa@etu.univ-amu.fr>
 * @author     François Dargentolle <francois.dargentolle@etu.univ-amu.fr>
 * @author     William Edelstein <william.edelstein@etu.univ-amu.fr>
 * @author     Nathan Griguer <nathan.griguer@etu.univ-amu.fr>
 * @author     Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license    MIT License https://opensource.org/licenses/MIT
 * @link       https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class Professor extends User
{
    /**
     * AMU identifier of the professor.
     *
     * @var string
     */
    protected string $amu_id = '';

    /**
     * Initializes a new professor.
     *
     * Sets the user type and delegates initialization to the parent
     * constructor (User).
     *
     * @param array<string, string|integer> $data Optional initial data for the professor.
     */
    public function __construct(array $data = [])
    {
        $this->user_type = 'professor';
        parent::__construct($data);
    }

    /**
     * Saves the professor's specific data to the database.
     *
     * Inserts a row into the `professors` table.
     *
     * @param PDO     $connection PDO object representing the database connection.
     * @param integer $userId     User ID from the `users` table.
     *
     * @return void
     *
     * @throws \PDOException If an error occurs during query execution.
     */
    protected function saveSpecificData(PDO $connection, int $userId): void
    {
        $stmt = $connection->prepare(
            'INSERT INTO professors (professor_id, amu_id)
             VALUES (:professor_id, :amu_id)'
        );

        $stmt->execute(
            [
                'professor_id' => $userId,
                'amu_id' => $this->amu_id,
            ]
        );
    }

    /**
     * Fetches the professor's specific data from the database.
     *
     * Fills the object's properties corresponding to the retrieved columns.
     *
     * @param PDO    $db    PDO object representing the database connection.
     * @param string $email The user's email address linked to the professor.
     *
     * @return void
     *
     * @throws \PDOException If an error occurs during query execution.
     */
    protected function fetchSpecificData(PDO $db, string $email): void
    {
        $stmt = $db->prepare(
            'SELECT p.*
             FROM professors p
             JOIN users u ON p.professor_id = u.user_id
             WHERE u.email = :email'
        );

        $stmt->execute(['email' => $email]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($data) {
            foreach ($data as $key => $value) {
                if (property_exists($this, $key)) {
                    $this->$key = $value;
                }
            }
        }
    }

    /**
     * Retrieves the SAEs associated with the professor.
     *
     * @param PDO     $connection PDO object representing the database connection.
     * @param integer $userId     The professor's user ID.
     *
     * @return array<int, array<string, mixed>> Associative array containing the SAE records.
     *
     * @throws \PDOException If an error occurs during query execution.
     */
    protected function fetchSAEData(PDO $connection, int $userId): array
    {
        $stmt = $connection->prepare(
            '  SELECT * FROM SAE_subjects sae, professors
                                        WHERE (
                                                -- if the professor is responsible for the SAE
                                                sae.responsible_prof_id = professors.professor_id

                                                -- or if the professor is assigned to the SAE
                                                OR sae.sae_subject_id IN (
                                                    SELECT spg.sae_subject_id
                                                    FROM sae_professor_groups spg
                                                    WHERE spg.professor_id = professors.professor_id
                                                )
                                            )
                                        AND professors.professor_id = :user_id;'
        );
        $stmt->execute(['user_id' => $userId]);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $data;
    }

    /**
     * A professor can access an SAE if they are the responsible professor OR assigned to it.
     *
     * @param integer $saeId The SAE ID.
     * @return boolean True if accessible, false otherwise.
     */
    public function canAccessSAE(int $saeId): bool
    {
        try {
            $db = Database::getInstance();
            $stmt = $db->prepare(
                'SELECT COUNT(*) FROM sae_subjects s
                 WHERE s.sae_subject_id = :sae_id
                 AND (s.responsible_prof_id = :prof_id
                      OR EXISTS (
                          SELECT 1 FROM sae_professor_groups spg
                          WHERE spg.sae_subject_id = :sae_id AND spg.professor_id = :prof_id
                      )
                 )'
            );
            $stmt->execute(['sae_id' => $saeId, 'prof_id' => $this->user_id]);
            return $stmt->fetchColumn() > 0;
        } catch (\PDOException $e) {
            error_log('Erreur canAccessSAE (Professor) : ' . $e->getMessage());
            return false;
        }
    }

    /**
     * A professor can manage (create/update) an SAE if they are the responsible professor.
     * For creation (saeId = null), all professors are allowed to create.
     *
     * @param integer|null $saeId The SAE ID.
     * @return boolean True if allowed, false otherwise.
     */
    public function canManageSAE(?int $saeId = null): bool
    {
        // Création : Every professor can create a SAE.
        if ($saeId === null) {
            return true;
        }

        // Modification : Only the responsible professor can modify the SAE.
        return $this->isResponsibleProfessor($saeId);
    }

    /**
     * A responsible professor can see ALL groups; otherwise, only their assigned groups.
     *
     * @param integer $saeId The SAE ID.
     * @return array<int, array{
     *   user_id: int,
     *   first_name: string,
     *   last_name: string,
     *   email: string,
     *   phone: string,
     *   sae_group_id: int,
     *   td: int,
     *   tp: int
     * }> The list of accessible group members.
     */
    public function getAccessibleGroupMembers(int $saeId): array
    {
        if ($this->isResponsibleProfessor($saeId)) {
            return $this->getAllSAEMembers($saeId);
        }

        return $this->getAssignedGroupMembers($saeId);
    }
    /**
     * Retrieves ALL members of an SAE (for the responsible professor).
     *
     * @param integer $saeId The SAE ID.
     * @return array<int, array{
     *   user_id: int,
     *   first_name: string,
     *   last_name: string,
     *   email: string,
     *   phone: string,
     *   sae_group_id: int,
     *   td: int,
     *   tp: int
     * }> The list of all group members.
     */
    private function getAllSAEMembers(int $saeId): array
    {
        try {
            $db = Database::getInstance();
            $stmt = $db->prepare(
                'SELECT DISTINCT u.user_id, u.first_name, u.last_name, u.email, u.phone,
                        st.sae_group_id, st.td, st.tp
                 FROM sae_groups sg
                 JOIN students st ON sg.sae_group_id = st.sae_group_id
                 JOIN users u ON st.student_id = u.user_id
                 WHERE sg.sae_subject_id = :sae_id
                 ORDER BY st.sae_group_id, u.last_name, u.first_name'
            );
            $stmt->execute(['sae_id' => $saeId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log('Erreur getAllSAEMembers : ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Retrieves the members of the groups assigned to the professor.
     *
     * @param integer $saeId The SAE ID.
     * @return array<int, array{
     *   user_id: int,
     *   first_name: string,
     *   last_name: string,
     *   email: string,
     *   phone: string,
     *   sae_group_id: int,
     *   td: int,
     *   tp: int
     * }> The list of accessible group members.
     */
    private function getAssignedGroupMembers(int $saeId): array
    {
        try {
            $db = Database::getInstance();
            $stmt = $db->prepare(
                'SELECT DISTINCT u.user_id, u.first_name, u.last_name, u.email, u.phone,
                        st.sae_group_id, st.td, st.tp
                 FROM sae_professor_groups spg
                 JOIN sae_groups sg ON spg.sae_subject_id = sg.sae_subject_id
                 JOIN students st ON sg.sae_group_id = st.sae_group_id
                 JOIN users u ON st.student_id = u.user_id
                 WHERE spg.professor_id = :prof_id AND spg.sae_subject_id = :sae_id
                 ORDER BY st.sae_group_id, u.last_name, u.first_name'
            );
            $stmt->execute(['prof_id' => $this->user_id, 'sae_id' => $saeId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log('Erreur getAssignedGroupMembers : ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Checks whether this user is the responsible professor for an SAE.
     *
     * @param integer $saeId The SAE ID.
     * @return boolean If the user is the responsible professor.
     */
    public function isResponsibleProfessor(int $saeId): bool
    {
        try {
            $db = Database::getInstance();
            $stmt = $db->prepare(
                'SELECT COUNT(*) FROM sae_subjects 
                WHERE sae_subject_id = :sae_id AND responsible_prof_id = :prof_id'
            );
            $stmt->execute(['sae_id' => $saeId, 'prof_id' => $this->user_id]);
            return $stmt->fetchColumn() > 0;
        } catch (\PDOException $e) {
            error_log('Error in isResponsibleProfessor: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Checks if the professor can modify a to-do list item.
     *
     * @param integer $todoId The to-do item ID.
     * @return boolean Always false for professor.
     */
    public function canModifyTodo(int $todoId): bool
    {
        return false;
    }

    // -----------------
    // Getters
    // -----------------

    /**
     * Returns the professor's AMU identifier.
     *
     * @return string AMU identifier.
     */
    public function getAmuId(): string
    {
        return $this->amu_id;
    }
}
