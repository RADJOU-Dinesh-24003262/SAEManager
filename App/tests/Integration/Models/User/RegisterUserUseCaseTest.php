<?php

namespace Tests\Integration\Models\User;

use Models\Entity\User\UserFactory;
use Models\Repository\User\PdoUserRepository;
use Models\Repository\User\PdoPendingRegistrationRepository;
use Models\UseCase\User\RegisterUserUseCase;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Core\Includes\Exception\ExceptionEmailAlreadyExists;
use PHPUnit\Framework\Attributes\UsesClass;
use Core\Models\BaseModel;
use Models\Entity\User\Student;
use Models\Entity\User\Professor;
use Models\Entity\User\Client;
use Models\Entity\User\User;
use Services\TokenService;

#[CoversClass(RegisterUserUseCase::class)]
#[CoversClass(UserFactory::class)]
#[CoversClass(TokenService::class)]
#[CoversClass(ExceptionEmailAlreadyExists::class)]
#[UsesClass(BaseModel::class)]
#[UsesClass(Student::class)]
#[UsesClass(Professor::class)]
#[UsesClass(Client::class)]
#[UsesClass(User::class)]
class RegisterUserUseCaseTest extends TestCase
{
    private $userRepo;
    private $pendingRepo;
    private $useCase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userRepo = $this->createMock(PdoUserRepository::class);
        $this->pendingRepo = $this->createMock(PdoPendingRegistrationRepository::class);

        $this->useCase = new RegisterUserUseCase(
            $this->userRepo,
            $this->pendingRepo
        );
    }

    #[Test]
    public function canRegisterStudent(): void
    {
        $data = [
            'first_name' => 'John',
            'last_name' => 'StudentReg',
            'email' => 'john.student.reg@test.com',
            'password' => 'password123',
            'phone' => '0600000001',
            'user_type' => 'student',
            'amu_id' => 's_reg_1',
            'year' => 2,
            'td' => 'TD1',
            'tp' => 'TP1'
        ];

        $this->userRepo->method('existsByEmail')->willReturn(false);
        $this->pendingRepo->method('existsByEmail')->willReturn(false);
        $this->pendingRepo->method('insert')->willReturn(true);

        $token = $this->useCase->execute($data);

        $this->assertTrue(TokenService::isValidFormat($token));
    }

    #[Test]
    public function canRegisterProfessor(): void
    {
        $data = [
            'first_name' => 'Prof',
            'last_name' => 'Reg',
            'email' => 'prof.reg@test.com',
            'password' => 'password123',
            'phone' => '0600000002',
            'user_type' => 'professor',
            'amu_id' => 'p_reg_1'
        ];

        $this->userRepo->method('existsByEmail')->willReturn(false);
        $this->pendingRepo->method('existsByEmail')->willReturn(false);
        $this->pendingRepo->method('insert')->willReturn(true);

        $token = $this->useCase->execute($data);

        $this->assertTrue(TokenService::isValidFormat($token));
    }

    #[Test]
    public function canRegisterClient(): void
    {
        $data = [
            'first_name' => 'Client',
            'last_name' => 'Reg',
            'email' => 'client.reg@test.com',
            'password' => 'password123',
            'phone' => '0600000003',
            'user_type' => 'client',
            'organisation' => 'Test Corp'
        ];

        $this->userRepo->method('existsByEmail')->willReturn(false);
        $this->pendingRepo->method('existsByEmail')->willReturn(false);
        $this->pendingRepo->method('insert')->willReturn(true);

        $token = $this->useCase->execute($data);

        $this->assertTrue(TokenService::isValidFormat($token));
    }

    #[Test]
    public function cannotRegisterDuplicateEmailInUsers(): void
    {
        $data = [
            'first_name' => 'Dup',
            'last_name' => 'One',
            'email' => 'dup.student@test.com',
            'password' => 'password123',
            'phone' => '0600000004',
            'user_type' => 'student'
        ];

        $this->userRepo->method('existsByEmail')->willReturn(true);

        $this->expectException(ExceptionEmailAlreadyExists::class);

        $this->useCase->execute($data);
    }

    #[Test]
    public function cannotRegisterDuplicateEmailInPending(): void
    {
        $data = [
            'first_name' => 'Dup',
            'last_name' => 'Two',
            'email' => 'dup.student2@test.com',
            'password' => 'password123',
            'phone' => '0600000005',
            'user_type' => 'student'
        ];

        $this->userRepo->method('existsByEmail')->willReturn(false);
        $this->pendingRepo->method('existsByEmail')->willReturn(true);

        $this->expectException(ExceptionEmailAlreadyExists::class);

        $this->useCase->execute($data);
    }
}
