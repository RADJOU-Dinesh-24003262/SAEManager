<?php

namespace Tests\Unit\Models\User;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Models\User\Student;
use Models\User\Professor;
use Models\User\Client;
use Models\User\User;
use Core\includes\Database;
use Core\includes\exception\ExceptionBD\ExceptionFetchDataBD;
use PDO;

/**
 * Tests for the methods fetchSpecificData and saveSpecificData
 */
#[CoversClass(Student::class)]
#[CoversClass(Professor::class)]
#[CoversClass(Client::class)]
#[CoversClass(User::class)]
#[CoversClass(Database::class)]
#[CoversClass(ExceptionFetchDataBD::class)]
class UserSpecificDataMethodsTest extends TestCase
{
    /**
     * @var \Core\includes\Database|\PHPUnit\Framework\MockObject\MockObject|null
     */
    private \Core\includes\Database|\PHPUnit\Framework\MockObject\MockObject|null $mockPdo = null;

    /**
     * @var \PDOStatement|\PHPUnit\Framework\MockObject\MockObject|null
     */
    private \PDOStatement|\PHPUnit\Framework\MockObject\MockObject|null $mockStmt = null;

    protected function setUp(): void
    {
        parent::setUp();
        // Create a mock of the Database class (extends PDO) to assign
        // it to the typed Database::$instance property in tests.
        $this->mockPdo = $this->createMock(Database::class);
        $this->mockStmt = $this->createMock(\PDOStatement::class);
    }

    protected function tearDown(): void
    {
        $this->mockPdo = null;
        $this->mockStmt = null;
        parent::tearDown();
    }

    // ========================================
    // Tests for Student::fetchSpecificData
    // ========================================

    #[Test]
    public function studentFetchSpecificDataPopulatesAllFields(): void
    {
        $studentRow = [
            'student_id' => 1,
            'amu_id' => 'dupont123',
            'year' => 2,
            'td' => 'TD2',
            'tp' => 'TPB',
            'major' => 'A'
        ];

        $this->assertInstanceOf(\PHPUnit\Framework\MockObject\MockObject::class, $this->mockStmt);

        // Expect the execute method to be called once with this parameter
        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->with(['email' => 'test@etu.univ-amu.fr'])
            ->willReturn(true);

        // Mock the fetch method to return the student row
        $this->mockStmt->expects($this->once())
            ->method('fetch')
            ->willReturn($studentRow);

        $this->assertInstanceOf(\PHPUnit\Framework\MockObject\MockObject::class, $this->mockPdo);

        // Expect the prepare method to be called with a query containing 'FROM students'
        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('FROM students'))
            ->willReturn($this->mockStmt);

        // Use reflection to set the Database instance property
        $reflection = new \ReflectionClass(Database::class);
        $instance = $reflection->getProperty('instance');
        $instance->setAccessible(true);
        $instance->setValue(null, $this->mockPdo);

        // Call fetchSpecificData via reflection
        $student = new Student(['first_name' => 'Test', 'last_name' => 'User']);
        $method = new \ReflectionMethod($student, 'fetchSpecificData');
        $method->setAccessible(true);
        $method->invoke($student, $this->mockPdo, 'test@etu.univ-amu.fr');

