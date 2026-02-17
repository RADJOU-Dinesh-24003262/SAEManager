<?php

namespace Tests\Unit\Models\UseCase\User;

use Core\includes\exception\ExceptionEmailAlreadyExists;
use InvalidArgumentException;
use Models\Entity\User\Student;
use Models\Entity\User\User;
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
class RegisterUserUseCaseTest extends TestCase
{
    private UserInterface|MockObject $userRepository;
    private RegisterUserUseCase $registerUseCase;

    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(UserInterface::class);
        $this->registerUseCase = new RegisterUserUseCase($this->userRepository);
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

        $this->userRepository->expects($this->once())
            ->method('create')
            ->willReturnCallback(function (User $user) {
                return $user;
            });

        $user = $this->registerUseCase->execute($data);

        $this->assertInstanceOf(Student::class, $user);
        $this->assertEquals('John', $user->getFirstName());
        // Check email domain appending logic
        $this->assertStringContainsString('@etu.univ-amu.fr', $user->getEmail());
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

    #[Test]
    public function executeThrowsExceptionForInvalidUserType(): void
    {
        $data = [
            'user_type' => 'alien',
            'first_name' => 'John'
        ];

        $this->expectException(InvalidArgumentException::class);

        $this->registerUseCase->execute($data);
    }
}
