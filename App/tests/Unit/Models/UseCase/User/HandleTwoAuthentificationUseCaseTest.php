<?php

namespace Tests\Unit\Models\UseCase\User;

use Core\Includes\Exception\ExceptionToken\ExceptionInvalidToken;
use Models\Entity\User\Student;
use Models\Entity\User\User;
use Models\Entity\User\UserFactory;
use Models\UseCase\User\HandleTwoAuthentificationUseCase;
use Models\UseCase\User\InterfaceDB\ClientInterface;
use Models\UseCase\User\InterfaceDB\PendingRegistrationInterface;
use Models\UseCase\User\InterfaceDB\ProfessorInterface;
use Models\UseCase\User\InterfaceDB\StudentInterface;
use Models\UseCase\User\ValidateTokenUseCase;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;

#[CoversClass(HandleTwoAuthentificationUseCase::class)]
#[UsesClass(UserFactory::class)]
#[UsesClass(Student::class)]
#[UsesClass(User::class)]
#[UsesClass(ExceptionInvalidToken::class)]
class HandleTwoAuthentificationUseCaseTest extends TestCase
{
    private StudentInterface|MockObject $studentRepository;
    private ProfessorInterface|MockObject $professorRepository;
    private ClientInterface|MockObject $clientRepository;
    private PendingRegistrationInterface|MockObject $pendingRegistrationRepository;
    private ValidateTokenUseCase|MockObject $validateTokenUseCase;
    private HandleTwoAuthentificationUseCase $handleTwoAuthentificationUseCase;

    protected function setUp(): void
    {
        $this->studentRepository = $this->createMock(StudentInterface::class);
        $this->professorRepository = $this->createMock(ProfessorInterface::class);
        $this->clientRepository = $this->createMock(ClientInterface::class);
        $this->pendingRegistrationRepository = $this->createMock(PendingRegistrationInterface::class);
        $this->validateTokenUseCase = $this->createMock(ValidateTokenUseCase::class);

        $this->handleTwoAuthentificationUseCase = new HandleTwoAuthentificationUseCase(
            $this->studentRepository,
            $this->professorRepository,
            $this->clientRepository,
            $this->pendingRegistrationRepository,
            $this->validateTokenUseCase
        );
    }

    #[Test]
    public function executeCreatesStudentAndCleansUpTokenSuccessfuly(): void
    {
        $token = 'valid_token_string';
        $registrationData = [
            'user_type' => 'student',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@etu.univ-amu.fr',
            'password' => 'hashed_password',
            'phone' => '0612345678',
            'amu_id' => 's12345678',
            'year' => 1,
            'td' => 'TD1',
            'tp' => 'TP1'
        ];

        // 1. Mock ValidateTokenUseCase to return valid registration data
        $this->validateTokenUseCase->expects($this->once())
            ->method('execute')
            ->with($token, 'confirmation')
            ->willReturn($registrationData);

        // 2. Mock student repository to correctly insert the user
        $this->studentRepository->expects($this->once())
            ->method('insert')
            ->with($this->isInstanceOf(Student::class))
            ->willReturn(1); // Return an insert ID (int)

        // 3. Mock student repository to fetch the newly created user
        $expectedUser = new Student($registrationData);
        $expectedUser->setUserId(1);

        $this->studentRepository->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($expectedUser);

        // 4. Mock PendingRegistration cleanup
        $this->pendingRegistrationRepository->expects($this->once())
            ->method('markAsUsed')
            ->with($token);

        $this->pendingRegistrationRepository->expects($this->once())
            ->method('purgeExpired');

        // Execute the Use Case
        $resultUser = $this->handleTwoAuthentificationUseCase->execute($token);

        $this->assertInstanceOf(Student::class, $resultUser);
        $this->assertEquals('john.doe@etu.univ-amu.fr', $resultUser->getEmail());
        $this->assertEquals(1, $resultUser->getUserId());
    }

    #[Test]
    public function executeThrowsExceptionWhenTokenIsInvalid(): void
    {
        $token = 'invalid_token';

        $this->validateTokenUseCase->expects($this->once())
            ->method('execute')
            ->with($token, 'confirmation')
            ->willThrowException(new ExceptionInvalidToken("Token invalide ou non trouvé."));

        $this->expectException(ExceptionInvalidToken::class);
        $this->expectExceptionMessage("Token invalide ou non trouvé.");

        $this->handleTwoAuthentificationUseCase->execute($token);
    }

    #[Test]
    public function executeThrowsExceptionWhenUserTypeIsUnknown(): void
    {
        $token = 'valid_token_string';
        $registrationData = [
            'user_type' => 'alien', // Unknown type
            'first_name' => 'Zorg',
            'last_name' => 'Blorg',
            'email' => 'zorg.blorg@mars.com',
            'password' => 'hashed_password',
            'phone' => '0000000000'
        ];

        $this->validateTokenUseCase->expects($this->once())
            ->method('execute')
            ->with($token, 'confirmation')
            ->willReturn($registrationData);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Type d'utilisateur inconnu : alien");

        $this->handleTwoAuthentificationUseCase->execute($token);
    }

    #[Test]
    public function executeThrowsExceptionWhenInsertFails(): void
    {
        $token = 'valid_token_string';
        $registrationData = [
            'user_type' => 'student',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@etu.univ-amu.fr',
            'password' => 'hashed_password',
            'phone' => '0612345678',
            'amu_id' => 's12345678',
            'year' => 1,
            'td' => 'TD1',
            'tp' => 'TP1'
        ];

        $this->validateTokenUseCase->expects($this->once())
            ->method('execute')
            ->with($token, 'confirmation')
            ->willReturn($registrationData);

        $this->studentRepository->expects($this->once())
            ->method('insert')
            ->willReturn(false); // Fails to insert

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Échec de la création de l'utilisateur.");

        $this->handleTwoAuthentificationUseCase->execute($token);
    }

    #[Test]
    public function executeThrowsExceptionWhenUserNotFoundAfterCreation(): void
    {
        $token = 'valid_token_string';
        $registrationData = [
            'user_type' => 'student',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@etu.univ-amu.fr',
            'password' => 'hashed_password',
            'phone' => '0612345678',
            'amu_id' => 's12345678',
            'year' => 1,
            'td' => 'TD1',
            'tp' => 'TP1'
        ];

        $this->validateTokenUseCase->expects($this->once())
            ->method('execute')
            ->with($token, 'confirmation')
            ->willReturn($registrationData);

        $this->studentRepository->expects($this->once())
            ->method('insert')
            ->willReturn(1); // Returns an ID correctly

        $this->pendingRegistrationRepository->expects($this->once())
            ->method('markAsUsed');

        $this->pendingRegistrationRepository->expects($this->once())
            ->method('purgeExpired');

        $this->studentRepository->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn(null); // But findById returns null/false

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Utilisateur non trouvé après création.");

        $this->handleTwoAuthentificationUseCase->execute($token);
    }
}
