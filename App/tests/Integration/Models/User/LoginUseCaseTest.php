<?php

namespace Tests\Integration\Models\User;

use Models\Entity\User\User;
use Models\Repository\User\PdoUserRepository;
use Models\Repository\User\PdoStudentRepository;
use Models\UseCase\User\LoginUseCase;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Core\Includes\Database;
use ReflectionClass;
use Core\Includes\Exception\ExceptionValidation\ExceptionValidationLogin;
use Models\Entity\User\Student;

#[CoversClass(LoginUseCase::class)]
#[CoversClass(PdoUserRepository::class)]
#[CoversClass(User::class)]
#[CoversClass(Student::class)]
#[CoversClass(ExceptionValidationLogin::class)]
class LoginUseCaseTest extends TestCase
{
    private $userRepository;
    private $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userRepository = $this->createMock(PdoUserRepository::class);

        $this->user = new Student([
            'user_id' => 1,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@test.com',
            'phone' => '0600000000',
            'amu_id' => 'u_test_login',
            'year' => 1,
            'td' => 'A',
            'tp' => '1'
        ]);
        $this->user->setPassword('password123');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    #[Test]
    public function canLoginWithValidCredentials(): void
    {
        $this->userRepository->method('findByEmail')
            ->with('john.doe@test.com')
            ->willReturn($this->user);

        $useCase = new LoginUseCase($this->userRepository);
        $authenticatedUser = $useCase->execute('john.doe@test.com', 'password123');

        $this->assertNotNull($authenticatedUser);
        $this->assertEquals(1, $authenticatedUser->getUserId());
        $this->assertEquals('john.doe@test.com', $authenticatedUser->getEmail());
        $this->assertInstanceOf(Student::class, $authenticatedUser);
    }

    #[Test]
    public function cannotLoginWithInvalidPassword(): void
    {
        $this->userRepository->method('findByEmail')
            ->with('john.doe@test.com')
            ->willReturn($this->user);

        $this->expectException(ExceptionValidationLogin::class);

        $useCase = new LoginUseCase($this->userRepository);
        $useCase->execute('john.doe@test.com', 'wrongpassword');
    }

    #[Test]
    public function cannotLoginWithUnknownEmail(): void
    {
        $this->userRepository->method('findByEmail')
            ->with('unknown@test.com')
            ->willReturn(null);

        $this->expectException(ExceptionValidationLogin::class);

        $useCase = new LoginUseCase($this->userRepository);
        $useCase->execute('unknown@test.com', 'password123');
    }
}
