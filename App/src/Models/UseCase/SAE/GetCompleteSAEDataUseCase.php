<?php

namespace Models\UseCase\SAE;

use Core\includes\exception\ExceptionBD\ExceptionFetchDataBD;
use Models\Entity\SAE\SAESubject;
use Models\Entity\User\Client;
use Models\Entity\User\Student;
use Models\UseCase\SAE\InterfaceDB\ParticipatedInInterface;
use Models\UseCase\SAE\InterfaceDB\SAEGroupInterface;
use Models\UseCase\SAE\InterfaceDB\SAESubjectInterface;
use Models\UseCase\User\InterfaceDB\StudentInterface;
use Models\UseCase\User\InterfaceDB\ProfessorInterface;
use Models\UseCase\User\InterfaceDB\ClientInterface;
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
     * The student interface.
     * @var StudentInterface
     */
    private StudentInterface $studentInterface;

    /**
     * The professor interface.
     * @var ProfessorInterface
     */
    private ProfessorInterface $professorInterface;

    /**
     * The client interface.
     * @var ClientInterface
     */
    private ClientInterface $clientInterface;

    /**
     * Constructor.
     *
     * @param SAESubjectInterface     $subjectInterface        The SAE subject interface.
     * @param SAEGroupInterface       $groupInterface          The SAE group interface.
     * @param ParticipatedInInterface $participatedInInterface The participated in interface.
     * @param StudentInterface        $studentInterface        The student interface.
     * @param ProfessorInterface      $professorInterface      The professor interface.
     * @param ClientInterface         $clientInterface         The client interface.
     */
    public function __construct(
        SAESubjectInterface $subjectInterface,
        SAEGroupInterface $groupInterface,
        ParticipatedInInterface $participatedInInterface,
        StudentInterface $studentInterface,
        ProfessorInterface $professorInterface,
        ClientInterface $clientInterface
    ) {
        $this->subjectInterface = $subjectInterface;
        $this->groupInterface = $groupInterface;
        $this->participatedInInterface = $participatedInInterface;
        $this->studentInterface = $studentInterface;
        $this->professorInterface = $professorInterface;
        $this->clientInterface = $clientInterface;
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
     *   }|array{}|null
     * }|null Complete SAE data or null if no access.
     * @throws ExceptionFetchDataBD If data cannot be fetched.
     */
    public function execute(int $saeId, User $user): ?array
    {
        $accessInterfaces = [
            'student' => $this->studentInterface,
            'professor' => $this->professorInterface,
            'client' => $this->clientInterface,
        ];

        $canAccess = false;
        if (isset($accessInterfaces[$user->getUserType()])) {
            $canAccess = $accessInterfaces[$user->getUserType()]->canAccessSAE($user->getUserId(), $saeId);
        }

        if (!$canAccess) {
            return null;
        }

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
            'client' => $client ? $client : []
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

        $strategies = [

            'professor' => function () use ($allGroups, $subject, $user) {

                $result = [];

                $isResponsible = $subject->getResponsibleProfId() === $user->getUserId();
                if (!$isResponsible) {
                    return $result;
                }

                foreach ($allGroups as $group) {
                    $groupId = (int) $group->getSaeGroupId();

                    $students = $this->groupInterface->getStudentsInGroup($groupId);

                    $result[] = [
                        'group'    => $group,
                        'students' => $students,
                    ];
                }

                return $result;
            },

            'student' => function () use ($saeId, $user) {

                $result = [];

                $userId  = $user->getUserId();
                $groupId = $this->participatedInInterface->getStudentGroupId($userId, $saeId);

                if (!$groupId) {
                    return $result;
                }

                $group = $this->groupInterface->findById($groupId);
                if (!$group) {
                    return $result;
                }

                $students = $this->groupInterface->getStudentsInGroup((int) $groupId);

                $result[] = [
                    'group'    => $group,
                    'students' => $students,
                ];

                return $result;
            },

            'client' => function () use ($allGroups) {

                $result = [];

                foreach ($allGroups as $group) {
                    $groupId = (int) $group->getSaeGroupId();

                    $students = $this->groupInterface->getStudentsInGroup($groupId);

                    $result[] = [
                        'group'    => $group,
                        'students' => $students,
                    ];
                }

                return $result;
            },
        ];

        return $strategies[$user->getUserType()]();
    }
}
