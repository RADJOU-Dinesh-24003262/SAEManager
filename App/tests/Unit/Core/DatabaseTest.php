<?php

namespace Tests\Unit\Core;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Core\includes\Database;

/**
 * Unit tests for Database class
 */
#[CoversClass(Database::class)]
class DatabaseTest extends TestCase
{
    #[Test]
    public function databaseClassExists(): void
    {
        $this->assertTrue(class_exists(Database::class));
    }

    #[Test]
    public function getInstanceReturnsPDOInstance(): void
    {
        try {
            $db = Database::getInstance();

            $this->assertInstanceOf(\PDO::class, $db);
        } catch (\PDOException $e) {
            $this->markTestSkipped('Database connection not available: ' . $e->getMessage());
        }
    }

    #[Test]
    public function getInstanceReturnsSameInstance(): void
    {
        try {
            $db1 = Database::getInstance();
            $db2 = Database::getInstance();

            $this->assertSame($db1, $db2, 'Database should implement singleton pattern');
        } catch (\PDOException $e) {
            $this->markTestSkipped('Database connection not available');
        }
    }

    #[Test]
    public function databaseUsesCorrectErrorMode(): void
    {
        try {
            $db = Database::getInstance();

            $errorMode = $db->getAttribute(\PDO::ATTR_ERRMODE);

            $this->assertEquals(
                \PDO::ERRMODE_EXCEPTION,
                $errorMode,
                'Database should use ERRMODE_EXCEPTION'
            );
        } catch (\PDOException $e) {
            $this->markTestSkipped('Database connection not available');
        }
    }

    #[Test]
    public function databaseConnectionCanExecuteQueries(): void
    {
        try {
            $db = Database::getInstance();

            // Simple query that should work on any database
            $stmt = $db->query('SELECT 1 as test');
            /** @var \PDOStatement $stmt */
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);

            $this->assertEquals(['test' => 1], $result);
        } catch (\PDOException $e) {
            $this->markTestSkipped('Database connection not available');
        }
    }

    #[Test]
    public function databaseCanPrepareStatements(): void
    {
        try {
            $db = Database::getInstance();

            $stmt = $db->prepare('SELECT ? as value');
            $this->assertInstanceOf(\PDOStatement::class, $stmt);

            $stmt->execute([42]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);

            $this->assertEquals(['value' => 42], $result);
        } catch (\PDOException $e) {
            $this->markTestSkipped('Database connection not available');
        }
    }

    #[Test]
    public function preparedStatementsProtectAgainstSQLInjection(): void
    {
        try {
            $db = Database::getInstance();

            $maliciousInput = "'; DROP TABLE users--";

            $stmt = $db->prepare('SELECT ? as input');
            $stmt->execute([$maliciousInput]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);

            // Should return the string as-is, not execute as SQL
            $this->assertEquals(['input' => $maliciousInput], $result);
        } catch (\PDOException $e) {
            $this->markTestSkipped('Database connection not available');
        }
    }

    #[Test]
    public function databaseHandlesSpecialCharacters(): void
    {
        try {
            $db = Database::getInstance();

            $specialChars = "Test with 'quotes' and \"double quotes\" and <tags>";

            $stmt = $db->prepare('SELECT ? as text');
            $stmt->execute([$specialChars]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);

            $this->assertEquals(['text' => $specialChars], $result);
        } catch (\PDOException $e) {
            $this->markTestSkipped('Database connection not available');
        }
    }

    #[Test]
    public function databaseHandlesUnicodeCharacters(): void
    {
        try {
            $db = Database::getInstance();

            $unicode = "Tëst wîth ûnicode: 你好 🎉";

            $stmt = $db->prepare('SELECT ? as text');
            $stmt->execute([$unicode]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);

            $this->assertEquals(['text' => $unicode], $result);
        } catch (\PDOException $e) {
            $this->markTestSkipped('Database connection not available');
        }
    }

    #[Test]
    public function databaseConnectionIsReusable(): void
    {
        try {
            $db = Database::getInstance();

            // Execute multiple queries
            $stmt1 = $db->query('SELECT 1 as test1');
            /** @var \PDOStatement $stmt1 */
            $result1 = $stmt1->fetch(\PDO::FETCH_ASSOC);

            $stmt2 = $db->query('SELECT 2 as test2');
            /** @var \PDOStatement $stmt2 */
            $result2 = $stmt2->fetch(\PDO::FETCH_ASSOC);

            $this->assertEquals(['test1' => 1], $result1);
            $this->assertEquals(['test2' => 2], $result2);
        } catch (\PDOException $e) {
            $this->markTestSkipped('Database connection not available');
        }
    }

    #[Test]
    public function databaseThrowsExceptionOnInvalidQuery(): void
    {
        try {
            $db = Database::getInstance();

            $this->expectException(\PDOException::class);

            $db->query('INVALID SQL SYNTAX HERE');
        } catch (\PDOException $e) {
            if (strpos($e->getMessage(), 'could not find driver') !== false) {
                $this->markTestSkipped('Database connection not available');
            }
            throw $e;
        }
    }

    #[Test]
    public function databaseHandlesNullValues(): void
    {
        try {
            $db = Database::getInstance();

            $stmt = $db->prepare('SELECT ? as nullable');
            $stmt->execute([null]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);

            $this->assertNull($result['nullable']);
        } catch (\PDOException $e) {
            $this->markTestSkipped('Database connection not available');
        }
    }

    #[Test]
    public function multipleInstanceCallsReturnSameConnection(): void
    {
        try {
            $instances = [];
            for ($i = 0; $i < 10; $i++) {
                $instances[] = Database::getInstance();
            }

            // All should be the same instance
            foreach ($instances as $instance) {
                $this->assertSame($instances[0], $instance);
            }
        } catch (\PDOException $e) {
            $this->markTestSkipped('Database connection not available');
        }
    }
}
