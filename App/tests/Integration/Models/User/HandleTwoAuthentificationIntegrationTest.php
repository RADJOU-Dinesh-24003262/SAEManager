<?php

namespace Tests\Integration\Models\User;

use Models\Entity\User\Student;
use Models\Entity\User\User;
use Models\Entity\User\UserFactory;
use Models\UseCase\User\HandleTwoAuthentificationUseCase;
use Models\UseCase\User\InterfaceDB\ClientInterface;
use Models\UseCase\User\InterfaceDB\PendingRegistrationInterface;
use Models\UseCase\User\InterfaceDB\ProfessorInterface;
use Models\UseCase\User\InterfaceDB\StudentInterface;
use Models\UseCase\User\InterfaceDB\TokenRepositoryInterface;
use Models\UseCase\User\RegisterUserUseCase;
use Models\UseCase\User\ValidateTokenUseCase;
use Models\UseCase\User\InterfaceDB\UserInterface;
use Core\Includes\Exception\ExceptionToken\ExceptionInvalidToken;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use Services\TokenService;

/**
 * Integration test: full flow from registration to MFA token handling.
 * Uses mocked repositories to simulate database interactions.
 */
#[CoversClass(HandleTwoAuthentificationUseCase::class)]
#[CoversClass(RegisterUserUseCase::class)]
#[CoversClass(ValidateTokenUseCase::class)]
#[CoversClass(TokenService::class)]
#[UsesClass(UserFactory::class)]
#[UsesClass(Student::class)]
#[UsesClass(User::class)]
#[UsesClass(ExceptionInvalidToken::class)]
class HandleTwoAuthentificationIntegrationTest extends TestCase
{
    private UserInterface|MockObject $userRepo;
    private PendingRegistrationInterface|MockObject $pendingRepo;
    private StudentInterface|MockObject $studentRepo;
    private ProfessorInterface|MockObject $professorRepo;
    private ClientInterface|MockObject $clientRepo;
    private TokenService $tokenService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userRepo = $this->createMock(UserInterface::class);
        $this->pendingRepo = $this->createMock(PendingRegistrationInterface::class);
        $this->studentRepo = $this->createMock(StudentInterface::class);
        $this->professorRepo = $this->createMock(ProfessorInterface::class);
        $this->clientRepo = $this->createMock(ClientInterface::class);
        $this->tokenService = new TokenService();
    }

    /**
     * Full integration scenario:
     * 1. User registers → token created in pending_registrations
     * 2. User clicks email link → HandleTwoAuthentificationUseCase validates token and creates user
     */
    #[Test]
    public function fullRegistrationToMfaFlowCreatesUserSuccessfully(): void
    {
        // --- STEP 1: REGISTRATION ---
        $inputData = [
            'user_type'  => 'student',
            'first_name' => 'Alice',
            'last_name'  => 'Dupont',
            'email'      => 'alice.dupont@etu.univ-amu.fr',
            'phone'      => '0611223344',
            'password'   => 'Secur3P@ss!',
            'amu_id'     => 's24001234',
            'year'       => 2,
            'td'         => 'TD2',
            'tp'         => 'TP2',
        ];

        $this->userRepo->method('existsByEmail')->willReturn(false);
        $this->pendingRepo->method('existsByEmail')->willReturn(false);
        $this->pendingRepo->method('insert')->willReturn(true);

        $registerUseCase = new RegisterUserUseCase($this->userRepo, $this->pendingRepo, $this->tokenService);
        $token = $registerUseCase->execute($inputData);

        // Verify a valid token was generated
        $this->assertTrue(TokenService::isValidFormat($token));

        // --- STEP 2: MFA (email confirmation) ---
        // Simulate the token data that would be stored in pending_registrations
        $pendingData = array_merge($inputData, [
            'hashed_password' => password_hash($inputData['password'], PASSWORD_ARGON2ID),
            'expires_at'      => date('Y-m-d H:i:s', time() + 86400),
            'used'            => false,
        ]);

        // Mock token repository to return the pending registration data
        $tokenRepo = $this->createMock(PendingRegistrationInterface::class);
        $tokenRepo->method('purgeExpired');
        $tokenRepo->method('findByToken')->with($token)->willReturn($pendingData);
        $tokenRepo->method('markAsUsed')->with($token);

        $validateTokenUseCase = new ValidateTokenUseCase($tokenRepo, $this->tokenService);

        // Setup the student being returned after DB insert
        $createdStudent = new Student($pendingData);
        $createdStudent->setUserId(42);

        $this->studentRepo->method('insert')->willReturn(42);
        $this->studentRepo->method('findById')->with(42)->willReturn($createdStudent);

        $handleMfaUseCase = new HandleTwoAuthentificationUseCase(
            $this->studentRepo,
            $this->professorRepo,
            $this->clientRepo,
            $tokenRepo,
            $validateTokenUseCase
        );

        $user = $handleMfaUseCase->execute($token);

        // Assertions
        $this->assertInstanceOf(Student::class, $user);
        $this->assertEquals('alice.dupont@etu.univ-amu.fr', $user->getEmail());
        $this->assertEquals(42, $user->getUserId());
    }

    /**
     * Scenario: the email confirmation token has expired.
     * The MFA step must reject the request with ExceptionInvalidToken.
     */
    #[Test]
    public function mfaFlowRejectsExpiredToken(): void
    {
        $token = TokenService::generate();

        $expiredPendingData = [
            'user_type'       => 'student',
            'first_name'      => 'Bob',
            'last_name'       => 'Martin',
            'email'           => 'bob.martin@etu.univ-amu.fr',
            'phone'           => '0699887766',
            'hashed_password' => password_hash('pass', PASSWORD_ARGON2ID),
            'expires_at'      => date('Y-m-d H:i:s', time() - 3600), // expired 1 hour ago
            'used'            => false,
            'amu_id'          => 's24009999',
            'year'            => 1,
            'td'              => 'TD1',
            'tp'              => 'TP1',
        ];

        $tokenRepo = $this->createMock(PendingRegistrationInterface::class);
        $tokenRepo->method('purgeExpired');
        $tokenRepo->method('findByToken')->willReturn($expiredPendingData);

        $validateTokenUseCase = new ValidateTokenUseCase($tokenRepo, $this->tokenService);

        $handleMfaUseCase = new HandleTwoAuthentificationUseCase(
            $this->studentRepo,
            $this->professorRepo,
            $this->clientRepo,
            $tokenRepo,
            $validateTokenUseCase
        );

        $this->expectException(ExceptionInvalidToken::class);

        $handleMfaUseCase->execute($token);
    }

    /**
     * Scenario: the email confirmation token has already been used.
     * The MFA step must reject the request with ExceptionInvalidToken.
     */
    #[Test]
    public function mfaFlowRejectsAlreadyUsedToken(): void
    {
        $token = TokenService::generate();

        $usedPendingData = [
            'user_type'       => 'student',
            'first_name'      => 'Carol',
            'last_name'       => 'Durand',
            'email'           => 'carol.durand@etu.univ-amu.fr',
            'phone'           => '0612345678',
            'hashed_password' => password_hash('pass', PASSWORD_ARGON2ID),
            'expires_at'      => date('Y-m-d H:i:s', time() + 86400),
            'used'            => true, // already used
            'amu_id'          => 's24008888',
            'year'            => 3,
            'td'              => 'TD3',
            'tp'              => 'TP3',
        ];

        $tokenRepo = $this->createMock(PendingRegistrationInterface::class);
        $tokenRepo->method('purgeExpired');
        $tokenRepo->method('findByToken')->willReturn($usedPendingData);

        $validateTokenUseCase = new ValidateTokenUseCase($tokenRepo, $this->tokenService);

        $handleMfaUseCase = new HandleTwoAuthentificationUseCase(
            $this->studentRepo,
            $this->professorRepo,
            $this->clientRepo,
            $tokenRepo,
            $validateTokenUseCase
        );

        $this->expectException(ExceptionInvalidToken::class);

        $handleMfaUseCase->execute($token);
    }
}
