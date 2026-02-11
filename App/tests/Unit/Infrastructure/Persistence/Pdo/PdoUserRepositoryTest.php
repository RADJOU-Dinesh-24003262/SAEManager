<?php

namespace Tests\Unit\Infrastructure\Persistence\Pdo;

use App\Domain\User\Client;
use App\Domain\User\Professor;
use App\Domain\User\Student;
use App\Infrastructure\Persistence\Pdo\PdoUserRepository;
use Core\Database\Database;
use PDO;
use PDOStatement;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use ReflectionClass;

/**
 * Tests for PdoUserRepository
 */
#[CoversClass(PdoUserRepository::class)]
class PdoUserRepositoryTest extends TestCase
{
    private Database|MockObject|null $mockPdo = null;
    private PDOStatement|MockObject|null $mockStmt = null;
    private PdoUserRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Mock PDO
        $this->mockPdo = $this->createMock(Database::class);
        $this->mockStmt = $this->createMock(PDOStatement::class);

        // Inject Mock PDO into Database singleton
        $reflection = new ReflectionClass(Database::class);
        $instance = $reflection->getProperty('instance');
        $instance->setAccessible(true);
        $instance->setValue(null, $this->mockPdo);

        $this->repository = new PdoUserRepository();
    }

    protected function tearDown(): void
    {
        $this->mockPdo = null;
        $this->mockStmt = null;
        // Reset singleton? Ideally yes, but Reflection handles it for next test
        parent::tearDown();
    }

    #[Test]
    public function findByEmailReturnsStudentWithJoinedData(): void
    {
        $email = 'student@test.fr';
        
        // Mock data from JOIN query
        $row = [
            'first_name' => 'Jean',
            'last_name' => 'Dupont',
            'email' => $email,
            'user_type' => '0', // Student
            'phone' => '0612345678',
            'user_id' => 1,
            // Joined fields
            'student_id' => 1,
            'student_amu_id' => 'std123',
            'year' => 2,
            'td' => 'TD1',
            'tp' => 'TPA',
            'major' => 'Info',
            // Other Joined fields are null
            'professor_id' => null,
            'professor_amu_id' => null,
            'client_id' => null,
            'organisation' => null
        ];

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('LEFT JOIN students'))
            ->willReturn($this->mockStmt);

        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->with(['email' => $email]);

        $this->mockStmt->expects($this->once())
            ->method('fetch')
            ->willReturn($row);

        $user = $this->repository->findByEmail($email);

        $this->assertInstanceOf(Student::class, $user);
        $this->assertEquals('Jean', $user->getFirstName());
        $this->assertEquals('std123', $user->getAmuId());
        $this->assertEquals(2, $user->getYear());
    }

    #[Test]
    public function findByEmailReturnsProfessorWithJoinedData(): void
    {
        $email = 'prof@test.fr';
        
        // Mock data from JOIN query
        $row = [
            'first_name' => 'Marie',
            'last_name' => 'Curie',
            'email' => $email,
            'user_type' => '1', // Professor
            'phone' => '0612345678',
            'user_id' => 2,
            // Joined fields
            'student_id' => null,
            'student_amu_id' => null,
            'year' => null,
            'professor_id' => 2,
            'professor_amu_id' => 'prof123',
            'client_id' => null,
            'organisation' => null
        ];

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->willReturn($this->mockStmt);

        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->with(['email' => $email]);

        $this->mockStmt->expects($this->once())
            ->method('fetch')
            ->willReturn($row);

        $user = $this->repository->findByEmail($email);

        $this->assertInstanceOf(Professor::class, $user);
        $this->assertEquals('Marie', $user->getFirstName());
        $this->assertEquals('prof123', $user->getAmuId());
    }

    #[Test]
    public function findByEmailReturnsClientWithJoinedData(): void
    {
        $email = 'client@test.fr';
        
        $row = [
            'first_name' => 'Pierre',
            'last_name' => 'Durand',
            'email' => $email,
            'user_type' => '2', // Client
            'phone' => '0612345678',
            'user_id' => 3,
            'student_id' => null,
            'professor_id' => null,
            'client_id' => 3,
            'organisation' => 'TechCorp'
        ];

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->willReturn($this->mockStmt);

        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->with(['email' => $email]);

        $this->mockStmt->expects($this->once())
            ->method('fetch')
            ->willReturn($row);

        $user = $this->repository->findByEmail($email);

        $this->assertInstanceOf(Client::class, $user);
        $this->assertEquals('TechCorp', $user->getOrganisation());
    }

    #[Test]
    public function findByEmailReturnsNullWhenNotFound(): void
    {
        $email = 'unknown@test.fr';

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->willReturn($this->mockStmt);

        $this->mockStmt->expects($this->once())
            ->method('fetch')
            ->willReturn(false);

        $user = $this->repository->findByEmail($email);

        $this->assertNull($user);
    }

    #[Test]
    public function existsByEmailReturnsTrueIfFound(): void
    {
        $email = 'known@test.fr';

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('SELECT COUNT(*)'))
            ->willReturn($this->mockStmt);

        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->with(['email' => $email]);
        
        $this->mockStmt->expects($this->once())
            ->method('fetchColumn')
            ->willReturn(1);

        $this->assertTrue($this->repository->existsByEmail($email));
    }
}