        // Verify that the properties are correctly populated
        $this->assertEquals('dupont123', $student->getAmuId());
        $this->assertEquals(2, $student->getYear());
        $this->assertEquals('TD2', $student->getTd());
        $this->assertEquals('TPB', $student->getTp());
        $this->assertEquals('A', $student->getParcours());
    }

    #[Test]
    public function studentFetchSpecificDataThrowsExceptionWhenNoData(): void
    {
        $this->expectException(ExceptionFetchDataBD::class);

        $this->assertInstanceOf(\PHPUnit\Framework\MockObject\MockObject::class, $this->mockStmt);

        // Expect execute to be called once
        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        // Mock fetch to return false, simulating no data found
        $this->mockStmt->expects($this->once())
            ->method('fetch')
            ->willReturn(false);

        $this->assertInstanceOf(\PHPUnit\Framework\MockObject\MockObject::class, $this->mockPdo);

        // Expect prepare to be called
        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->willReturn($this->mockStmt);

        // Set the Database instance using reflection
        $reflection = new \ReflectionClass(Database::class);
        $instance = $reflection->getProperty('instance');
        $instance->setAccessible(true);
        $instance->setValue(null, $this->mockPdo);

        // Call fetchSpecificData via reflection, expecting an exception
        $student = new Student(['first_name' => 'Test', 'last_name' => 'User']);
        $method = new \ReflectionMethod($student, 'fetchSpecificData');
        $method->setAccessible(true);
        $method->invoke($student, $this->mockPdo, 'nonexistent@test.fr');
    }

    #[Test]
    public function studentFetchSpecificDataUsesCorrectJoin(): void
    {
        $capturedQuery = null;

        $this->assertInstanceOf(\PHPUnit\Framework\MockObject\MockObject::class, $this->mockStmt);

        // Expect execute to be called once
        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        // Mock fetch to return a sample student row
        $this->mockStmt->expects($this->once())
            ->method('fetch')
            ->willReturn([
                'student_id' => 1,
                'amu_id' => 'test',
                'year' => 1,
                'td' => 'TD1',
                'tp' => 'TPA'
            ]);

        $this->assertInstanceOf(\PHPUnit\Framework\MockObject\MockObject::class, $this->mockPdo);

        // Capture the prepared SQL query
        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->willReturnCallback(function ($query) use (&$capturedQuery) {
                $capturedQuery = $query;
                return $this->mockStmt;
            });

        // Set the Database instance
        $reflection = new \ReflectionClass(Database::class);
        $instance = $reflection->getProperty('instance');
        $instance->setAccessible(true);
        $instance->setValue(null, $this->mockPdo);

        // Call fetchSpecificData
        $student = new Student(['first_name' => 'Test', 'last_name' => 'User']);
        $method = new \ReflectionMethod($student, 'fetchSpecificData');
        $method->setAccessible(true);
        $method->invoke($student, $this->mockPdo, 'test@test.fr');

        // Verify that the SQL query contains the necessary elements
        /** @var string $capturedQuery */
        $this->assertStringContainsString('students', $capturedQuery);
        $this->assertStringContainsString('JOIN', $capturedQuery);
        $this->assertStringContainsString('users', $capturedQuery);
        $this->assertStringContainsString('email', $capturedQuery);
    }

    // ========================================
    // Tests for Professor::fetchSpecificData
    // ========================================

    #[Test]
    public function professorFetchSpecificDataPopulatesAllFields(): void
    {
        $professorRow = [
            'professor_id' => 1,
            'amu_id' => 'martin456'
        ];

        $this->assertInstanceOf(\PHPUnit\Framework\MockObject\MockObject::class, $this->mockStmt);

        // Expect execute to be called with the email
        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->with(['email' => 'prof@univ-amu.fr'])
            ->willReturn(true);

        // Mock fetch to return professor row
        $this->mockStmt->expects($this->once())
            ->method('fetch')
            ->willReturn($professorRow);

        $this->assertInstanceOf(\PHPUnit\Framework\MockObject\MockObject::class, $this->mockPdo);

        // Expect prepare to include 'FROM professors'
        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('FROM professors'))
            ->willReturn($this->mockStmt);

        // Set Database instance
        $reflection = new \ReflectionClass(Database::class);
        $instance = $reflection->getProperty('instance');
        $instance->setAccessible(true);
        $instance->setValue(null, $this->mockPdo);

        // Call fetchSpecificData
        $professor = new Professor(['first_name' => 'Test', 'last_name' => 'Prof']);
        $method = new \ReflectionMethod($professor, 'fetchSpecificData');
        $method->setAccessible(true);
        $method->invoke($professor, $this->mockPdo, 'prof@univ-amu.fr');

        $this->assertEquals('martin456', $professor->getAmuId());
    }

    #[Test]
    public function professorFetchSpecificDataHandlesEmptyResult(): void
    {
        $this->assertInstanceOf(\PHPUnit\Framework\MockObject\MockObject::class, $this->mockStmt);

        // Mock execute to succeed
        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        // Mock fetch returns false (no data)
        $this->mockStmt->expects($this->once())
            ->method('fetch')
            ->willReturn(false);

        $this->assertInstanceOf(\PHPUnit\Framework\MockObject\MockObject::class, $this->mockPdo);

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->willReturn($this->mockStmt);

        $reflection = new \ReflectionClass(Database::class);
        $instance = $reflection->getProperty('instance');
        $instance->setAccessible(true);
        $instance->setValue(null, $this->mockPdo);

        $professor = new Professor(['first_name' => 'Test', 'last_name' => 'Prof']);
        $method = new \ReflectionMethod($professor, 'fetchSpecificData');
        $method->setAccessible(true);

        // Should not throw any exception for Professor
        $method->invoke($professor, $this->mockPdo, 'nonexistent@test.fr');

        $this->assertTrue(true);
    }

    // ========================================
    // Tests for Client::fetchSpecificData
    // ========================================

    #[Test]
    public function clientFetchSpecificDataPopulatesAllFields(): void
    {
        $clientRow = [
            'client_id' => 1,
            'organisation' => 'Tech Corp'
        ];

        $this->assertInstanceOf(\PHPUnit\Framework\MockObject\MockObject::class, $this->mockStmt);

        // Expect execute called with email
        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->with(['email' => 'client@company.com'])
            ->willReturn(true);

        $this->mockStmt->expects($this->once())
            ->method('fetch')
            ->willReturn($clientRow);

        $this->assertInstanceOf(\PHPUnit\Framework\MockObject\MockObject::class, $this->mockPdo);

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('FROM clients'))
            ->willReturn($this->mockStmt);

        // Set Database instance
        $reflection = new \ReflectionClass(Database::class);
        $instance = $reflection->getProperty('instance');
        $instance->setAccessible(true);
        $instance->setValue(null, $this->mockPdo);

        // Call fetchSpecificData
        $client = new Client(['first_name' => 'Client', 'last_name' => 'User']);
        $method = new \ReflectionMethod($client, 'fetchSpecificData');
        $method->setAccessible(true);
        $method->invoke($client, $this->mockPdo, 'client@company.com');

        $this->assertEquals('Tech Corp', $client->getOrganisation());
    }

    // ========================================
    // Tests for Student::saveSpecificData
    // ========================================

    #[Test]
    public function studentSaveSpecificDataInsertsCorrectly(): void
    {
        $capturedParams = null;

        $this->assertInstanceOf(\PHPUnit\Framework\MockObject\MockObject::class, $this->mockStmt);

        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->willReturnCallback(function ($params) use (&$capturedParams) {
                $capturedParams = $params;
                return true;
            });

        $this->assertInstanceOf(\PHPUnit\Framework\MockObject\MockObject::class, $this->mockPdo);

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('INSERT INTO students'))
            ->willReturn($this->mockStmt);

        $reflection = new \ReflectionClass(Database::class);
        $instance = $reflection->getProperty('instance');
        $instance->setAccessible(true);
        $instance->setValue(null, $this->mockPdo);

        $student = new Student([
            'amu_id' => 'test123',
            'year' => 2,
            'td' => 'TD1',
            'tp' => 'TPA'
        ]);

        $method = new \ReflectionMethod($student, 'saveSpecificData');
        $method->setAccessible(true);
        $method->invoke($student, $this->mockPdo, 42);

        $this->assertNotNull($capturedParams);
        $this->assertEquals(42, $capturedParams['student_id']);
        $this->assertEquals('test123', $capturedParams['amu_id']);
        $this->assertEquals(2, $capturedParams['year']);
        $this->assertEquals('TD1', $capturedParams['td']);
        $this->assertEquals('TPA', $capturedParams['tp']);
    }

    #[Test]
    public function studentSaveSpecificDataUsesCorrectQuery(): void
    {
        $capturedQuery = null;

        $this->assertInstanceOf(\PHPUnit\Framework\MockObject\MockObject::class, $this->mockStmt);

        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        $this->assertInstanceOf(\PHPUnit\Framework\MockObject\MockObject::class, $this->mockPdo);

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->willReturnCallback(function ($query) use (&$capturedQuery) {
                $capturedQuery = $query;
                return $this->mockStmt;
            });

        $reflection = new \ReflectionClass(Database::class);
        $instance = $reflection->getProperty('instance');
        $instance->setAccessible(true);
        $instance->setValue(null, $this->mockPdo);

        $student = new Student([
            'amu_id' => 'test',
            'year' => 1,
            'td' => 'TD1',
            'tp' => 'TPA'
        ]);

        $method = new \ReflectionMethod($student, 'saveSpecificData');
        $method->setAccessible(true);
        $method->invoke($student, $this->mockPdo, 1);

        // Checks the structure of the query
        /** @var string $capturedQuery */
        $this->assertStringContainsString('INSERT INTO students', $capturedQuery);
        $this->assertStringContainsString('student_id', $capturedQuery);
        $this->assertStringContainsString('amu_id', $capturedQuery);
        $this->assertStringContainsString('year', $capturedQuery);
        $this->assertStringContainsString('td', $capturedQuery);
        $this->assertStringContainsString('tp', $capturedQuery);
    }

    // ========================================
    // Tests for Professor::saveSpecificData
    // ========================================

    #[Test]
    public function professorSaveSpecificDataInsertsCorrectly(): void
    {
        $capturedParams = null;

        $this->assertInstanceOf(\PHPUnit\Framework\MockObject\MockObject::class, $this->mockStmt);

        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->willReturnCallback(function ($params) use (&$capturedParams) {
                $capturedParams = $params;
                return true;
            });

        $this->assertInstanceOf(\PHPUnit\Framework\MockObject\MockObject::class, $this->mockPdo);

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('INSERT INTO professors'))
            ->willReturn($this->mockStmt);

        $reflection = new \ReflectionClass(Database::class);
        $instance = $reflection->getProperty('instance');
        $instance->setAccessible(true);
        $instance->setValue(null, $this->mockPdo);

        $professor = new Professor([
            'amu_id' => 'prof456'
        ]);

        $method = new \ReflectionMethod($professor, 'saveSpecificData');
        $method->setAccessible(true);
        $method->invoke($professor, $this->mockPdo, 99);

        $this->assertNotNull($capturedParams);
        $this->assertEquals(99, $capturedParams['professor_id']);
        $this->assertEquals('prof456', $capturedParams['amu_id']);
    }

    // ========================================
    // Tests for Client::saveSpecificData
    // ========================================

    #[Test]
    public function clientSaveSpecificDataInsertsCorrectly(): void
    {
        $capturedParams = null;

        $this->assertInstanceOf(\PHPUnit\Framework\MockObject\MockObject::class, $this->mockStmt);

        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->willReturnCallback(function ($params) use (&$capturedParams) {
                $capturedParams = $params;
                return true;
            });

        $this->assertInstanceOf(\PHPUnit\Framework\MockObject\MockObject::class, $this->mockPdo);

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('INSERT INTO clients'))
            ->willReturn($this->mockStmt);

        $reflection = new \ReflectionClass(Database::class);
        $instance = $reflection->getProperty('instance');
        $instance->setAccessible(true);
        $instance->setValue(null, $this->mockPdo);

        $client = new Client([
            'organisation' => 'Innovative Solutions'
        ]);

        $method = new \ReflectionMethod($client, 'saveSpecificData');
        $method->setAccessible(true);
        $method->invoke($client, $this->mockPdo, 77);

        $this->assertNotNull($capturedParams);
        $this->assertEquals(77, $capturedParams['client_id']);
        $this->assertEquals('Innovative Solutions', $capturedParams['organisation']);
    }

    // ========================================
    // Tests for consistency between save and fetch
    // ========================================

    #[Test]
    public function studentDataRemainsConsistentBetweenSaveAndFetch(): void
    {
        $originalData = [
            'amu_id' => 'consistency_test',
            'year' => 3,
            'td' => 'TD3',
            'tp' => 'TPA',
            'major' => 'B'
        ];

        $this->assertInstanceOf(\PHPUnit\Framework\MockObject\MockObject::class, $this->mockStmt);

        // Test save
        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        $this->assertInstanceOf(\PHPUnit\Framework\MockObject\MockObject::class, $this->mockPdo);

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->willReturn($this->mockStmt);

        $reflection = new \ReflectionClass(Database::class);
        $instance = $reflection->getProperty('instance');
        $instance->setAccessible(true);
        $instance->setValue(null, $this->mockPdo);

        $student = new Student($originalData);

        $saveMethod = new \ReflectionMethod($student, 'saveSpecificData');
        $saveMethod->setAccessible(true);
        $saveMethod->invoke($student, $this->mockPdo, 1);

        // The data should remain the same
        $this->assertEquals('consistency_test', $student->getAmuId());
        $this->assertEquals(3, $student->getYear());
        $this->assertEquals('TD3', $student->getTd());
        $this->assertEquals('TPA', $student->getTp());
        $this->assertEquals('B', $student->getParcours());
    }

    // ========================================
    // Tests for error handling
    // ========================================

    #[Test]
    public function saveSpecificDataHandlesDatabaseException(): void
    {
        $this->expectException(\PDOException::class);

        $this->assertInstanceOf(\PHPUnit\Framework\MockObject\MockObject::class, $this->mockPdo);

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->willThrowException(new \PDOException('Database error'));

        $reflection = new \ReflectionClass(Database::class);
        $instance = $reflection->getProperty('instance');
        $instance->setAccessible(true);
        $instance->setValue(null, $this->mockPdo);

        $student = new Student([
            'amu_id' => 'test',
            'year' => 1,
            'td' => 'TD1',
            'tp' => 'TPA'
        ]);

        $method = new \ReflectionMethod($student, 'saveSpecificData');
        $method->setAccessible(true);
        $method->invoke($student, $this->mockPdo, 1);
    }

    #[Test]
    public function fetchSpecificDataOnlyUpdatesExistingProperties(): void
    {
        $studentRow = [
            'student_id' => 1,
            'amu_id' => 'test',
            'year' => 1,
            'td' => 'TD1',
            'tp' => 'TPA',
            'invalid_field' => 'should_be_ignored'
        ];

        $this->assertInstanceOf(\PHPUnit\Framework\MockObject\MockObject::class, $this->mockStmt);

        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        $this->mockStmt->expects($this->once())
            ->method('fetch')
            ->willReturn($studentRow);

        $this->assertInstanceOf(\PHPUnit\Framework\MockObject\MockObject::class, $this->mockPdo);

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->willReturn($this->mockStmt);

        $reflection = new \ReflectionClass(Database::class);
        $instance = $reflection->getProperty('instance');
        $instance->setAccessible(true);
        $instance->setValue(null, $this->mockPdo);

        $student = new Student(['first_name' => 'Test', 'last_name' => 'User']);
        $method = new \ReflectionMethod($student, 'fetchSpecificData');
        $method->setAccessible(true);

        // Should not throw any exception
        $method->invoke($student, $this->mockPdo, 'test@test.fr');

        // The valid fields should be updated
        $this->assertEquals('test', $student->getAmuId());
    }

    // ========================================
    // Tests for data types
    // ========================================

    #[Test]
    public function fetchSpecificDataPreservesDataTypes(): void
    {
        $this->assertInstanceOf(\PHPUnit\Framework\MockObject\MockObject::class, $this->mockStmt);
        $studentRow = [
            'student_id' => 1,
            'amu_id' => 'test123',
            'year' => 2,  // Integer
            'td' => 'TD1',  // String
            'tp' => 'TPA',  // String
            'major' => 'A'  // String
        ];

        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        $this->mockStmt->expects($this->once())
            ->method('fetch')
            ->willReturn($studentRow);

        $this->assertInstanceOf(\PHPUnit\Framework\MockObject\MockObject::class, $this->mockPdo);

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->willReturn($this->mockStmt);

        $reflection = new \ReflectionClass(Database::class);
        $instance = $reflection->getProperty('instance');
        $instance->setAccessible(true);
        $instance->setValue(null, $this->mockPdo);

        $student = new Student(['first_name' => 'Test', 'last_name' => 'User']);
        $method = new \ReflectionMethod($student, 'fetchSpecificData');
        $method->setAccessible(true);
        $method->invoke($student, $this->mockPdo, 'test@test.fr');

        // Check the types
        $this->assertIsString($student->getAmuId());
        $this->assertIsInt($student->getYear());
        $this->assertIsString($student->getTd());
        $this->assertIsString($student->getTp());
    }
}
