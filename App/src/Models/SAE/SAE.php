<?php

namespace Models\SAE;

use Models\User\User;
use Core\includes\exception\ExceptionBD\ExceptionFetchDataBD;
use Core\includes\exception\SAE\ExceptionAccessDenied;
use Core\includes\exception\SAE\ExceptionResourceNotFound;
use Core\includes\exception\SAE\ExceptionInvalidData;
use Models\SAE\Repository\SAESubjectRepository;
use Models\SAE\Repository\SAEGroupRepository;
use Models\SAE\Repository\CompetenceRepository;
use Models\SAE\Repository\SAEProfessorGroupRepository;
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
     * The competence repository.
     *
     * @var CompetenceRepository
     */
    protected CompetenceRepository $competenceRepo;

    /**
     * The professor group repository.
     *
     * @var SAEProfessorGroupRepository
     */
    protected SAEProfessorGroupRepository $professorGroupRepo;

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
        $this->competenceRepo = CompetenceRepository::getInstance();
        $this->professorGroupRepo = SAEProfessorGroupRepository::getInstance();
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
     *   subject: \Models\SAE\SAESubject,
     *   groups: array<int, array{
     *     group: \Models\SAE\SAEGroup,
     *     students: array<int, array{
     *       student_id: string,
     *       amu_id: string,
     *       year: string,
     *       major: string,
     *       td: string,
     *       tp: string,
     *       first_name: string,
     *       last_name: string,
     *       email: string,
     *       phone: string|null
     *     }>
     *   }>,
     *   competences: array<int, \Models\SAE\Competence>,
     *   responsible_professor: array{
     *     user_id: string,
     *     first_name: string,
     *     last_name: string,
     *     email: string,
     *     phone: string|null,
     *     amu_id: string
     *   }|null,
     *   all_professors: array<int, array{
     *     user_id: string,
     *     first_name: string,
     *     last_name: string,
     *     email: string,
     *     phone: string|null,
     *     amu_id: string,
     *     is_responsible: int
     *   }>,
     *   client: array{
     *     user_id: string,
     *     first_name: string,
     *     last_name: string,
     *     email: string,
     *     phone: string|null,
     *     organisation: string
     *   }|null
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

        // Get competences.
        $competences = $this->competenceRepo->findBySaeId($saeId);

        // Get professors info.
        $responsibleProf = $this->subjectRepo->getResponsibleProfessor($saeId);
        $allProfs = $this->subjectRepo->getAllProfessorsInfo($saeId);

        // Get client info.
        $client = $this->subjectRepo->getClientInfo($saeId);

        return [
            'subject' => $subject,
            'groups' => $groups,
            'competences' => $competences,
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
     *   group: \Models\SAE\SAEGroup,
     *   students: array<int, array{
     *     student_id: string,
     *     amu_id: string,
     *     year: string,
     *     major: string,
     *     td: string,
     *     tp: string,
     *     first_name: string,
     *     last_name: string,
     *     email: string,
     *     phone: string|null
     *   }>
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

        // If assigned professor, return only assigned groups.
        if ($user->isProfessor()) {
            $assignedGroupIds = $this->professorGroupRepo->getProfessorGroups($user->getUserId(), $saeId);
            $accessibleGroups = array_filter($allGroups, function ($group) use ($assignedGroupIds) {
                return in_array($group->getSaeGroupId(), $assignedGroupIds);
            });

            return array_map(function ($group) {
                return [
                    'group' => $group,
                    'students' => $this->groupRepo->getGroupStudents(intval($group->getSaeGroupId())),
                ];
            }, $accessibleGroups);
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

        return [];
    }

    /**
     * Gets the group ID of a student in a SAE.
     *
     * @param User    $student The student.
     * @param integer $saeId   The SAE subject ID.
     * @return integer|null
     */
    private function getUserGroupId(User $student, int $saeId): ?int
    {
        return $this->groupRepo->getStudentGroupId($student->getUserId(), $saeId);
    }

    /**
     * Creates a new SAE with competences.
     *
     * @param User                 $creator The professor creating the SAE.
     * @param array<string, mixed> $data    SAE data.
     * @return SAESubject The created SAE.
     * @throws ExceptionAccessDenied If user doesn't have permission.
     * @throws ExceptionInvalidData  If data is invalid.
     */
    public function createSAE(User $creator, array $data): SAESubject
    {
        // Check permission.
        if (!$creator->canManageSAE()) {
            throw new ExceptionAccessDenied("Vous n'avez pas la permission de créer une SAE");
        }

        // Create SAE subject.
        $subject = new SAESubject($data);
        $errors = $subject->validate();
        if (!empty($errors)) {
            throw new ExceptionInvalidData(implode(', ', $errors));
        }

        $subject = $this->subjectRepo->create($subject);

        // Create competences if provided.
        if (!empty($data['competences']) && is_array($data['competences'])) {
            foreach ($data['competences'] as $competenceName) {
                $this->competenceRepo->create(intval($subject->getSaeSubjectId()), $competenceName);
            }
        }

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
     * @throws ExceptionInvalidData      If data is invalid.
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

        // Update subject fields.
        foreach ($data as $key => $value) {
            $setter = 'set' . ucfirst($key);
            if (method_exists($subject, $setter)) {
                $subject->$setter($value);
            }
        }

        $errors = $subject->validate();
        if (!empty($errors)) {
            throw new ExceptionInvalidData(implode(', ', $errors));
        }

        $success = $this->subjectRepo->update($subject);

        // Update competences if provided.
        if (isset($data['competences']) && is_array($data['competences'])) {
            $this->competenceRepo->updateSaeCompetences($saeId, $data['competences']);
        }

        return $success;
    }

    /**
     * Creates a new group for a SAE.
     *
     * @param User    $user  The requesting user.
     * @param integer $saeId The SAE subject ID.
     * @return SAEGroup The created group.
     * @throws ExceptionAccessDenied If user doesn't have permission.
     */
    public function createGroup(User $user, int $saeId): SAEGroup
    {
        if (!$user->canManageSAE($saeId)) {
            throw new ExceptionAccessDenied("Vous n'avez pas la permission de créer un groupe");
        }

        $group = new SAEGroup(['sae_subject_id' => $saeId]);
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
     * Assigns a professor to a SAE.
     *
     * @param User    $responsibleProf The responsible professor.
     * @param integer $saeId           The SAE subject ID.
     * @param integer $professorId     The professor to assign.
     * @return boolean Success status.
     * @throws ExceptionAccessDenied If not responsible professor.
     */
    public function assignProfessorToSAE(User $responsibleProf, int $saeId, int $professorId): bool
    {
        if ($responsibleProf instanceof Professor && $responsibleProf->isResponsibleProfessor($saeId)) {
            return $this->professorGroupRepo->assignProfessor($saeId, $professorId);
        } else {
            throw new ExceptionAccessDenied("Seul le responsable peut assigner des professeurs");
        }
    }

    /**
     * Gets contact information of group members grouped by SAE group.
     * Each key in the returned array is a SAE group ID.
     *
     * @param User    $user  The requesting user.
     * @param integer $saeId The SAE subject ID.
     *
     * @return array<int, array<int, array{
     *     user_id: int,
     *     first_name: string,
     *     last_name: string,
     *     email: string,
     *     phone: string,
     *     sae_group_id: int,
     *     td: int,
     *     tp: int
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
}
