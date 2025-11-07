<?php

namespace Models\User;

use PDO;
use Models\SAE\SAE;

/**
 * Represents a professor user in the system.
 *
 * Provides operations specific to professors (saving, fetching
 * specific data, managing SAEs and groups).
 *
 * @category   Models
 * @package    Src
 * @subpackage Models\User
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
     * @param array $data Optional initial data for the professor.
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
     * @return array Associative array containing the SAE records.
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
     * Creates a new SAE (subject) record linked to the professor.
     *
     * Dates are converted using STR_TO_DATE according to the expected format.
     *
     * @param PDO     $connection PDO object representing the database connection.
     * @param integer $clientid   ID of the client linked to the SAE.
     * @param string  $name       Name of the SAE.
     * @param string  $begindate  Start date (expected format: e.g., "March 01 2024").
     * @param string  $enddate    End date (expected format: e.g., "June 30 2024").
     * @param integer $userId     ID of the responsible professor.
     *
     * @return void
     *
     * @throws \PDOException If an error occurs during query execution.
     */
    protected function createSAE(
        PDO $connection,
        int $clientid,
        string $name,
        string $begindate,
        string $enddate,
        int $userId
    ): void {
        $stmt = $connection->prepare(
            'INSERT INTO sae_subjects
                (responsible_prof_id, client_id, subject_name, begin_date, end_date) VALUES
            (:responsible_prof_id, :client_id, :subject_name, STR_TO_DATE(:begin_date, "%M %d %Y"),
            STR_TO_DATE(:end_date, "%M %d %Y"));'
        );
        $stmt->execute(
            [
                'responsible_prof_id' => $userId,
                'client_id' => $clientid,
                'subject_name' => $name,
                'begin_date' => $begindate,
                'end_date' => $enddate
            ]
        );
    }

    /**
     * Updates an existing SAE with the provided data.
     *
     * If a value in the \$data array is NULL, the current value of the SAE is kept.
     *
     * @param PDO             $connection PDO object representing the database connection.
     * @param \Models\SAE\SAE $sae        SAE instance representing the current record.
     * @param array           $data       Associative array of fields to update.
     *                                    Expected keys: 'responsible_prof_id',
     *                                    'client_id', 'subject_name',
     *                                    'begin_date', 'end_date'.
     *
     * @return void
     *
     * @throws \PDOException If an error occurs during query execution.
     */
    protected function updateSAE(PDO $connection, SAE $sae, array $data): void
    {
        $saedata = $sae->getDataArray();
        for ($i = 0; $i < count($data); $i++) {
            if ($data[$i] == null) {
                $data[$i] = $saedata[$i];
            }
        }
        $stmt = $connection->prepare(
            'UPDATE sae_subjects
                                    SET sae_subject_id = sae_subject_id,
                                        responsible_prof_id = :responsible_prof_id, client_id = :client_id,
                                        subject_name = :subject_name, begin_date = :begin_date, end_date = :end_date
                                    WHERE sae_subject_id = :sae_subject_id;'
        );
        $stmt->execute(
            [
                'sae_subject_id' => $sae->getSaeSubjectId(),
                'responsible_prof_id' => $data['responsible_prof_id'],
                'client_id' => $data['client_id'],
                'subject_name' => $data['subject_name'],
                'begin_date' => $data['begin_date'],
                'end_date' => $data['end_date']
            ]
        );
    }

    /**
     * Creates an SAE group for a given subject and returns its ID.
     *
     * First inserts a record into `SAE_groups`, then selects an
     * unassigned group for students for the same SAE.
     *
     * @param PDO     $connection PDO object representing the database connection.
     * @param integer $saeID      SAE subject ID.
     *
     * @return integer The ID of the created group (sae_group_id).
     *
     * @throws \PDOException If an error occurs during query execution.
     */
    protected function createGroup(PDO $connection, int $saeID): int
    {
        $stmt = $connection->prepare(
            'INSERT INTO SAE_groups(sae_subject_id)
                                    VALUES (:sae_subject_id);'
        );
        $stmt->execute(
            [
                'sae_subject_id' => $saeID
            ]
        );
        $stmt = $connection->prepare(
            'SELECT sae_group_id FROM SAE_groups
                                    WHERE sae_group_id NOT IN (SELECT sae_group_id FROM students)
                                    AND sae_subject_id = :sae_subject_id
                                    LIMIT 1;'
        );
        $stmt->execute(
            [
                'sae_subject_id' => $saeID
            ]
        );
        return (int)$stmt->fetchColumn(0);
    }

    /**
     * Assigns a student to an SAE group.
     *
     * @param PDO     $connection PDO object representing the database connection.
     * @param integer $groupId    SAE group ID.
     * @param integer $student    Student ID (student_id).
     *
     * @return void
     *
     * @throws \PDOException If an error occurs during query execution.
     */
    protected function assignedSAE(PDO $connection, int $groupId, int $student): void
    {
        $stmt = $connection->prepare(
            'UPDATE students
                                    SET sae_group_id = :sae_group_id
                                    WHERE student_id = :student_id;'
        );
        $stmt->execute(
            [
                'sae_group_id' => $groupId,
                'student_id' => $student
            ]
        );
    }

    /**
     * Removes a professor's association with an SAE.
     *
     * Deletes the corresponding row in `sae_professor_groups`.
     *
     * @param PDO     $connection PDO object representing the database connection.
     * @param integer $saeID      SAE subject ID.
     * @param integer $profID     Professor ID.
     *
     * @return void
     *
     * @throws \PDOException If an error occurs during query execution.
     */
    protected function removeProfFromSae(PDO $connection, int $saeID, int $profID): void
    {
        $stmt = $connection->prepare(
            'DELETE FROM sae_professor_groups
                                    WHERE sae_subject_id = :sae_subject_id
                                    AND professor_id = :professor_id;'
        );
        $stmt->execute(
            [
                'sae_subject_id' => $saeID,
                'professor_id' => $profID
            ]
        );
    }

    /**
     * Adds a professor's association with an SAE.
     *
     * @param PDO     $connection PDO object representing the database connection.
     * @param integer $saeID      SAE subject ID.
     * @param integer $profID     Professor ID.
     *
     * @return void
     *
     * @throws \PDOException If an error occurs during query execution.
     */
    protected function addProfToSae(PDO $connection, int $saeID, int $profID): void
    {
        $stmt = $connection->prepare(
            'INSERT INTO sae_professor_groups(sae_subject_id, professor_id)
                                    VALUES (:sae_subject_id, :professor_id);'
        );
        $stmt->execute(
            [
                'sae_subject_id' => $saeID,
                'professor_id' => $profID
            ]
        );
    }

    /**
     * Unassigns a student from an SAE (removes the student from the group).
     *
     * @param PDO             $connection PDO object representing the database connection.
     * @param \Models\SAE\SAE $sae        SAE instance used to retrieve the id.
     * @param integer         $student    ID of the student to unassign.
     *
     * @return void
     *
     * @throws \PDOException If an error occurs during query execution.
     */
    protected function unassignedSAE(PDO $connection, SAE $sae, int $student): void
    {
        $stmt = $connection->prepare(
            'UPDATE students
                                    SET sae_group_id = :sae_subject_id
                                    WHERE student_id = :student_id;'
        );
        $stmt->execute(
            [
                'sae_group_id' => $sae->getSaeSubjectId(),
                'student_id' => null
            ]
        );
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
