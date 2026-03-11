<?php

namespace Tests\Integration\Models\User;

use Models\Entity\User\User;
use Models\Entity\User\Student;
use Models\Entity\User\Professor;
use Models\Entity\User\Client;
use Models\Entity\User\UserFactory;
use Models\Repository\User\PdoUserRepository;
use Models\Repository\User\PdoStudentRepository;
use Models\Repository\User\PdoProfessorRepository;
use Models\Repository\User\PdoClientRepository;
use Models\UseCase\User\RegisterUserUseCase;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Core\Includes\Database;
use ReflectionClass;
use Core\Includes\Exception\ExceptionEmailAlreadyExists;

#[CoversClass(RegisterUserUseCase::class)]
#[CoversClass(PdoUserRepository::class)]
#[CoversClass(PdoStudentRepository::class)]
#[CoversClass(PdoProfessorRepository::class)]
#[CoversClass(PdoClientRepository::class)]
#[CoversClass(User::class)]
#[CoversClass(Student::class)]
#[CoversClass(Professor::class)]
#[CoversClass(Client::class)]
#[CoversClass(ExceptionEmailAlreadyExists::class)]
#[CoversClass(UserFactory::class)]
class RegisterUserUseCaseTest extends TestCase
{
    private $studentRepo;
    private $professorRepo;
    private $clientRepo;
    private $userRepo;
    private $useCase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->studentRepo = $this->createMock(PdoStudentRepository::class);
        $this->professorRepo = $this->createMock(PdoProfessorRepository::class);
        $this->clientRepo = $this->createMock(PdoClientRepository::class);
        $this->userRepo = $this->createMock(PdoUserRepository::class);

        $this->useCase = new RegisterUserUseCase(
            $this->studentRepo,
            $this->professorRepo,
            $this->clientRepo,
            $this->userRepo
        );
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    #[Test]
    public function canRegisterStudent(): void
    {
        $data = [
            'first_name' => 'John',
            'last_name' => 'StudentReg',
            'email' => 'john.student.reg@test.com',
            'password' => 'password123',
            'phone' => '0600000001',
            'user_type' => 'student',
            'amu_id' => 's_reg_1',
            'year' => 2,
            'td' => 'TD1',
            'tp' => 'TP1'
        ];

        $this->userRepo->method('existsByEmail')->willReturn(false);
        $this->studentRepo->method('insert')->willReturn(1);

        $mockStudent = new Student($data);
        $reflection = new ReflectionClass(Student::class);
        $prop = $reflection->getProperty('user_id');
        $prop->setAccessible(true);
        $prop->setValue($mockStudent, 1);

        $this->studentRepo->method('findById')->willReturn($mockStudent);

        $user = $this->useCase->execute($data);

        $this->assertNotNull($user);
        $this->assertInstanceOf(Student::class, $user);
        $this->assertEquals('john.student.reg@test.com', $user->getEmail());
    }

    #[Test]
    public function canRegisterProfessor(): void
    {
        $data = [
            'first_name' => 'Prof',
            'last_name' => 'Reg',
            'email' => 'prof.reg@test.com',
            'password' => 'password123',
            'phone' => '0600000002',
            'user_type' => 'professor',
            'amu_id' => 'p_reg_1'
        ];

        $this->userRepo->method('existsByEmail')->willReturn(false);
        $this->professorRepo->method('insert')->willReturn(2);

        $mockProf = new Professor($data);
        $reflection = new ReflectionClass(Professor::class);
        $prop = $reflection->getProperty('user_id');
        $prop->setAccessible(true);
        $prop->setValue($mockProf, 2);

        $this->professorRepo->method('findById')->willReturn($mockProf);

        $user = $this->useCase->execute($data);

        $this->assertNotNull($user);
        $this->assertInstanceOf(Professor::class, $user);
        $this->assertEquals('prof.reg@test.com', $user->getEmail());
    }

    #[Test]
    public function canRegisterClient(): void
    {
        $data = [
            'first_name' => 'Client',
            'last_name' => 'Reg',
            'email' => 'client.reg@test.com',
            'password' => 'password123',
            'phone' => '0600000003',
            'user_type' => 'client',
            'organisation' => 'Test Corp'
        ];

        $this->userRepo->method('existsByEmail')->willReturn(false);
        $this->clientRepo->method('insert')->willReturn(3);

        $mockClient = new Client($data);
        $reflection = new ReflectionClass(Client::class);
        $prop = $reflection->getProperty('user_id');
        $prop->setAccessible(true);
        $prop->setValue($mockClient, 3);

        $this->clientRepo->method('findById')->willReturn($mockClient);

        $user = $this->useCase->execute($data);

        $this->assertNotNull($user);
        $this->assertInstanceOf(Client::class, $user);
        $this->assertEquals('client.reg@test.com', $user->getEmail());
    }

    #[Test]
    public function cannotRegisterDuplicateEmail(): void
    {
        $data = [
            'first_name' => 'Dup',
            'last_name' => 'One',
            'email' => 'dup.student@test.com',
            'password' => 'password123',
            'phone' => '0600000004',
            'user_type' => 'student',
            'amu_id' => 's_dup_1',
            'year' => 1,
            'td' => 'A',
            'tp' => '1'
        ];

        $this->userRepo->method('existsByEmail')->willReturn(true);

        $this->expectException(ExceptionEmailAlreadyExists::class);

        $this->useCase->execute($data);
    }
}
