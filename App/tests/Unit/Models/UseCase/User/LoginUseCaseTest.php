<?php

namespace Tests\Unit\Models\UseCase\User;

use Core\includes\exception\ExceptionValidation\ExceptionValidationLogin;
use Models\Entity\User\User;
use Models\UseCase\User\InterfaceDB\UserInterface;
use Models\UseCase\User\LoginUseCase;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(LoginUseCase::class)]
class LoginUseCaseTest extends TestCase
{
    private UserInterface|MockObject $userRepository;
    private LoginUseCase $loginUseCase;

    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(UserInterface::class);
        $this->loginUseCase = new LoginUseCase($this->userRepository);
    }

    #[Test]
    public function executeReturnsUserOnSuccess(): void
    {
        $email = 'test@example.com';
        $password = 'password123';

        $user = $this->createMock(User::class);
        $user->method('verifyPassword')->with($password)->willReturn(true);
        $user->method('getEmail')->willReturn($email);

        $this->userRepository->expects($this->once())
            ->method('findByEmail')
            ->with($email)
            ->willReturn($user);

        $result = $this->loginUseCase->execute($email, $password);

        $this->assertSame($user, $result);
    }

    #[Test]
    public function executeThrowsExceptionOnUserNotFound(): void
    {
        $email = 'unknown@example.com';
        $password = 'password123';

        $this->userRepository->expects($this->once())
            ->method('findByEmail')
            ->with($email)
            ->willReturn(null);

        $this->expectException(ExceptionValidationLogin::class);

        $this->loginUseCase->execute($email, $password);
    }

    #[Test]
    public function executeThrowsExceptionOnInvalidPassword(): void
    {
        $email = 'test@example.com';
        $password = 'wrongpassword';

        $user = $this->createMock(User::class);
        $user->method('verifyPassword')->with($password)->willReturn(false);

        $this->userRepository->expects($this->once())
            ->method('findByEmail')
            ->with($email)
            ->willReturn($user);

        $this->expectException(ExceptionValidationLogin::class);

        $this->loginUseCase->execute($email, $password);
    }
}
