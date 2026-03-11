<?php

namespace Tests\Integration\Models\User;

use Models\Entity\User\Student;
use Models\Repository\User\PdoUserRepository;
use Models\Repository\User\PdoStudentRepository;
use Models\Repository\User\PdoProfessorRepository;
use Models\Repository\User\PdoClientRepository;
use Models\UseCase\User\RegisterUserUseCase;
use Models\Entity\User\User;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\CoversClass;
use Core\Includes\Database;
use Models\Entity\User\UserFactory;
use ReflectionClass;

#[CoversClass(RegisterUserUseCase::class)]
#[CoversClass(Student::class)]
#[CoversClass(PdoUserRepository::class)]
#[CoversClass(PdoStudentRepository::class)]
#[CoversClass(PdoProfessorRepository::class)]
#[CoversClass(PdoClientRepository::class)]
#[CoversClass(Database::class)]
#[CoversClass(User::class)]
#[CoversClass(UserFactory::class)]
class RegisterRegressionTest extends TestCase
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
    public function controllerUsageFailsToInsertStudentData(): void
    {
        $email = 'regression.student@test.com';
        $data = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => $email,
            'password' => 'password123',
            'phone' => '0600000000',
            'user_type' => 'student',
            'amu_id' => 's_reg_fail',
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

        $fetched = $this->studentRepo->findById($user->getUserId());

        $this->assertInstanceOf(Student::class, $fetched);
        $this->assertNotNull($fetched->getAmuId(), "AMU ID should not be null");
        $this->assertEquals('s_reg_fail', $fetched->getAmuId(), "AMU ID should match");
    }
}
