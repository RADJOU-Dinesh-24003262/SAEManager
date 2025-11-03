<?php

namespace Models\User;

use PDO;

/**
 * Represents a professor user in the system.
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
     * The AMU identification string.
     *
     * @var string
     */
    protected string $amu_id = '';

    /**
     * Initializes a new professor.
     *
     * @param array $data The professor data.
     */
    public function __construct(array $data = [])
    {
        $this->user_type = 'professor';
        parent::__construct($data);
    }

    /**
     * Saves professor-specific data to the database.
     *
     * @param PDO     $connection The database connection.
     * @param integer $userId     The user ID from the users table.
     *
     * @return void
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
     * Fetches professor-specific data from the database.
     *
     * @param PDO    $db    The database connection.
     * @param string $email The user\'s email.
     *
     * @return void
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

     protected function fetchSAEData(PDO $connection, int $userId): Array
    {
        $stmt = $connection->prepare('SELECT * FROM SAE_subjects 
                                            JOIN sae_professor_groups on SAE_subjects.sae_subject_id = sae_professor_groups.sae_subject_id 
                                            JOIN professors ON sae_professor_groups.professor_id = professors.professor_id 
                                            WHERE professors.professor_id = :professor_id');
        $stmt->execute(['professor_id' => $userId]);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $data;
    }

    protected function createSAE(PDO $connection, int $clientid, string $name, string $begindate, string $enddate, int $userId):void {
         $stmt = $connection->prepare(
            'INSERT INTO sae_subjects(responsible_prof_id, client_id, subject_name, begin_date, end_date)
            VALUES
            (:responsible_prof_id, :client_id, :subject_name, STR_TO_DATE(:begin_date, "%M %d %Y"), STR_TO_DATE(:end_date, "%M %d %Y"));'
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

    protected function updateSAE(PDO $connection, \SAE $sae, Array $data):void {
        $saedata = $sae->getDataArray();
        for($i=0;$i<count($data);$i++){
            if($data[$i] == Null){
                $data[$i] = $saedata[$i];
            }
        }
        $stmt = $connection->prepare('UPDATE sae_subjects 
                                    SET sae_subject_id = sae_subject_id, responsible_prof_id = :responsible_prof_id, client_id = :client_id,
                                        subject_name = :subject_name, begin_date = :begin_date, end_date = :end_date
                                    WHERE sae_subject_id = :sae_subject_id;');
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
    protected function createGroup (PDO $connection, int $saeID):int {
        $stmt = $connection->prepare('INSERT INTO SAE_groups(sae_subject_id)
                                    VALUES (:sae_subject_id);');
        $stmt->execute(
            [
                'sae_subject_id' => $saeID
            ]
        );
        $stmt = $connection->prepare('SELECT sae_group_id FROM SAE_groups
                                    WHERE sae_group_id NOT IN (SELECT sae_group_id FROM students)
                                    AND sae_subject_id = :sae_subject_id
                                    LIMIT 1;');
        $stmt->execute(
            [
                'sae_subject_id' => $saeID
            ]
        );
        return (int)$stmt->fetchColumn(0);
    }
    protected function assignedSAE(PDO $connection, int $groupId, int $student):void{
        $stmt = $connection->prepare('UPDATE students 
                                    SET sae_group_id = :sae_group_id
                                    WHERE student_id = :student_id;');
        $stmt->execute(
            [
                'sae_group_id' => $groupId,
                'student_id' => $student
            ]
            );
    }

    protected function removeProfFromSae(PDO $connection, int $saeID, int $profID):void{
        $stmt = $connection->prepare('DELETE FROM sae_professor_groups 
                                    WHERE sae_subject_id = :sae_subject_id
                                    AND professor_id = :professor_id;');
        $stmt->execute(
            [
                'sae_subject_id' => $saeID,
                'professor_id' => $profID
            ]
        );
    }

    protected function addProfToSae(PDO $connection, int $saeID, int $profID):void{
        $stmt = $connection->prepare('INSERT INTO sae_professor_groups(sae_subject_id, professor_id)
                                    VALUES (:sae_subject_id, :professor_id);');
        $stmt->execute(
            [
                'sae_subject_id' => $saeID,
                'professor_id' => $profID
            ]
        );
    }

    protected function unassignedSAE(PDO $connection, \SAE $sae, int $student):void{
        $stmt = $connection->prepare('UPDATE students 
                                    SET sae_group_id = :sae_subject_id
                                    WHERE student_id = :student_id;');
        $stmt->execute(
            [
                'sae_group_id' => $sae->getSaeSubjectId(),
                'student_id' => Null
            ]
        );
    }

    // -----------------
    // Getters
    // -----------------

    /**
     * Gets the professor\'s AMU ID.
     *
     * @return string
     */
    protected function getAmuId(): string
    {
        return $this->amu_id;
    }
}
