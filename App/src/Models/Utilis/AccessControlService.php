<?php

namespace Models\Utilis;

use Models\User\User;
use Models\User\Student;
use Models\User\Professor;
use Models\User\Client;
use Core\includes\Database;
use PDO;

/**
 * Service to manage access control and permissions.
 *
 * Implements business rules:
 * - Students/Clients: can only access their group members
 * - Responsible professors: can access all groups of their SAE
 * - Assigned professors: can access only their assigned groups
 * - Professors: can create/modify SAE
 * - Students: can modify their to-do lists
 *
 * @category   Service
 * @package    Core
 * @subpackage Utilis
 * @author     SAE Manager Team
 * @license    MIT License https://opensource.org/licenses/MIT
 */
class AccessControlService
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Checks if a user can access a specific SAE
     *
     * @param User    $user  The user requesting access
     * @param integer $saeId The SAE subject ID
     * @return boolean
     */
    public function canAccessSAE(User $user, int $saeId): bool
    {
        if ($user->isProfessor()) {
            return $this->canProfessorAccessSAE($user, $saeId);
        }

        if ($user->isStudent()) {
            return $this->canStudentAccessSAE($user, $saeId);
        }

        if ($user->isClient()) {
            return $this->canClientAccessSAE($user, $saeId);
        }

        return false;
    }

    /**
     * Checks if a professor can access a SAE
     *
     * @param User    $professor The professor
     * @param integer $saeId     The SAE subject ID
     * @return boolean
     */
    private function canProfessorAccessSAE(User $professor, int $saeId): bool
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM sae_subjects s
             WHERE s.sae_subject_id = :sae_id
             AND (s.responsible_prof_id = :prof_id
                  OR EXISTS (
                      SELECT 1 FROM sae_professor_groups spg
                      WHERE spg.sae_subject_id = :sae_id
                      AND spg.professor_id = :prof_id
                  )
             )'
        );
        $stmt->execute(['sae_id' => $saeId, 'prof_id' => $professor->getUserId()]);
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Checks if a student can access a SAE
     *
     * @param User    $student The student
     * @param integer $saeId   The SAE subject ID
     * @return boolean
     */
    private function canStudentAccessSAE(User $student, int $saeId): bool
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM students st
             JOIN sae_groups sg ON st.sae_group_id = sg.sae_group_id
             WHERE st.student_id = :student_id
             AND sg.sae_subject_id = :sae_id'
        );
        $stmt->execute(['student_id' => $student->getUserId(), 'sae_id' => $saeId]);
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Checks if a client can access a SAE
     *
     * @param User    $client The client
     * @param integer $saeId  The SAE subject ID
     * @return boolean
     */
    private function canClientAccessSAE(User $client, int $saeId): bool
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM sae_subjects
             WHERE sae_subject_id = :sae_id
             AND client_id = :client_id'
        );
        $stmt->execute(['sae_id' => $saeId, 'client_id' => $client->getUserId()]);
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Checks if a user is the responsible professor of a SAE
     *
     * @param User    $user  The user
     * @param integer $saeId The SAE subject ID
     * @return boolean
     */
    public function isResponsibleProfessor(User $user, int $saeId): bool
    {
        if (!$user->isProfessor()) {
            return false;
        }

        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM sae_subjects
             WHERE sae_subject_id = :sae_id
             AND responsible_prof_id = :prof_id'
        );
        $stmt->execute(['sae_id' => $saeId, 'prof_id' => $user->getUserId()]);
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Gets all group members accessible by a user
     *
     * @param User    $user  The user requesting access
     * @param integer $saeId The SAE subject ID
     * @return array Array of user data with contact info
     */
    public function getAccessibleGroupMembers(User $user, int $saeId): array
    {
        if ($user->isProfessor() && $this->isResponsibleProfessor($user, $saeId)) {
            // Responsible professor: access to ALL groups
            return $this->getAllSAEMembers($saeId);
        }

        if ($user->isProfessor()) {
            // Assigned professor: access to assigned groups only
            return $this->getAssignedGroupMembers($user, $saeId);
        }

        if ($user->isStudent() || $user->isClient()) {
            // Student/Client: access to their group only
            return $this->getOwnGroupMembers($user, $saeId);
        }

        return [];
    }

    /**
     * Gets all members of a SAE (for responsible professor)
     *
     * @param integer $saeId The SAE subject ID
     * @return array
     */
    private function getAllSAEMembers(int $saeId): array
    {
        $stmt = $this->db->prepare(
            'SELECT DISTINCT u.user_id, u.first_name, u.last_name, u.email, u.phone, u.user_type,
                    st.sae_group_id, st.td, st.tp
             FROM sae_groups sg
             JOIN students st ON sg.sae_group_id = st.sae_group_id
             JOIN users u ON st.student_id = u.user_id
             WHERE sg.sae_subject_id = :sae_id
             ORDER BY st.sae_group_id, u.last_name, u.first_name'
        );
        $stmt->execute(['sae_id' => $saeId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Gets members of groups assigned to a professor
     *
     * @param User    $professor The professor
     * @param integer $saeId     The SAE subject ID
     * @return array
     */
    private function getAssignedGroupMembers(User $professor, int $saeId): array
    {
        $stmt = $this->db->prepare(
            'SELECT DISTINCT u.user_id, u.first_name, u.last_name, u.email, u.phone, u.user_type,
                    st.sae_group_id, st.td, st.tp
             FROM sae_professor_groups spg
             JOIN sae_groups sg ON spg.sae_subject_id = sg.sae_subject_id
             JOIN students st ON sg.sae_group_id = st.sae_group_id
             JOIN users u ON st.student_id = u.user_id
             WHERE spg.professor_id = :prof_id
             AND spg.sae_subject_id = :sae_id
             ORDER BY st.sae_group_id, u.last_name, u.first_name'
        );
        $stmt->execute(['prof_id' => $professor->getUserId(), 'sae_id' => $saeId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Gets members of the user's own group
     *
     * @param User    $user  The user (student or client)
     * @param integer $saeId The SAE subject ID
     * @return array
     */
    private function getOwnGroupMembers(User $user, int $saeId): array
    {
        if ($user->isStudent()) {
            $stmt = $this->db->prepare(
                'SELECT u.user_id, u.first_name, u.last_name, u.email, u.phone, u.user_type,
                        st.sae_group_id, st.td, st.tp
                 FROM students st_self
                 JOIN students st ON st.sae_group_id = st_self.sae_group_id
                 JOIN users u ON st.student_id = u.user_id
                 WHERE st_self.student_id = :user_id
                 ORDER BY u.last_name, u.first_name'
            );
            $stmt->execute(['user_id' => $user->getUserId()]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        // For clients, return students working on their SAE
        $stmt = $this->db->prepare(
            'SELECT DISTINCT u.user_id, u.first_name, u.last_name, u.email, u.phone, u.user_type,
                    st.sae_group_id, st.td, st.tp
             FROM sae_subjects s
             JOIN sae_groups sg ON s.sae_subject_id = sg.sae_subject_id
             JOIN students st ON sg.sae_group_id = st.sae_group_id
             JOIN users u ON st.student_id = u.user_id
             WHERE s.client_id = :client_id
             AND s.sae_subject_id = :sae_id
             ORDER BY st.sae_group_id, u.last_name, u.first_name'
        );
        $stmt->execute(['client_id' => $user->getUserId(), 'sae_id' => $saeId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Checks if a user can modify a to-do list
     *
     * @param User    $user   The user
     * @param integer $todoId The to-do item ID
     * @return boolean
     */
    public function canModifyTodo(User $user, int $todoId): bool
    {
        // Only students can modify to-do lists
        if (!$user->isStudent()) {
            return false;
        }

        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM sae_todolists todo
             JOIN students st ON todo.sae_group_id = st.sae_group_id
             WHERE todo.todoid = :todo_id
             AND st.student_id = :student_id'
        );
        $stmt->execute(['todo_id' => $todoId, 'student_id' => $user->getUserId()]);
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Checks if a user can view a to-do list
     *
     * @param User    $user    The user
     * @param integer $groupId The group ID
     * @return boolean
     */
    public function canViewTodoList(User $user, int $groupId): bool
    {
        // Everyone with access to the SAE can view to-do lists
        $stmt = $this->db->prepare(
            'SELECT sae_subject_id FROM sae_groups WHERE sae_group_id = :group_id'
        );
        $stmt->execute(['group_id' => $groupId]);
        $saeId = $stmt->fetchColumn();

        if (!$saeId) {
            return false;
        }

        return $this->canAccessSAE($user, intval($saeId));
    }

    /**
     * Checks if a user can create/modify a SAE
     *
     * @param User         $user  The user
     * @param integer|null $saeId Optional SAE ID for modification
     * @return boolean
     */
    public function canManageSAE(User $user, ?int $saeId = null): bool
    {
        // Only professors can manage SAE
        if (!$user->isProfessor()) {
            return false;
        }

        // For creation, all professors can create
        if ($saeId === null) {
            return true;
        }

        // For modification, only responsible professor can modify
        return $this->isResponsibleProfessor($user, $saeId);
    }
}
