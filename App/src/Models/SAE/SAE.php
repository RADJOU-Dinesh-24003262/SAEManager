<?php

namespace Models\SAE;

use Models\User\User;
use Core\includes\exception\ExceptionBD\ExceptionFetchDataBD;
use Core\includes\exception\SAE\ExceptionAccessDenied;
use Core\includes\exception\SAE\ExceptionResourceNotFound;
use Models\SAE\Repository\SAESubjectRepository;
use Models\SAE\Repository\SAEGroupRepository;
use Models\User\Professor;

/**
 * Facade for SAE operations.
 *
 * This class implements the Facade design pattern to provide
 * a simplified interface for complex SAE operations involving
 * multiple repositories and models.
 *
 * It orchestrates:
 * - SAESubject operations
 * - SAEGroup management
 * - Competence handling
 * - Professor assignments
 * - Access control
 *
 * @category   Models
 * @package    Src
 * @subpackage Models/SAE
 * @author     Alexandre Benhafessa <alexandre.benhafessa@etu.univ-amu.fr>
 * @author     François Dargentolle <francois.dargentolle@etu.univ-amu.fr>
 * @author     William Edelstein <william.edelstein@etu.univ-amu.fr>
 * @author     Nathan Griguer <nathan.griguer@etu.univ-amu.fr>
 * @author     Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license    MIT License https://opensource.org/licenses/MIT
 * @link       https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class SAE
{
    /**
     * The subject repository.
     *
     * @var SAESubjectRepository
     */
    protected SAESubjectRepository $subjectRepo;

    /**
     * The group repository.
     *
     * @var SAEGroupRepository
     */
    protected SAEGroupRepository $groupRepo;

    /**
     * Singleton instance.
     *
     * @var SAE|null
     */
    protected static ?SAE $instance = null;

    /**
     * Constructor.
     */
    private function __construct()
    {
        $this->subjectRepo = SAESubjectRepository::getInstance();
        $this->groupRepo = SAEGroupRepository::getInstance();
    }

    /**
     * Gets the singleton instance.
     *
     * @return SAE
     */
    public static function getInstance(): SAE
    {
        if (self::$instance === null) {
            self::$instance = new SAE();
        }
        return self::$instance;
    }

    /**
     * Gets all SAEs accessible by a user.
     *
     * @param User $user The requesting user.
     * @return array<SAESubject> Array of SAE subjects.
     * @throws ExceptionFetchDataBD If data cannot be fetched.
     */
    public function getUserSAEs(User $user): array
    {
        if ($user->isProfessor()) {
            return $this->subjectRepo->findByProfessorId($user->getUserId());
        }

        if ($user->isStudent()) {
            return $this->subjectRepo->findByStudentId($user->getUserId());
        }

        if ($user->isClient()) {
            return $this->subjectRepo->findByClientId($user->getUserId());
        }

        return [];
    }

    /**
     * Gets complete SAE data with access control.
     *
     * @param integer $saeId The SAE subject ID.
     * @param User    $user  The requesting user.
     * @return array{
     * subject: SAESubject,
     * groups: array<int, array{
     * group: SAEGroup,
     * students: array<int, array{
     * student_id: string,
     * amu_id: string,
     * year: string,
     * major: string,
     * td: string,
     * tp: string,
     * first_name: string,
     * last_name: string,
     * email: string,
     * phone: string|null
     * }>
     * }>,
     * responsible_professor: array{
     * user_id: string,
     * first_name: string,
     * last_name: string,
     * email: string,
     * phone: string|null,
     * amu_id: string
     * }|null,
     * all_professors: array<int, array{
     * user_id: string,
     * first_name: string,
     * last_name: string,
     * email: string,
     * phone: string|null,
     * amu_id: string,
     * is_responsible: int
     * }>,
     * client: array{
     * user_id: string,
     * first_name: string,
     * last_name: string,
     * email: string,
     * phone: string|null,
     * organisation: string
     * }|null
     * }|null Complete SAE data or null if no access.
     * @throws ExceptionFetchDataBD If data cannot be fetched.
     */
    public function getCompleteSAEData(int $saeId, User $user): ?array
    {

        $subject = $this->subjectRepo->findById($saeId);
        if (!$subject) {
            return null;
        }

        // Get groups accessible by the user.
        $groups = $this->getAccessibleGroups($saeId, $user);

        // Get professors info.
        $responsibleProf = $this->subjectRepo->getResponsibleProfessor($saeId);
        $allProfs = $this->subjectRepo->getAllProfessorsInfo($saeId);

        // Get client info.
        $client = $this->subjectRepo->getClientInfo($saeId);

        return [
            'subject' => $subject,
            'groups' => $groups,
            'responsible_professor' => $responsibleProf,
            'all_professors' => $allProfs,
            'client' => $client
        ];
    }

    /**
     * Gets groups accessible by a user for a SAE.
     *
     * @param integer $saeId The SAE subject ID.
     * @param User    $user  The requesting user.
     * @return array<int, array{
     * group: SAEGroup,
     * students: array<int, array{
     * student_id: string,
     * amu_id: string,
     * year: string,
     * major: string,
     * td: string,
     * tp: string,
     * first_name: string,
     * last_name: string,
     * email: string,
     * phone: string|null
     * }>
     * }> Array of groups with member's details.
     */
    private function getAccessibleGroups(int $saeId, User $user): array
    {
        $allGroups = $this->groupRepo->findBySaeId($saeId);

        // If responsible professor, return all groups.
        if ($user instanceof Professor && $user->isResponsibleProfessor($saeId)) {
            return array_map(function ($group) {
                return [
                    'group' => $group,
                    'students' => $this->groupRepo->getGroupStudents(intval($group->getSaeGroupId())),
                ];
            }, $allGroups);
        }

        // If assigned professor (managing specific groups), return only managed groups.
        if ($user->isProfessor()) {
            $managedGroups = $this->groupRepo->findByProfessorId($user->getUserId(), $saeId);

            return array_map(function ($group) {
                return [
                    'group' => $group,
                    'students' => $this->groupRepo->getGroupStudents(intval($group->getSaeGroupId())),
                ];
            }, $managedGroups);
        }

        // If student or client, return only their group.
        if ($user->isStudent()) {
            $userGroupId = $this->getUserGroupId($user, $saeId);
            if ($userGroupId) {
                $group = $this->groupRepo->findById($userGroupId);
                if ($group) {
                    return [[
                        'group' => $group,
                        'students' => $this->groupRepo->getGroupStudents($userGroupId),
                    ]];
                }
            }
        }

        if ($user->isClient()) {
            // Clients see all groups.
            return array_map(function ($group) {
                return [
                    'group' => $group,
                    'students' => $this->groupRepo->getGroupStudents(intval($group->getSaeGroupId())),
                ];
            }, $allGroups);
        }

        return [];
    }

    /**
     * Gets the group ID of a student in a SAE.
     *
     * @param User    $student The student.
     * @param integer $saeId   The SAE subject ID.
     * @return integer|null The group ID or null if not assigned.
     */
    private function getUserGroupId(User $student, int $saeId): ?int
    {
        return $this->groupRepo->getStudentGroupId($student->getUserId(), $saeId);
    }

    /**
     * Creates a new SAE.
     *
     * @param User                 $creator The professor creating the SAE.
     * @param array<string, mixed> $data    SAE data.
     * @return SAESubject The created SAE.
     * @throws ExceptionAccessDenied If user doesn't have permission.
     */
    public function createSAE(User $creator, array $data): SAESubject
    {
        // Check permission.
        if (!$creator->canManageSAE()) {
            throw new ExceptionAccessDenied("Vous n'avez pas la permission de créer une SAE");
        }

        // Create SAE subject.
        $subject = new SAESubject($data);

        $subject = $this->subjectRepo->create($subject);

        return $subject;
    }

    /**
     * Updates a SAE.
     *
     * @param User                 $user  The user updating the SAE.
     * @param integer              $saeId The SAE ID.
     * @param array<string, mixed> $data  Updated data.
     * @return boolean Success status.
     * @throws ExceptionAccessDenied    If user doesn't have permission.
     * @throws ExceptionResourceNotFound If SAE is not found.
     */
    public function updateSAE(User $user, int $saeId, array $data): bool
    {
        if (!$user->canManageSAE($saeId)) {
            throw new ExceptionAccessDenied("Vous n'avez pas la permission de modifier cette SAE");
        }

        $subject = $this->subjectRepo->findById($saeId);
        if (!$subject) {
            throw new ExceptionResourceNotFound("SAE non trouvée");
        }

        foreach ($data as $key => $value) {
            $camelKey = str_replace('_', '', ucwords($key, '_'));
            $setter = 'set' . $camelKey;

            if (method_exists($subject, $setter)) {
                $subject->$setter($value);
            }
        }
        return $this->subjectRepo->update($subject);
    }

    /**
     * Creates a new group for a SAE.
     *
     * @param User         $user        The requesting user.
     * @param integer      $saeId       The SAE subject ID.
     * @param integer|null $professorId The professor ID managing the group.
     * @return SAEGroup The created group.
     * @throws ExceptionAccessDenied If user doesn't have permission.
     */
    public function createGroup(User $user, int $saeId, ?int $professorId): SAEGroup
    {
        if (!$user->canManageSAE($saeId)) {
            throw new ExceptionAccessDenied("Vous n'avez pas la permission de créer un groupe");
        }

        $group = new SAEGroup([
            'sae_subject_id' => $saeId,
            'professor_id' => $professorId
        ]);
        return $this->groupRepo->create($group);
    }

    /**
     * Assigns a student to a group.
     *
     * @param User    $professor The professor.
     * @param integer $studentId The student ID.
     * @param integer $groupId   The group ID.
     * @return boolean Success status.
     * @throws ExceptionResourceNotFound If group is not found.
     * @throws ExceptionAccessDenied     If professor doesn't have permission.
     */
    public function assignStudentToGroup(User $professor, int $studentId, int $groupId): bool
    {
        $group = $this->groupRepo->findById($groupId);
        if (!$group) {
            throw new ExceptionResourceNotFound("Groupe non trouvé");
        }

        if (!$professor->canManageSAE($group->getSaeSubjectId())) {
            throw new ExceptionAccessDenied("Vous n'avez pas la permission d'assigner des étudiants");
        }

        return $this->groupRepo->assignStudent($studentId, $groupId);
    }

    /**
     * Assigns a professor to a SAE group.
     *
     * @param User         $responsibleProf The responsible professor.
     * @param integer      $groupId         The SAE group ID.
     * @param integer|null $professorId     The professor to assign.
     * @return boolean Success status.
     * @throws ExceptionAccessDenied If not responsible professor.
     * @throws ExceptionResourceNotFound If group not found.
     */
    public function assignProfessorToGroup(User $responsibleProf, int $groupId, ?int $professorId): bool
    {
        $group = $this->groupRepo->findById($groupId);
        if (!$group) {
            throw new ExceptionResourceNotFound("Groupe non trouvé");
        }

        if (
            $responsibleProf instanceof Professor
            && $responsibleProf->isResponsibleProfessor($group->getSaeSubjectId())
        ) {
            $group->setProfessorId($professorId);
            return $this->groupRepo->update($group);
        } else {
            throw new ExceptionAccessDenied("Seul le responsable peut assigner des professeurs aux groupes");
        }
    }

    /**
     * Gets contact information of group members grouped by SAE group.
     *
     * Each key in the returned array is a SAE group ID.
     *
     * @param User    $user  The requesting user.
     * @param integer $saeId The SAE subject ID.
     *
     * @return array<int, array<int, array{
     * user_id: int,
     * first_name: string,
     * last_name: string,
     * email: string,
     * phone: string,
     * sae_group_id: int,
     * td: int,
     * tp: int
     * }>>
     */
    public function getGroupContacts(User $user, int $saeId): array
    {
        $members = $user->getAccessibleGroupMembers($saeId);

        $grouped = [];

        foreach ($members as $member) {
            $groupId = $member['sae_group_id'];
            $grouped[$groupId][] = $member;
        }

        return $grouped;
    }

    /**
     * Deletes a SAE.
     *
     * @param User    $user  The requesting user.
     * @param integer $saeId The SAE subject ID.
     * @return boolean Success status.
     * @throws ExceptionAccessDenied If user doesn't have permission.
     */
    public function deleteSAE(User $user, int $saeId): bool
    {
        if ($user instanceof Professor && $user->isResponsibleProfessor($saeId)) {
            return $this->subjectRepo->delete($saeId);
        }
        throw new ExceptionAccessDenied("Seul le responsable peut supprimer la SAE");
    }

    /**
     * Removes a student from a group.
     *
     * @param User    $professor The professor.
     * @param integer $studentId The student ID.
     * @param integer $groupId   The group ID.
     * @return boolean Success status.
     * @throws ExceptionResourceNotFound If group is not found.
     * @throws ExceptionAccessDenied     If professor doesn't have permission.
     */
    public function removeStudentFromGroup(User $professor, int $studentId, int $groupId): bool
    {
        $group = $this->groupRepo->findById($groupId);
        if (!$group) {
            throw new ExceptionResourceNotFound("Groupe non trouvé");
        }

        if (!$professor->canManageSAE($group->getSaeSubjectId())) {
            throw new ExceptionAccessDenied("Vous n'avez pas la permission de retirer des étudiants");
        }

        return $this->groupRepo->unassignStudent($studentId, $groupId);
    }

    /**
     * Deletes a group.
     *
     * @param User    $professor The professor.
     * @param integer $groupId   The group ID.
     * @return boolean Success status.
     * @throws ExceptionResourceNotFound If group is not found.
     * @throws ExceptionAccessDenied     If professor doesn't have permission.
     */
    public function deleteGroup(User $professor, int $groupId): bool
    {
        $group = $this->groupRepo->findById($groupId);
        if (!$group) {
            throw new ExceptionResourceNotFound("Groupe non trouvé");
        }

        if (!$professor->canManageSAE($group->getSaeSubjectId())) {
            throw new ExceptionAccessDenied("Vous n'avez pas la permission de supprimer des groupes");
        }

        return $this->groupRepo->delete($groupId);
    }

    /**
     * Gets available students for a SAE (not in any group).
     *
     * @param User    $user  The requesting user.
     * @param integer $saeId The SAE subject ID.
     * @return array<int, array{
     * student_id: string,
     * amu_id: string,
     * year: string,
     * major: string,
     * td: string,
     * tp: string,
     * first_name: string,
     * last_name: string,
     * email: string
     * }>
     * @throws ExceptionAccessDenied If user doesn't have permission.
     */
    public function getAvailableStudents(User $user, int $saeId): array
    {
        if (!$user->canManageSAE($saeId)) {
            throw new ExceptionAccessDenied("Vous n'avez pas la permission de voir les étudiants disponibles");
        }

        return $this->groupRepo->getAvailableStudents($saeId);
    }

    /**
     * Gets the file name (path) of the SAE subject.
     *
     * @param User    $user  The requesting user.
     * @param integer $saeId The SAE subject ID.
     * @return string The relative file path.
     * @throws ExceptionAccessDenied If user doesn't have permission.
     */
    public function getFileName(User $user, int $saeId): string
    {
        if (!$user->canManageSAE($saeId)) {
            throw new ExceptionAccessDenied("Vous n'avez pas la permission de voir les étudiants disponibles");
        }

        return $this->subjectRepo->getFileName($saeId);
    }
}
