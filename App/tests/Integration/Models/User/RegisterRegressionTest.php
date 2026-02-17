<?php

namespace Tests\Integration\Models\User;

use Models\Entity\User\Student;
use Models\Repository\User\PdoUserRepository;
use Models\Repository\User\PdoStudentRepository;
use Models\Repository\User\PdoProfessorRepository;
use Models\Repository\User\PdoClientRepository;
use Models\UseCase\User\RegisterUserUseCase;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Core\includes\Database;
use ReflectionClass;

#[CoversClass(RegisterUserUseCase::class)]
#[CoversClass(Student::class)]
#[CoversClass(PdoUserRepository::class)]
#[CoversClass(PdoStudentRepository::class)]
#[CoversClass(PdoProfessorRepository::class)]
#[CoversClass(PdoClientRepository::class)]
#[CoversClass(Database::class)]
class RegisterRegressionTest extends TestCase
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
    public function controllerUsageFailsToInsertStudentData(): void
    {
        // Now mimics the FIXED RegisterPost.php which passes all repos
        $studentRepo = new PdoStudentRepository();
        $professorRepo = new PdoProfessorRepository();
        $clientRepo = new PdoClientRepository();
        $userRepo = new PdoUserRepository();

        $useCase = new RegisterUserUseCase(
            $studentRepo,
            $professorRepo,
            $clientRepo,
            $userRepo
        );

        $email = 'regression.student@test.com';
        $this->cleanEmail($email);

        $data = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => $email,
            'password' => 'password123',
            'phone' => '0600000000',
            'user_type' => 'student',
            'amu_id' => 's_reg_fail', // This should be saved!
            'year' => 2,
            'td' => 'TD1',
            'tp' => 'TP1'
        ];

        // This should now execute and insert into BOTH users and students tables
        $user = $useCase->execute($data);
        $this->createdUserIds[] = $user->getUserId();

        // Verify via PdoStudentRepository (which joins)
        $fetched = $studentRepo->findById($user->getUserId());

        $this->assertInstanceOf(Student::class, $fetched);

        // This assertion should PASS now
        $this->assertNotNull($fetched->getAmuId(), "AMU ID should not be null");
        $this->assertEquals('s_reg_fail', $fetched->getAmuId(), "AMU ID should match");
    }
}
