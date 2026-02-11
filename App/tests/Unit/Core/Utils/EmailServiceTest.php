<?php

namespace Tests\Unit\Core\Utils;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use App\Infrastructure\Service\EmailService;
use App\Domain\User\Exception\EmailAlreadyExistsException;

/**
 * Unit tests for EmailService
 */
#[CoversClass(EmailService::class)]
class EmailServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $_SERVER['HTTP_HOST'] = 'localhost';
        $_SERVER['HTTPS'] = 'on';
    }

    protected function tearDown(): void
    {
        unset($_SERVER['HTTP_HOST']);
        unset($_SERVER['HTTPS']);
        parent::tearDown();
    }

    // ========================================
    // Structure and configuration tests
    // ========================================
    #[Test]
    public function emailServiceClassExists(): void
    {
        $this->assertTrue(class_exists(EmailService::class));
    }

    #[Test]
    public function emailServiceHasRequiredMethods(): void
    {
        $reflection = new \ReflectionClass(EmailService::class);

        $this->assertTrue($reflection->hasMethod('send'));
        $this->assertTrue($reflection->hasMethod('setSender'));

        $method = $reflection->getMethod('send');
        $this->assertTrue($method->isStatic());
        $this->assertTrue($method->isPublic());
    }
}
