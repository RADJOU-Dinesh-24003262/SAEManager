<?php

namespace Tests\Unit\Models\UseCase\User;

use Core\Includes\Exception\ExceptionEmailAlreadyExists;
use InvalidArgumentException;
use Models\Entity\User\Student;
use Models\Entity\User\User;
use Models\Entity\User\UserFactory;
use Models\Repository\User\PdoClientRepository;
use Models\Repository\User\PdoProfessorRepository;
use Models\Repository\User\PdoStudentRepository;
use Models\Repository\User\PdoUserRepository;
use Models\UseCase\User\InterfaceDB\UserInterface;
use Models\UseCase\User\RegisterUserUseCase;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(RegisterUserUseCase::class)]
#[CoversClass(User::class)]
#[CoversClass(Student::class)]
#[CoversClass(ExceptionEmailAlreadyExists::class)]
#[CoversClass(UserFactory::class)]
class RegisterUserUseCaseTest extends TestCase
{
    private UserInterface|MockObject $userRepository;
    private StudentInterface|MockObject $studentRepository;
    private ProfessorInterface|MockObject $profRepository;
    private ClientInterface|MockObject $clientRepository;
    private RegisterUserUseCase $registerUseCase;

    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(PdoUserRepository::class);
        $this->studentRepository = $this->createMock(PdoStudentRepository::class);
        $this->profRepository = $this->createMock(PdoProfessorRepository::class);
        $this->clientRepository = $this->createMock(PdoClientRepository::class);
        $this->registerUseCase = new RegisterUserUseCase($this->studentRepository, $this->profRepository, $this->clientRepository, $this->userRepository);
    }

    #[Test]
    public function executeCreatesStudentSuccessfully(): void
    {
        $data = [
            'user_type' => 'student',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe', // Will be appended with @etu.univ-amu.fr
            'phone' => '0612345678',
            'password' => 'password123',
            'amu_id' => '12345678',
            'year' => 1,
            'td' => 'TD1',
            'tp' => 'TP1'
        ];

        $this->userRepository->expects($this->once())
            ->method('existsByEmail')
            ->willReturn(false);

        $this->studentRepository->expects($this->once())
            ->method('insert')
            ->willReturnCallback(function (User $user) {
                return 1;
            });

        $this->studentRepository->expects($this->once())
            ->method('findById')
            ->willReturn(new Student($data));

        $user = $this->registerUseCase->execute($data);

        $this->assertInstanceOf(Student::class, $user);
        $this->assertEquals('John', $user->getFirstName());
    }

    #[Test]
    public function executeThrowsExceptionIfEmailExists(): void
    {
        $data = [
            'user_type' => 'student',
            'first_name' => 'John',
            'email' => 'john.doe@etu.univ-amu.fr'
        ];

        $this->userRepository->expects($this->once())
            ->method('existsByEmail')
            ->willReturn(true);

        $this->expectException(ExceptionEmailAlreadyExists::class);

        $this->registerUseCase->execute($data);
    }
}
