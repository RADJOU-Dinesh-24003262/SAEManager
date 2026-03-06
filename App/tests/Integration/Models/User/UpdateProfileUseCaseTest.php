<?php

namespace Tests\Integration\Models\User;

use Models\Entity\User\User;
use Models\Repository\User\PdoUserRepository;
use Models\Repository\User\PdoStudentRepository;
use Models\UseCase\User\UpdateProfileUseCase;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Core\Includes\Database;
use ReflectionClass;
use Models\Entity\User\Student;

#[CoversClass(UpdateProfileUseCase::class)]
#[CoversClass(PdoUserRepository::class)]
#[CoversClass(User::class)]
#[CoversClass(Student::class)]
#[CoversClass(Database::class)]
#[CoversClass(PdoStudentRepository::class)]
class UpdateProfileUseCaseTest extends TestCase
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
            'last_name' => 'UpdateProfile',
            'email' => 'john.update@test.com',
            'phone' => '0600000000',
            'amu_id' => 'u_test_upd',
            'year' => 1,
            'td' => 'A',
            'tp' => '1'
        ]);
        $user->setPassword('password123');

        // Clean up
        try {
            $db = Database::getInstance();
            $db->exec("DELETE FROM users WHERE email = 'john.update@test.com'");
        } catch (\Exception $e) {
        }

        $createdUserId = $studentRepo->insert($user);
        $this->user = $studentRepo->findById($createdUserId);
        $this->userId = $createdUserId;
    }

    protected function tearDown(): void
    {
        if ($this->userId) {
            $this->userRepository->delete($this->userId);
        }

        $reflection = new ReflectionClass(Database::class);
        $instance = $reflection->getProperty('instance');
        $instance->setAccessible(true);
        $instance->setValue(null, null);

        parent::tearDown();
    }

    #[Test]
    public function canUpdatePhoneNumber(): void
    {
        $useCase = new UpdateProfileUseCase($this->userRepository);

        $newPhone = '0700000000';
        $updatedUser = $useCase->execute($this->userId, ['phone' => $newPhone]);

        $this->assertEquals($newPhone, $updatedUser->getPhone());

        // Verify in DB
        $storedUser = $this->userRepository->findById($this->userId);
        $this->assertEquals($newPhone, $storedUser->getPhone());
    }
}
