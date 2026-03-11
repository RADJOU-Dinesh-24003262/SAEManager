<?php

namespace Tests\Integration\Models\User;

use Core\Utils\SessionService;
use Models\Entity\User\User;
use Models\Repository\User\PdoUserRepository;
use Models\UseCase\User\DeleteUserUseCase;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(DeleteUserUseCase::class)]
#[CoversClass(PdoUserRepository::class)]
#[CoversClass(SessionService::class)]
class DeleteUserUseCaseTest extends TestCase
{
    private $userRepository;
    private $userId = 1;

    protected function setUp(): void
    {
        parent::setUp();
        SessionService::start();

        $this->userRepository = $this->createMock(PdoUserRepository::class);
    }

    #[Test]
    public function canDeleteUserById(): void
    {
        $this->userRepository->expects($this->once())
            ->method('delete')
            ->with($this->userId)
            ->willReturn(true);

        $this->userRepository->method('findById')
            ->with($this->userId)
            ->willReturn(null);

        $useCase = new DeleteUserUseCase($this->userRepository);
        $useCase->execute($this->userId);

        $this->assertNull($this->userRepository->findById($this->userId));
    }

    #[Test]
    public function canDeleteUserByEmail(): void
    {
        $email = 'john.delete@test.com';
        $mockUser = $this->createMock(User::class);
        $mockUser->method('getUserId')->willReturn($this->userId);

        $this->userRepository->method('findByEmail')
            ->with($email)
            ->willReturn($mockUser);

        $this->userRepository->expects($this->once())
            ->method('delete')
            ->with($this->userId)
            ->willReturn(true);

        $this->userRepository->method('findById')
            ->with($this->userId)
            ->willReturn(null);

        $useCase = new DeleteUserUseCase($this->userRepository);
        $useCase->executeByEmail($email);

        $this->assertNull($this->userRepository->findById($this->userId));
    }
}
