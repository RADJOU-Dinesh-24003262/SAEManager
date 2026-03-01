<?php

namespace Models\Repository\User;

use Core\includes\Database;
use Core\Models\BaseModel;
use Models\Entity\User\Student;
use Models\Entity\User\User;
use Models\UseCase\User\InterfaceDB\StudentInterface;
use PDO;
use PDOException;

/**
 * PDO implementation of StudentInterface.
 *
 * [Architecture Strategy]
 * Type 2 Repository (Inherited/Polymorphic).
 * This repository DOES NOT extend BaseRepository because it handles logic that involves
 * joining with the parent `users` table instead of mapping perfectly to a single table.
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
 */
class PdoStudentRepository implements StudentInterface
{
    /**
     * The User repository for base user operations.
     *
     * @var PdoUserRepository
     */
    private PdoUserRepository $userRepository;

    /**
     * The PDO connection instance
     * @var PDO
     */
    protected PDO $connection;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->userRepository = new PdoUserRepository();
        $this->connection = Database::getInstance();
    }

    /**
     * Finds a student by ID.
     *
     * @param integer $id The student ID.
     * @return Student|null The student entity or null if not found.
     */
    /**
     * Finds a student by ID.
     *
     * @param integer $id The student ID.
     * @return Student|null The student entity or null if not found.
     */
    public function findById(int $id): ?Student
    {
        try {
            $stmt = $this->connection->prepare(
                'SELECT u.*, s.* 
                 FROM users u
                 JOIN students s ON u.user_id = s.student_id
                 WHERE u.user_id = :id'
            );
            $stmt->execute(['id' => $id]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);

            return $data ? new Student($data) : null;
        } catch (PDOException $e) {
            error_log("Error in findById (Student): " . $e->getMessage());
            return null;
        }
    }

    /**
     * Finds all students.
     *
     * @return array<Student> Array of student entities.
     */
    public function findAll(): array
    {
        try {
            $stmt = $this->connection->prepare(
                'SELECT u.*, s.* 
                 FROM users u
                 JOIN students s ON u.user_id = s.student_id'
            );
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return array_map(fn($data) => new Student($data), $results);
        } catch (PDOException $e) {
            error_log("Error in findAll (Student): " . $e->getMessage());
            return [];
        }
    }




    /**
     * Inserts a student into the database.
     *
     * @param BaseModel $student The student object to insert.
     * @return integer|boolean The id of the inserted student or false on failure.
     */
    public function insert(BaseModel $student): int|bool
    {

        if (!$student instanceof Student) {
            return false;
        }

        $userId = $this->userRepository->insert($student);
        if (!$userId) {
            return false;
        }

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


            return $userId;
        } catch (PDOException $e) {
            $this->connection->rollBack();
            error_log('Error creating student: ' . $e->getMessage());
            return false;
        }
    }


    /**
     * Checks if a student can access a SAE.
     *
     * @param integer $studentId The student ID.
     * @param integer $saeId     The SAE ID.
     * @return boolean True if accessible, false otherwise.
     */
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

    /**
     * Updates an existing student.
     *
     * @param BaseModel $student The student entity to update.
     * @return boolean True on success, false on failure.
     */
    public function update(BaseModel $student): bool
    {
        if (!$student instanceof Student) {
            return false;
        }

        if (!$this->userRepository->update($student)) {
            return false;
        }

        try {
            $stmt = $this->connection->prepare(
                'UPDATE students
                             SET td = :td,
                                 tp = :tp,
                                 amu_id = :amu_id,
                                 major = :major,
                                 year = :year
                             WHERE student_id = :id'
            );

            return $stmt->execute([
                'td' => $student->getTd(),
                'tp' => $student->getTp(),
                'amu_id' => $student->getAmuId(),
                'major' => $student->getMajor(),
                'year' => $student->getYear(),
                'id' => $student->getUserId()
            ]);
        } catch (PDOException $e) {
            error_log('Error updating student: ' . $e->getMessage());
            return false;
        }
    }



    /**
     * Deletes a student from the database.
     *
     * @param integer $id The ID of the student to delete.
     * @return boolean True on success, false on failure.
     * @throws PDOException If the deletion fails.
     */
    public function delete(int $id): bool
    {
        $this->connection->beginTransaction();
        try {
            $stmt = $this->connection->prepare("DELETE FROM students WHERE student_id = :id");
            $stmt->execute(['id' => $id]);

            if (!$this->userRepository->delete($id)) {
                throw new PDOException("Failed to delete user");
            }

            $this->connection->commit();
            return true;
        } catch (PDOException $e) {
            $this->connection->rollBack();
            return false;
        }
    }

    /**
     * Finds students who are not participating in a specific SAE.
     *
     * @param integer $saeId The SAE subject ID.
     * @return array<Student> Array of students not in the SAE.
     */
    public function findStudentsNotInSAE(int $saeId): array
    {
        try {
            $stmt = $this->connection->prepare(
                'SELECT u.*, s.*
                             FROM students s
                             JOIN users u ON s.student_id = u.user_id
                             WHERE s.student_id NOT IN (
                                 SELECT pi.student_id
                                 FROM participated_in pi
                                 JOIN sae_groups sg ON pi.sae_group_id = sg.sae_group_id
                                 WHERE sg.sae_subject_id = :sae_id
                             )
                             ORDER BY u.last_name, u.first_name'
            );
            $stmt->execute(['sae_id' => $saeId]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return array_map(fn($data) => new Student($data), $results);
        } catch (PDOException $e) {
            error_log("Error in findStudentsNotInSAE: " . $e->getMessage());
            return [];
        }
    }
}
