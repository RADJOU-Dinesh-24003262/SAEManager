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
use Core\includes\Database;
use ReflectionClass;
use Core\includes\exception\ExceptionEmailAlreadyExists;

#[CoversClass(RegisterUserUseCase::class)]
#[CoversClass(PdoUserRepository::class)]
#[CoversClass(PdoStudentRepository::class)]
#[CoversClass(PdoProfessorRepository::class)]
#[CoversClass(PdoClientRepository::class)]
#[CoversClass(User::class)]
#[CoversClass(Student::class)]
#[CoversClass(Professor::class)]
#[CoversClass(Client::class)]
#[CoversClass(Database::class)]
#[CoversClass(ExceptionEmailAlreadyExists::class)]
#[CoversClass(UserFactory::class)]
class RegisterUserUseCaseTest extends TestCase
{
    private array $createdUserIds = [];

    protected function setUp(): void
    {
        parent::setUp();
        putenv('APP_ENV=testing');

        // Reset Database
        $reflection = new ReflectionClass(Database::class);
        $instance = $reflection->getProperty('instance');
        $instance->setAccessible(true);
        $instance->setValue(null, null);
    }

    protected function tearDown(): void
    {
        $repo = new PdoUserRepository();
        foreach ($this->createdUserIds as $id) {
            $repo->delete($id);
        }

        // Reset Database singleton
        $reflection = new ReflectionClass(Database::class);
        $instance = $reflection->getProperty('instance');
        $instance->setAccessible(true);
        $instance->setValue(null, null);

        parent::tearDown();
    }

    private function cleanEmail(string $email): void
    {
        try {
            $db = Database::getInstance();
            $stmt = $db->prepare("DELETE FROM users WHERE email = :email");
            $stmt->execute(['email' => strtolower($email)]);
        } catch (\Exception $e) {
        }
    }

    #[Test]
    public function canRegisterStudent(): void
    {
        $studentRepo = new PdoStudentRepository();
        $professorRepo = new PdoProfessorRepository();
        $clientRepo = new PdoClientRepository();
        $userRepo = new PdoUserRepository();

        $useCase = new RegisterUserUseCase($studentRepo, $professorRepo, $clientRepo, $userRepo);

        $email = 'john.student.reg@test.com';
        $this->cleanEmail($email);

        $data = [
            'first_name' => 'John',
            'last_name' => 'StudentReg',
            'email' => $email,
            'password' => 'password123',
            'phone' => '0600000001',
            'user_type' => 'student',
            'amu_id' => 's_reg_1',
            'year' => 2,
            'td' => 'TD1',
            'tp' => 'TP1'
        ];

        $user = $useCase->execute($data);

        $this->assertNotNull($user);
        $this->createdUserIds[] = $user->getUserId();
        $this->assertInstanceOf(Student::class, $user);
        $this->assertEquals($email, $user->getEmail());

        // Verify via repository
        $fetched = $studentRepo->findById($user->getUserId());
        $this->assertNotNull($fetched);
        $this->assertEquals('s_reg_1', $fetched->getAmuId());
    }

    #[Test]
    public function canRegisterProfessor(): void
    {
        $studentRepo = new PdoStudentRepository();
        $professorRepo = new PdoProfessorRepository();
        $clientRepo = new PdoClientRepository();
        $userRepo = new PdoUserRepository();

        $useCase = new RegisterUserUseCase($studentRepo, $professorRepo, $clientRepo, $userRepo);

        $email = 'prof.reg@test.com';
        $this->cleanEmail($email);

        $data = [
            'first_name' => 'Prof',
            'last_name' => 'Reg',
            'email' => $email,
            'password' => 'password123',
            'phone' => '0600000002',
            'user_type' => 'professor',
            'amu_id' => 'p_reg_1'
        ];

        $user = $useCase->execute($data);

        $this->assertNotNull($user);
        $this->createdUserIds[] = $user->getUserId();
        $this->assertInstanceOf(Professor::class, $user);

        // Verify via repository
        $fetched = $professorRepo->findById($user->getUserId());
        $this->assertNotNull($fetched);
        $this->assertEquals('p_reg_1', $fetched->getAmuId());
    }

    #[Test]
    public function canRegisterClient(): void
    {
        $studentRepo = new PdoStudentRepository();
        $professorRepo = new PdoProfessorRepository();
        $clientRepo = new PdoClientRepository();
        $userRepo = new PdoUserRepository();

        $useCase = new RegisterUserUseCase($studentRepo, $professorRepo, $clientRepo, $userRepo);

        $email = 'client.reg@test.com';
        $this->cleanEmail($email);

        $data = [
            'first_name' => 'Client',
            'last_name' => 'Reg',
            'email' => $email,
            'password' => 'password123',
            'phone' => '0600000003',
            'user_type' => 'client',
            'organisation' => 'Test Corp'
        ];

        $user = $useCase->execute($data);

        $this->assertNotNull($user);
        $this->createdUserIds[] = $user->getUserId();
        $this->assertInstanceOf(Client::class, $user);

        // Verify via repository
        $fetched = $clientRepo->findById($user->getUserId());
        $this->assertNotNull($fetched);
        $this->assertEquals('Test Corp', $fetched->getOrganisation());
    }

    #[Test]
    public function cannotRegisterDuplicateEmail(): void
    {
        $studentRepo = new PdoStudentRepository();
        $professorRepo = new PdoProfessorRepository();
        $clientRepo = new PdoClientRepository();
        $userRepo = new PdoUserRepository();

        $useCase = new RegisterUserUseCase($studentRepo, $professorRepo, $clientRepo, $userRepo);

        $email = 'dup.student@test.com';
        $this->cleanEmail($email);

        $data = [
            'first_name' => 'Dup',
            'last_name' => 'One',
            'email' => $email,
            'password' => 'password123',
            'phone' => '0600000004',
            'user_type' => 'student',
            'amu_id' => 's_dup_1',
            'year' => 1,
            'td' => 'A',
            'tp' => '1'
        ];

        $user = $useCase->execute($data);
        $this->createdUserIds[] = $user->getUserId();

        $this->expectException(ExceptionEmailAlreadyExists::class);

        // Try to register again with same email
        $useCase->execute($data);
    }
}
