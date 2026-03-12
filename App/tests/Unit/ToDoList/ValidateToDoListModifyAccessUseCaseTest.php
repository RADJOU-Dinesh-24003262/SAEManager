<?php

namespace Tests\Unit\ToDoList;

use Core\Includes\Exception\SAE\ExceptionAccessDenied;
use Models\Entity\User\User;
use Models\Entity\SAE\SAEGroup;
use Models\UseCase\ToDoList\ValidateToDoListModifyAccessUseCase;
use Models\UseCase\SAE\InterfaceDB\ParticipatedInInterface;
use Models\UseCase\SAE\InterfaceDB\SAEGroupInterface;
use Models\UseCase\SAE\InterfaceDB\SAESubjectInterface;
use Models\UseCase\User\InterfaceDB\ClientInterface;
use Models\UseCase\User\InterfaceDB\ProfessorInterface;
use Models\UseCase\User\InterfaceDB\StudentInterface;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Models\UseCase\SAE\GetCompleteSAEDataUseCase;

/**
 * Unit test for ValidateToDoListModifyAccessUseCase class.
 *
 * @category Tests
 * @package  Tests\Unit\ToDoList
 */
#[CoversClass(ValidateToDoListModifyAccessUseCase::class)]
#[CoversClass(GetCompleteSAEDataUseCase::class)]
class ValidateToDoListModifyAccessUseCaseTest extends TestCase
{
    private SAESubjectInterface $subjectRepo;
    private SAEGroupInterface $groupRepo;
    private ParticipatedInInterface $participatedInRepo;
    private StudentInterface $studentRepo;
    private ProfessorInterface $professorRepo;
    private ClientInterface $clientRepo;
    private ValidateToDoListModifyAccessUseCase $useCase;

    protected function setUp(): void
    {
        $this->subjectRepo = $this->createMock(SAESubjectInterface::class);
        $this->groupRepo = $this->createMock(SAEGroupInterface::class);
        $this->participatedInRepo = $this->createMock(ParticipatedInInterface::class);
        $this->studentRepo = $this->createMock(StudentInterface::class);
        $this->professorRepo = $this->createMock(ProfessorInterface::class);
        $this->clientRepo = $this->createMock(ClientInterface::class);

        $this->useCase = new ValidateToDoListModifyAccessUseCase(
            $this->subjectRepo,
            $this->groupRepo,
            $this->participatedInRepo,
            $this->studentRepo,
            $this->professorRepo,
            $this->clientRepo
        );
    }

    /**
     * Test execution fails and throws ExceptionAccessDenied when student cannot access SAE.
     */
    public function testExecuteThrowsExceptionWhenStudentCannotAccessSAE(): void
    {
        $user = $this->createMock(User::class);
        $user->method('isStudent')->willReturn(true);
        $user->method('getUserId')->willReturn(1);

        $this->studentRepo->expects($this->once())
            ->method('canAccessSAE')
            ->with(1, 100)
            ->willReturn(false);

        $this->expectException(ExceptionAccessDenied::class);
        $this->expectExceptionMessage("Vous n'avez pas accès à cette SAE.");

        $this->useCase->execute($user, 100);
    }

    /**
     * Test execution fails and throws ExceptionAccessDenied when user is not a student (e.g., Professor).
     */
    public function testExecuteThrowsExceptionWhenUserIsNotStudent(): void
    {
        $user = $this->createMock(User::class);
        $user->method('isStudent')->willReturn(false);

        $this->expectException(ExceptionAccessDenied::class);
        $this->expectExceptionMessage("Vous n'avez pas le droit de modifier cette To-Do List.");

        $this->useCase->execute($user, 100);
    }

    /**
     * Test execution fails and throws ExceptionAccessDenied when SAEDate cannot be resolved.
     */
    public function testExecuteThrowsExceptionWhenSAENotFoundOrUnauthorized(): void
    {
        $user = $this->createMock(User::class);
        $user->method('getUserType')->willReturn('student');
        $user->method('isStudent')->willReturn(true);
        $user->method('getUserId')->willReturn(1);

        $this->studentRepo->expects($this->exactly(2))
            ->method('canAccessSAE')
            ->with(1, 100)
            ->willReturn(true);

        $this->subjectRepo->expects($this->once())
            ->method('findById')
            ->willReturn(null); // Triggers empty saeData or unauthorized

        $this->expectException(ExceptionAccessDenied::class);
        $this->expectExceptionMessage("Accès non autorisé à cette SAE.");
        $this->expectExceptionCode(403);

        $this->useCase->execute($user, 100);
    }

    /**
     * Test execution fails and throws ExceptionAccessDenied when the student has no assigned group.
     */
    public function testExecuteThrowsExceptionWhenStudentHasNoGroup(): void
    {
        $user = $this->createMock(User::class);
        $user->method('getUserType')->willReturn('student');
        $user->method('isStudent')->willReturn(true);
        $user->method('getUserId')->willReturn(1);

        $this->studentRepo->expects($this->exactly(2))
            ->method('canAccessSAE')
            ->willReturn(true);

        $mockSubject = $this->createMock(\Models\Entity\SAE\SAESubject::class);
        $mockSubject->method('getSaeSubjectId')->willReturn(100);

        $this->subjectRepo->expects($this->once())
            ->method('findById')
            ->willReturn($mockSubject); // Not null

        $this->groupRepo->expects($this->once())
            ->method('findBySaeSubjectId')
            ->willReturn([]); // Return any array

        $this->participatedInRepo->expects($this->once())
            ->method('getStudentGroupId')
            ->willReturn(null); // Student has no group

        $this->expectException(ExceptionAccessDenied::class);
        $this->expectExceptionMessage("Vous n'êtes assigné à aucun groupe.");
        $this->expectExceptionCode(403);

        $this->useCase->execute($user, 100);
    }

    /**
     * Test execution succeeds and returns the group ID for an authorized student assigned to a group.
     */
    public function testExecuteReturnsGroupIdForAuthorizedAssignedStudent(): void
    {
        $user = $this->createMock(User::class);
        $user->method('getUserType')->willReturn('student');
        $user->method('isStudent')->willReturn(true);
        $user->method('getUserId')->willReturn(1);
        
        $this->studentRepo->expects($this->exactly(2))
            ->method('canAccessSAE')
            ->willReturn(true);

        $mockSubject = $this->createMock(\Models\Entity\SAE\SAESubject::class);
        $mockSubject->method('getSaeSubjectId')->willReturn(100);

        $this->subjectRepo->expects($this->once())
            ->method('findById')
            ->willReturn($mockSubject);

        $mockGroup = $this->createMock(SAEGroup::class);
        $mockGroup->method('getSaeGroupId')->willReturn(42);

        $this->groupRepo->expects($this->once())
            ->method('findBySaeSubjectId')
            ->willReturn([$mockGroup]);

        $this->participatedInRepo->expects($this->once())
            ->method('getStudentGroupId')
            ->willReturn(42);

        $this->groupRepo->expects($this->once())
            ->method('findById')
            ->with(42)
            ->willReturn($mockGroup);

        $result = $this->useCase->execute($user, 100);

        $this->assertEquals(42, $result);
    }
}
