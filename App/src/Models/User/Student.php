<?php

namespace Models\User;

use Core\includes\exception\ExceptionBD\ExceptionFetchDataBD;
use Override;
use PDO;
use Core\includes\Database;
use PDOException;

/**
 * Represents a student user in the system.
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
class Student extends User
{
    /**
     * The AMU identification string.
     *
     * @var string
     */
    protected string $amu_id = '';

    /**
     * The year of study of the student.
     *
     * @var integer
     */
    protected int $year;

    /**
     * The major/parcours of the student.
     *
     * @var string|null
     */
    protected ?string $major = '';

    /**
     * The TD group of the student.
     *
     * @var string
     */
    protected string $td = '';

    /**
     * The TP group of the student.
     *
     * @var string
     */
    protected string $tp = '';

    /**
     * The student ID.
     *
     * @var integer
     */
    protected int $student_id;


    /**
     * Initializes a new student.
     *
     * @param array<string, string|integer|null> $data The student data.
     */
    public function __construct(array $data = [])
    {
        $this->user_type = 'student';
        parent::__construct($data);
    }

    /**
     * Saves student-specific data to the database.
     *
     * @param PDO     $connection The database connection.
     * @param integer $userId     The user ID from the users table.
     *
     * @return void
     */
    #[Override]
    protected function saveSpecificData(PDO $connection, int $userId): void
    {
        $stmt = $connection->prepare(
            'INSERT INTO students (student_id, amu_id, year, td, tp)
             VALUES (:student_id, :amu_id, :year, :td, :tp)'
        );

        $stmt->execute(
            [
                'student_id' => $userId,
                'amu_id' => $this->amu_id,
                'year' => $this->year,
                'td' => $this->td,
                'tp' => $this->tp,
            ]
        );
    }

    /**
     * Fetches student-specific data from the database.
     *
     * @param PDO    $db    The database connection.
     * @param string $email The user ID.
     *
     * @return void
     * @throws ExceptionFetchDataBD If the data can't be fetch.
     */
    #[Override]
    protected function fetchSpecificData(PDO $db, string $email): void
    {
        $stmt = $db->prepare(
            'SELECT s.*
             FROM students s
             JOIN users u ON s.student_id = u.user_id
             WHERE u.email = :email'
        );

        $stmt->execute(['email' => $email]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        if ($data) {
            foreach ($data as $key => $value) {
                if (property_exists($this, $key)) {
                    $this->$key = $value;
                }
            }
        } else {
            throw new ExceptionFetchDataBD();
        }
    }

    /**
     * Fetches the SAE subjects and group data for the student.
     *
     * @param PDO     $connection The database connection.
     * @param integer $userId     The student's user ID.
     *
     * @return array<int, array{
     *   sae_subject_id: int,
     *   responsible_prof_id: int,
     *   client_id: int,
     *   subject_name: string,
     *   begin_date: string,
     *   end_date: string,
     *   file_path: string|null
     * }> An array of SAE data (subject and group information).
     */
    #[Override]
    protected function fetchSAEData(PDO $connection, int $userId): array
    {
        $stmt = $connection->prepare(
            'SELECT * FROM sae_subjects
             JOIN sae_groups ON sae_subjects.sae_subject_id = sae_groups.sae_subject_id
             JOIN participated_in pi ON sae_groups.sae_group_id = pi.sae_group_id
             WHERE pi.student_id = :user_id'
        );
        $stmt->execute(['user_id' => $userId]);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $data;
    }


    /**
     * A student can access an SAE if they are part of a group for that SAE.
     *
     * @param integer $saeId The SAE ID.
     * @return boolean True if accessible, false otherwise.
     */
    #[Override]
    public function canAccessSAE(int $saeId): bool
    {
        try {
            $db = Database::getInstance();
            $stmt = $db->prepare(
                'SELECT COUNT(*) FROM participated_in pi
                 JOIN sae_groups sg ON pi.sae_group_id = sg.sae_group_id
                 WHERE pi.student_id = :student_id AND sg.sae_subject_id = :sae_id'
            );
            $stmt->execute(['student_id' => $this->user_id, 'sae_id' => $saeId]);
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log('Erreur canAccessSAE (Student) : ' . $e->getMessage());
            return false;
        }
    }

    /**
     * A student can modify a to-do if it belongs to their group.
     *
     * @param integer $todoId The to-do ID.
     * @return boolean True if modifiable, false otherwise.
     */
    #[Override]
    public function canModifyTodo(int $todoId): bool
    {
        try {
            $db = Database::getInstance();
            $stmt = $db->prepare(
                'SELECT COUNT(*) FROM sae_todolists todo
                 JOIN participated_in pi ON todo.sae_group_id = pi.sae_group_id
                 WHERE todo.todoid = :todo_id AND pi.student_id = :student_id'
            );
            $stmt->execute(['todo_id' => $todoId, 'student_id' => $this->user_id]);
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log('Erreur canModifyTodo (Student) : ' . $e->getMessage());
            return false;
        }
    }

    /**
     * A student can only see the members of THEIR own group.
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
    #[Override]
    public function getAccessibleGroupMembers(int $saeId): array
    {
        try {
            $db = Database::getInstance();
            $stmt = $db->prepare(
                'SELECT u.user_id, u.first_name, u.last_name, u.email, u.phone,
                        st.td, st.tp, pi.sae_group_id
                 FROM participated_in pi_self
                 JOIN participated_in pi ON pi.sae_group_id = pi_self.sae_group_id
                 JOIN students st ON st.student_id = pi.student_id
                 JOIN users u ON st.student_id = u.user_id
                 JOIN sae_groups sg ON pi.sae_group_id = sg.sae_group_id
                 WHERE pi_self.student_id = :user_id AND sg.sae_subject_id = :sae_id
                 ORDER BY u.last_name, u.first_name'
            );
            $stmt->execute(['user_id' => $this->user_id, 'sae_id' => $saeId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Erreur getAccessibleGroupMembers (Student) : ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Checks if the student can manage the SAE.
     *
     * @param integer|null $saeId The SAE ID, null if he want to create a SAE.
     * @return boolean Always false for student.
     */
    #[Override]
    public function canManageSAE(?int $saeId = null): bool
    {
        return false;
    }

    // -----------------
    // Getters
    // -----------------

    /**
     * Gets the student's AMU ID.
     *
     * @return string
     */
    public function getAmuId(): string
    {
        return $this->amu_id;
    }

    /**
     * Gets the student's year of study.
     *
     * @return integer
     */
    public function getYear(): int
    {
        return $this->year;
    }

    /**
     * Gets the student's parcours (major).
     *
     * @return string|null
     */
    public function getParcours(): ?string
    {
        return $this->major;
    }

    /**
     * Gets the student's TD group.
     *
     * @return string
     */
    public function getTd(): string
    {
        return $this->td;
    }

    /**
     * Gets the student's TP group.
     *
     * @return string
     */
    public function getTp(): string
    {
        return $this->tp;
    }

    /**
     * Get the student id in the database of the student
     * @return integer
     */
    public function getStudentId(): int
    {
        return $this->student_id;
    }

    // ... (previous code of Student.php) ...

    /**
     * The SAE Subject ID.
     * Added to allow the repository to inject the subject ID directly into the student object.
     *
     * @var integer|null
     */
    protected ?int $sae_subject_id = null;

    /**
     * Gets the SAE Subject ID.
     *
     * @return integer|null
     */
    public function getSaeSubjectId(): ?int
    {
        return $this->sae_subject_id;
    }

    /**
     * Sets the SAE Subject ID.
     *
     * @param integer $sae_subject_id The SAE Subject ID.
     * @return void
     */
    public function setSaeSubjectId(int $sae_subject_id): void
    {
        $this->sae_subject_id = $sae_subject_id;
    }
}
