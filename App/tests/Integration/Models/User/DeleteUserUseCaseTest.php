<?php

namespace Tests\Integration\Models\User;

use Models\Entity\User\User;
use Models\Repository\User\PdoUserRepository;
use Models\Repository\User\PdoStudentRepository;
use Models\UseCase\User\DeleteUserUseCase;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Core\includes\Database;
use ReflectionClass;
use Models\Entity\User\Student;

#[CoversClass(DeleteUserUseCase::class)]
#[CoversClass(PdoUserRepository::class)]
#[CoversClass(User::class)]
#[CoversClass(Student::class)]
#[CoversClass(Database::class)]
#[CoversClass(PdoStudentRepository::class)]
class DeleteUserUseCaseTest extends TestCase
{
    private ?User $user;
    private ?PdoUserRepository $userRepository;
    private ?int $userId = null;

    protected function setUp(): void
    {
        parent::setUp();
        putenv('APP_ENV=testing');

        // Reset Database
        $reflection = new ReflectionClass(Database::class);
        $instance = $reflection->getProperty('instance');
        $instance->setAccessible(true);
        $instance->setValue(null, null);

        $this->userRepository = new PdoUserRepository();

        // Create a test user
        $studentRepo = new PdoStudentRepository();
        $user = new Student([
            'first_name' => 'John',
            'last_name' => 'DeleteMe',
            'email' => 'john.delete@test.com',
            'phone' => '0600000000',
            'amu_id' => 'u_test_del',
            'year' => 1,
            'td' => 'A',
            'tp' => '1'
        ]);
        $user->setPassword('password123');

        try {
            $db = Database::getInstance();
            $db->exec("DELETE FROM users WHERE email = 'john.delete@test.com'");
        } catch (\Exception $e) {
        }

        $createdUserId = $studentRepo->insert($user);
        $this->user = $studentRepo->findById($createdUserId);
        $this->userId = $createdUserId;
    }

    protected function tearDown(): void
    {
        // Try deleting again if test failed or to clean up
        if ($this->userId) {
            try {
                $this->userRepository->delete($this->userId);
            } catch (\Exception $e) {
            }
        }

        $reflection = new ReflectionClass(Database::class);
        $instance = $reflection->getProperty('instance');
        $instance->setAccessible(true);
        $instance->setValue(null, null);

        parent::tearDown();
    }

    #[Test]
    public function canDeleteUserById(): void
    {
        $useCase = new DeleteUserUseCase($this->userRepository);
        $useCase->execute($this->userId);

        $this->assertNull($this->userRepository->findById($this->userId));
        $this->userId = null; // Mark as deleted so tearDown doesn't complain
    }

    #[Test]
    public function canDeleteUserByEmail(): void
    {
        $useCase = new DeleteUserUseCase($this->userRepository);
        $useCase->executeByEmail('john.delete@test.com');

        $this->assertNull($this->userRepository->findById($this->userId));
        $this->userId = null;
    }
}
