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
    private $userRepository;
    private $userId = 1;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userRepository = $this->createMock(PdoUserRepository::class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    #[Test]
    public function canUpdatePhoneNumber(): void
    {
        $newPhone = '0700000000';
        $mockUser = new Student([
            'user_id' => $this->userId,
            'first_name' => 'John',
            'last_name' => 'UpdateProfile',
            'email' => 'john.update@test.com',
            'phone' => '0600000000',
            'amu_id' => 'u_test_upd',
            'year' => 1,
            'td' => 'A',
            'tp' => '1'
        ]);

        $this->userRepository->method('findById')
            ->with($this->userId)
            ->willReturn($mockUser);

        $this->userRepository->method('update')
            ->willReturn(true);

        $useCase = new UpdateProfileUseCase($this->userRepository);
        $updatedUser = $useCase->execute($this->userId, ['phone' => $newPhone]);

        $this->assertEquals($newPhone, $updatedUser->getPhone());
    }
}
