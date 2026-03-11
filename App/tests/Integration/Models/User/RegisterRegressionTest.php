<?php

namespace Tests\Integration\Models\User;

use Models\Repository\User\PdoUserRepository;
use Models\Repository\User\PdoPendingRegistrationRepository;
use Models\UseCase\User\RegisterUserUseCase;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\CoversClass;
use Services\TokenService;

#[CoversClass(RegisterUserUseCase::class)]
#[CoversClass(TokenService::class)]
class RegisterRegressionTest extends TestCase
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
    public function testAMUIdIsExtractedProperlyBeforeInsert(): void
    {
        $email = 'regression.student@test.com';
        $data = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => $email,
            'password' => 'password123',
            'phone' => '0600000000',
            'user_type' => 'student',
            'amu_id' => 's_reg_fail',
            'year' => 2,
            'td' => 'TD1',
            'tp' => 'TP1'
        ];

        $this->userRepo->method('existsByEmail')->willReturn(false);
        $this->pendingRepo->method('existsByEmail')->willReturn(false);

        $this->pendingRepo->expects($this->once())
            ->method('insert')
            ->with(
                $this->isType('string'), // Token is now dynamically generated
                'John',
                'Doe',
                $email,
                '0600000000',
                $this->anything(), // Handled by password_hash
                'student',
                $this->anything(), // expiresAt
                's_reg_fail',      // AMU ID must be passed here
                'TD1',
                'TP1',
                null,
                2,
                null
            )->willReturn(true);

        $token = $this->useCase->execute($data);

        $this->assertTrue(TokenService::isValidFormat($token));
    }
}
