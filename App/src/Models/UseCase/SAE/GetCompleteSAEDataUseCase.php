<?php

namespace Models\UseCase\SAE;

use Core\includes\exception\ExceptionBD\ExceptionFetchDataBD;
use Models\Entity\SAE\SAESubject;
use Models\UseCase\SAE\InterfaceDB\ParticipatedInInterface;
use Models\UseCase\SAE\InterfaceDB\SAEGroupInterface;
use Models\UseCase\SAE\InterfaceDB\SAESubjectInterface;
use Models\Entity\User\Professor;
use Models\Entity\User\User;

/**
 * Use case for retrieving complete SAE data with access control.
 *
 * @category   Models
 * @package    Src
 * @subpackage Models/UseCase/SAE
 * @author     Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license    MIT License https://opensource.org/licenses/MIT
 * @link       https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
class GetCompleteSAEDataUseCase
{
    /**
     * The SAE subject interface.
     *
     * @var SAESubjectInterface
     */
    private SAESubjectInterface $subjectInterface;

    /**
     * The SAE group interface.
     *
     * @var SAEGroupInterface
     */
    private SAEGroupInterface $groupInterface;

    /**
     * The participated in interface.
     *
     * @var ParticipatedInInterface
     */
    private ParticipatedInInterface $participatedInInterface;

    /**
     * Constructor.
     *
     * @param SAESubjectInterface     $subjectInterface        The SAE subject interface.
     * @param SAEGroupInterface       $groupInterface          The SAE group interface.
     * @param ParticipatedInInterface $participatedInInterface The participated in interface.
     */
    public function __construct(
        SAESubjectInterface $subjectInterface,
        SAEGroupInterface $groupInterface,
        ParticipatedInInterface $participatedInInterface
    ) {
        $this->subjectInterface = $subjectInterface;
        $this->groupInterface = $groupInterface;
        $this->participatedInInterface = $participatedInInterface;
    }

    /**
     * Executes the use case.
     *
     * @param integer $saeId The SAE subject ID.
     * @param User    $user  The requesting user.
     * @return array{
     *   subject: SAESubject,
     *   groups: array<int, array{
     *     group: \Models\Entity\SAE\SAEGroup,
     *     students: array<int, array{
     *       student_id: string,
     *       amu_id: string,
     *       year: string,
     *       td: string,
     *       tp: string,
     *       first_name: string,
     *       last_name: string,
     *       email: string,
     *       phone: string|null
     *     }>
     *   }>,
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
    public function execute(int $saeId, User $user): ?array
    {
        $subject = $this->subjectInterface->findById($saeId);
        if (!$subject) {
            return null;
        }

        // Get groups accessible by the user.
        $groups = $this->getAccessibleGroups($subject, $user);

        // Get professors info.
        $responsibleProf = $this->subjectInterface->getResponsibleProfessor($saeId);
        $allProfs = $this->subjectInterface->getAllProfessorsInfo($saeId);

        // Get client info.
        $client = $this->subjectInterface->getClientInfo($saeId);

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
     * @param SAESubject $subject The SAE subject.
     * @param User       $user    The requesting user.
     * @return array<int, array{
     *   group: \Models\Entity\SAE\SAEGroup,
     *   students: array<int, array{
     *     student_id: string,
     *     amu_id: string,
     *     year: string,
     *     td: string,
     *     tp: string,
     *     first_name: string,
     *     last_name: string,
     *     email: string,
     *     phone: string|null
     *   }>
     * }>
     */
    private function getAccessibleGroups(SAESubject $subject, User $user): array
    {
        $saeId = (int)$subject->getSaeSubjectId();
        $allGroups = $this->groupInterface->findBySaeSubjectId($saeId);

        // If responsible professor, return all groups.
        if ($user instanceof Professor && $subject->getResponsibleProfId() === $user->getUserId()) {
            return array_map(function ($group) {
                return [
                    'group' => $group,
                    'students' => $this->groupInterface->getStudentsInGroup(intval($group->getSaeGroupId())),
                ];
            }, $allGroups);
        }

        // If student, return only their group.
        if ($user->isStudent()) {
            $userGroupId = $this->participatedInInterface->getStudentGroupId($user->getUserId(), $saeId);
            if ($userGroupId) {
                $group = $this->groupInterface->findById($userGroupId);
                if ($group) {
                    return [[
                            'group' => $group,
                            'students' => $this->groupInterface->getStudentsInGroup($userGroupId),
                        ]];
                }
            }
        }

        // If client, see all groups.
        if ($user->isClient()) {
            return array_map(function ($group) {
                return [
                    'group' => $group,
                    'students' => $this->groupInterface->getStudentsInGroup(intval($group->getSaeGroupId())),
                ];
            }, $allGroups);
        }

        return [];
    }
}
