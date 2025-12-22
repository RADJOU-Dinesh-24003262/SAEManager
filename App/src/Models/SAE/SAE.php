<?php

namespace Models\SAE;

use Models\User\User;
use Models\Utilis\AccessControlService;
use Core\includes\exception\ExceptionBD\ExceptionFetchDataBD;
use Models\SAE\Repository\SAESubjectRepository;
use Models\SAE\Repository\SAEGroupRepository;
use Models\SAE\Repository\CompetenceRepository;
use Models\SAE\Repository\SAEProfessorGroupRepository;

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
 * @subpackage Models\SAE
 * @author     SAE Manager Team
 * @license    MIT License https://opensource.org/licenses/MIT
 */
class SAE
{
    private SAESubjectRepository $subjectRepo;
    private SAEGroupRepository $groupRepo;
    private CompetenceRepository $competenceRepo;
    private SAEProfessorGroupRepository $professorGroupRepo;
    private AccessControlService $accessControl;

    /**
     * Singleton instance
     *
     * @var SAE|null
     */
    private static ?SAE $instance = null;

    /**
     * Constructor
     */
    private function __construct()
    {
        $this->subjectRepo = SAESubjectRepository::getInstance();
        $this->groupRepo = SAEGroupRepository::getInstance();
        $this->competenceRepo = CompetenceRepository::getInstance();
        $this->professorGroupRepo = SAEProfessorGroupRepository::getInstance();
        $this->accessControl = new AccessControlService();
    }

    /**
     * Gets the singleton instance
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
     * Gets all SAEs accessible by a user
     *
     * @param User $user The requesting user
     * @return array<SAESubject> Array of SAE subjects
     * @throws ExceptionFetchDataBD
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
     * Gets complete SAE data with access control
     *
     * @param integer $saeId The SAE subject ID
     * @param User    $user  The requesting user
     * @return array|null Complete SAE data or null if no access
     * @throws ExceptionFetchDataBD
     */
    public function getCompleteSAEData(int $saeId, User $user): ?array
    {
        // Check access
        if (!$this->accessControl->canAccessSAE($user, $saeId)) {
            return null;
        }

        $subject = $this->subjectRepo->findById($saeId);
        if (!$subject) {
            return null;
        }

        // Get groups accessible by the user
        $groups = $this->getAccessibleGroups($saeId, $user);

        // Get competences
        $competences = $this->competenceRepo->findBySaeId($saeId);

        // Get professors info
        $responsibleProf = $this->subjectRepo->getResponsibleProfessor($saeId);
        $allProfs = $this->subjectRepo->getAllProfessorsInfo($saeId);

        // Get client info
        $client = $this->subjectRepo->getClientInfo($saeId);

        return [
            'subject' => $subject,
            'groups' => $groups,
            'competences' => $competences,
            'responsible_professor' => $responsibleProf,
            'all_professors' => $allProfs,
            'client' => $client,
            'can_modify' => $this->accessControl->canManageSAE($user, $saeId),
            'is_responsible' => $this->accessControl->isResponsibleProfessor($user, $saeId),
        ];
    }

    /**
     * Gets groups accessible by a user for a SAE
     *
     * @param integer $saeId The SAE subject ID
     * @param User    $user  The requesting user
     * @return array Array of groups with details
     */
    private function getAccessibleGroups(int $saeId, User $user): array
    {
        $allGroups = $this->groupRepo->findBySaeId($saeId);

        // If responsible professor, return all groups
        if ($this->accessControl->isResponsibleProfessor($user, $saeId)) {
            return array_map(function ($group) {
                return [
                    'group' => $group,
                    'students' => $this->groupRepo->getGroupStudents(intval($group->getSaeGroupId())),
                ];
            }, $allGroups);
        }

        // If assigned professor, return only assigned groups
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

        // If student or client, return only their group
        if ($user->isStudent()) {
            $userGroupId = $this->getUserGroupId($user, $saeId);
            if ($userGroupId) {
                $group = $this->groupRepo->findById($userGroupId);
                return [[
                    'group' => $group,
                    'students' => $this->groupRepo->getGroupStudents($userGroupId),
                ]];
            }
        }

        return [];
    }

    /**
     * Gets the group ID of a student in a SAE
     *
     * @param User    $student The student
     * @param integer $saeId   The SAE subject ID
     * @return integer|null
     */
    private function getUserGroupId(User $student, int $saeId): ?int
    {
        return $this->groupRepo->getStudentGroupId($student->getUserId(), $saeId);
    }

