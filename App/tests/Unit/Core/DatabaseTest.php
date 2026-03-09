<?php

namespace Tests\Unit\Core;

use PDO;
use PDOException;
use PDOStatement;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Core\Includes\Database;

/**
 * Unit tests for Database class
 */
#[CoversClass(Database::class)]
class DatabaseTest extends TestCase
{
    protected function tearDown(): void
    {
        // Reset singleton
        $reflection = new \ReflectionClass(Database::class);
        $instance = $reflection->getProperty('instance');
        $instance->setAccessible(true);
        $instance->setValue(null, null);

        // Reset Config settings
        $configReflection = new \ReflectionClass(\Core\Utils\Config::class);
        $settings = $configReflection->getProperty('settings');
        $settings->setAccessible(true);
        $settings->setValue(null, null);

        parent::tearDown();
    }

    #[Test]
    public function databaseClassExists(): void
    {
        $this->assertTrue(class_exists(Database::class));
    }

    #[Test]
    public function setInstanceAndGetInstanceWorks(): void
    {
        $mockDb = $this->createMock(Database::class);
        Database::setInstance($mockDb);

        $this->assertSame($mockDb, Database::getInstance());
    }

    #[Test]
    public function getInstanceCreatesNewInstanceIfNoneExists(): void
    {
        // Clear instance first to be sure
        $reflection = new \ReflectionClass(Database::class);
        $instance = $reflection->getProperty('instance');
        $instance->setAccessible(true);
        $instance->setValue(null, null);

        // Force Config to return invalid driver to ensure failure
        $configReflection = new \ReflectionClass(\Core\Utils\Config::class);
        $settings = $configReflection->getProperty('settings');
        $settings->setAccessible(true);
        $settings->setValue(null, ['database' => ['driver' => 'non_existent_driver']]);

        // This should fail
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/Database connection failed/');
        Database::getInstance();
    }
}
