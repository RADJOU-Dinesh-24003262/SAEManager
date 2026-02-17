<?php

namespace Tests\Integration\Models\User;

use Models\Entity\User\User;
use Models\Repository\User\PdoUserRepository;
use Models\Repository\User\PdoStudentRepository;
use Models\UseCase\User\LoginUseCase;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Core\includes\Database;
use ReflectionClass;
use Core\includes\exception\ExceptionValidation\ExceptionValidationLogin;
use Models\Entity\User\Student;

#[CoversClass(LoginUseCase::class)]
#[CoversClass(PdoUserRepository::class)]
#[CoversClass(User::class)]
#[CoversClass(PdoStudentRepository::class)]
#[CoversClass(Student::class)]
#[CoversClass(Database::class)]
#[CoversClass(ExceptionValidationLogin::class)]
class LoginUseCaseTest extends TestCase
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

        // Create a test user using PdoStudentRepository to ensure valid user
        $studentRepo = new PdoStudentRepository();
        $user = new Student([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@test.com',
            'phone' => '0600000000',
            'amu_id' => 'u_test_login',
            'year' => 1,
            'td' => 'A',
            'tp' => '1'
        ]);
        $user->setPassword('password123');

        // Clean up if exists (optional, but good practice)
        try {
            $db = Database::getInstance();
            $stmt = $db->prepare("DELETE FROM users WHERE email = 'john.doe@test.com'");
            $stmt->execute();
        } catch (\Exception $e) {
        }

        $createdUserId = $studentRepo->insert($user);
        $this->user = $studentRepo->findById($createdUserId);
        // In case create returns object without properties updated (unlikely but possible)
        // Re-fetch to be sure
        // But let's trust create for now, or use getUserId()
        if ($this->user instanceof User) {
             $this->userId = $this->user->getUserId();
        }
    }

    protected function tearDown(): void
    {
        if ($this->userId) {
            $this->userRepository->delete($this->userId);
        }

        // Reset Database singleton
        $reflection = new ReflectionClass(Database::class);
        $instance = $reflection->getProperty('instance');
        $instance->setAccessible(true);
        $instance->setValue(null, null);

        parent::tearDown();
    }

    #[Test]
    public function canLoginWithValidCredentials(): void
    {
        $useCase = new LoginUseCase($this->userRepository);
        $authenticatedUser = $useCase->execute('john.doe@test.com', 'password123');

        $this->assertNotNull($authenticatedUser);
        $this->assertEquals($this->user->getUserId(), $authenticatedUser->getUserId());
        $this->assertEquals('john.doe@test.com', $authenticatedUser->getEmail());
        $this->assertInstanceOf(Student::class, $authenticatedUser);
    }

    #[Test]
    public function cannotLoginWithInvalidPassword(): void
    {
        $this->expectException(ExceptionValidationLogin::class);

        $useCase = new LoginUseCase($this->userRepository);
        $useCase->execute('john.doe@test.com', 'wrongpassword');
    }

    #[Test]
    public function cannotLoginWithUnknownEmail(): void
    {
        $this->expectException(ExceptionValidationLogin::class);

        $useCase = new LoginUseCase($this->userRepository);
        $useCase->execute('unknown@test.com', 'password123');
    }
}