    /**
     * Creates a new SAE with competences
     *
     * @param User  $creator The professor creating the SAE
     * @param array $data    SAE data
     * @return SAESubject The created SAE
     * @throws \Exception If user doesn't have permission or data is invalid
     */
    public function createSAE(User $creator, array $data): SAESubject
    {
        // Check permission
        if (!$this->accessControl->canManageSAE($creator)) {
            throw new \Exception("Vous n'avez pas la permission de créer une SAE");
        }

        // Create SAE subject
        $subject = new SAESubject($data);
        $errors = $subject->validate();
        if (!empty($errors)) {
            throw new \Exception(implode(', ', $errors));
        }

        $subject = $this->subjectRepo->create($subject);

        // Create competences if provided
        if (!empty($data['competences']) && is_array($data['competences'])) {
            foreach ($data['competences'] as $competenceName) {
                $this->competenceRepo->create(intval($subject->getSaeSubjectId()), $competenceName);
            }
        }

        return $subject;
    }

    /**
     * Updates a SAE
     *
     * @param User    $user  The user updating the SAE
     * @param integer $saeId The SAE ID
     * @param array   $data  Updated data
     * @return boolean Success status
     * @throws \Exception If user doesn't have permission
     */
    public function updateSAE(User $user, int $saeId, array $data): bool
    {
        if (!$this->accessControl->canManageSAE($user, $saeId)) {
            throw new \Exception("Vous n'avez pas la permission de modifier cette SAE");
        }

        $subject = $this->subjectRepo->findById($saeId);
        if (!$subject) {
            throw new \Exception("SAE non trouvée");
        }

        // Update subject fields
        foreach ($data as $key => $value) {
            $setter = 'set' . ucfirst($key);
            if (method_exists($subject, $setter)) {
                $subject->$setter($value);
            }
        }

        $errors = $subject->validate();
        if (!empty($errors)) {
            throw new \Exception(implode(', ', $errors));
        }

        $success = $this->subjectRepo->update($subject);

        // Update competences if provided
        if (isset($data['competences']) && is_array($data['competences'])) {
            $this->competenceRepo->updateSaeCompetences($saeId, $data['competences']);
        }

        return $success;
    }

    /**
     * Creates a new group for a SAE
     *
     * @param User    $user  The requesting user
     * @param integer $saeId The SAE subject ID
     * @return SAEGroup The created group
     * @throws \Exception If user doesn't have permission
     */
    public function createGroup(User $user, int $saeId): SAEGroup
    {
        if (!$this->accessControl->canManageSAE($user, $saeId)) {
            throw new \Exception("Vous n'avez pas la permission de créer un groupe");
        }

        return $this->groupRepo->create($saeId);
    }

    /**
     * Assigns a student to a group
     *
     * @param User    $professor The professor
     * @param integer $studentId The student ID
     * @param integer $groupId   The group ID
     * @return boolean Success status
     * @throws \Exception If professor doesn't have permission
     */
    public function assignStudentToGroup(User $professor, int $studentId, int $groupId): bool
    {
        $group = $this->groupRepo->findById($groupId);
        if (!$group) {
            throw new \Exception("Groupe non trouvé");
        }

        if (!$this->accessControl->canManageSAE($professor, $group->getSaeSubjectId())) {
            throw new \Exception("Vous n'avez pas la permission d'assigner des étudiants");
        }

        return $this->groupRepo->assignStudent($studentId, $groupId);
    }

    /**
     * Assigns a professor to a SAE
     *
     * @param User    $responsibleProf The responsible professor
     * @param integer $saeId           The SAE subject ID
     * @param integer $professorId     The professor to assign
     * @return boolean Success status
     * @throws \Exception If not responsible professor
     */
    public function assignProfessorToSAE(User $responsibleProf, int $saeId, int $professorId): bool
    {
        if (!$this->accessControl->isResponsibleProfessor($responsibleProf, $saeId)) {
            throw new \Exception("Seul le responsable peut assigner des professeurs");
        }

        return $this->professorGroupRepo->assignProfessor($saeId, $professorId);
    }

    /**
     * Gets contact information of group members
     *
     * @param User    $user  The requesting user
     * @param integer $saeId The SAE subject ID
     * @return array Array of contact information
     */
    public function getGroupContacts(User $user, int $saeId): array
    {
        return $this->accessControl->getAccessibleGroupMembers($user, $saeId);
    }

    /**
     * Deletes a SAE
     *
     * @param User    $user  The requesting user
     * @param integer $saeId The SAE subject ID
     * @return boolean Success status
     * @throws \Exception If user doesn't have permission
     */
    public function deleteSAE(User $user, int $saeId): bool
    {
        if (!$this->accessControl->isResponsibleProfessor($user, $saeId)) {
            throw new \Exception("Seul le responsable peut supprimer la SAE");
        }

        return $this->subjectRepo->delete($saeId);
    }
}
