<?php

namespace Models\Repository\User;

use Models\Entity\User\Student;
use Models\Entity\User\User;
use Models\UseCase\User\InterfaceDB\StudentInterface;
use Override;
use PDO;
use PDOException;

/**
 * PDO implementation of StudentInterface.
 *
 * This is the Infrastructure layer implementation of the Interface.
 *
 * @category   Models
 * @package    Src
 * @subpackage Models/Repository/User
 * @author     Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license    MIT License https://opensource.org/licenses/MIT
 * @link       https://github.com/RADJOU-Dinesh-24003262/SAEManager
 *
 * @extends PdoUserRepository
 */
class PdoStudentRepository extends PdoUserRepository implements StudentInterface
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->entityClass = Student::class;
    }

    /**
     * Finds a student by ID.
     *
     * @param integer $id The student ID.
     * @return Student|null The student entity or null if not found.
     */
    #[Override]
    public function findById(int $id): ?Student
    {
        try {
            $stmt = $this->connection->prepare(
                'SELECT * FROM users
                 JOIN students ON users.user_id = students.student_id
                 WHERE users.user_id = :id'
            );
            $stmt->execute(['id' => $id]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();

            if (!$data) {
                return null;
            }

            return new Student($data);
        } catch (PDOException $e) {
            return null;
        }
    }

    /**
     * Finds a student by email.
     *
     * @param string $email The student's email.
     * @return Student|null The student entity or null if not found.
     */
    public function findByEmail(string $email): ?Student
    {
        try {
            $stmt = $this->connection->prepare(
                'SELECT * FROM users
                 JOIN students ON users.user_id = students.student_id
                 WHERE users.email = LOWER(:email)'
            );
            $stmt->execute(['email' => $email]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();

            if (!$data) {
                return null;
            }

            return new Student($data);
        } catch (PDOException $e) {
            error_log("Error in PdoStudentRepository::findByEmail: " . $e->getMessage());
            return null;
        }
    }

    #[Override]
    public function create($student): Student|bool
    {
        $userId = parent::createUser($student);
        $this->connection->beginTransaction();

        try {
            $stmt = $this->connection->prepare(
                'INSERT INTO students (student_id, td, tp, amu_id, major, year) 
                 VALUES (:student_id, :td, :tp, :amu_id, :major, :year)'
            );
            $stmt->execute([
                'student_id' => $userId,
                'td' => $student->getTd(),
                'tp' => $student->getTp(),
                'amu_id' => $student->getAmuId(),
                'major' => $student->getMajor(),
                'year' => $student->getYear(),
            ]);

            $stmt->closeCursor();

            $this->connection->commit();
            $student = $this->findById($userId);

            return $student;
        } catch (PDOException $e) {
            $this->connection->rollBack();
            error_log('Error creating student: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Finds students by TD group.
     *
     * @param string $td The TD group.
     * @return array<Student> Array of student entities.
     */
    #[Override]
    public function findByTdGroup(string $td): array
    {
        try {
            $stmt = $this->connection->prepare(
                'SELECT u.*, s.* 
                 FROM users u
                 JOIN students s ON u.user_id = s.student_id
                 WHERE s.td = :td
                 ORDER BY u.last_name, u.first_name'
            );
            $stmt->execute(['td' => $td]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return array_map(fn($data) => new Student($data), $results);
        } catch (PDOException $e) {
            error_log("Error in findByTdGroup: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Finds students by TP group.
     *
     * @param string $tp The TP group.
     * @return array<Student> Array of student entities.
     */
    #[Override]
    public function findByTpGroup(string $tp): array
    {
        try {
            $stmt = $this->connection->prepare(
                'SELECT u.*, s.* 
                 FROM users u
                 JOIN students s ON u.user_id = s.student_id
                 WHERE s.tp = :tp
                 ORDER BY u.last_name, u.first_name'
            );
            $stmt->execute(['tp' => $tp]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return array_map(fn($data) => new Student($data), $results);
        } catch (PDOException $e) {
            error_log("Error in findByTpGroup: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Checks if a student can access a SAE.
     *
     * @param integer $studentId The student ID.
     * @param integer $saeId     The SAE ID.
     * @return boolean True if accessible, false otherwise.
     */
    #[Override]
    public function canAccessSAE(int $studentId, int $saeId): bool
    {
        try {
            $stmt = $this->connection->prepare(
                'SELECT COUNT(*) FROM participated_in pi
                 JOIN sae_groups sg ON pi.sae_group_id = sg.sae_group_id
                 WHERE pi.student_id = :student_id AND sg.sae_subject_id = :sae_id'
            );
            $stmt->execute(['student_id' => $studentId, 'sae_id' => $saeId]);
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log('Error in canAccessSAE (Student): ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Checks if a student can modify a to-do.
     *
     * @param integer $studentId The student ID.
     * @param integer $todoId    The to-do ID.
     * @return boolean True if modifiable, false otherwise.
     */
    #[Override]
    public function canModifyTodo(int $studentId, int $todoId): bool
    {
        try {
            $stmt = $this->connection->prepare(
                'SELECT COUNT(*) FROM sae_todolists todo
                 JOIN participated_in pi ON todo.sae_group_id = pi.sae_group_id
                 WHERE todo.todoid = :todo_id AND pi.student_id = :student_id'
            );
            $stmt->execute(['todo_id' => $todoId, 'student_id' => $studentId]);
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log('Error in canModifyTodo (Student): ' . $e->getMessage());
            return false;
        }
    }
}
