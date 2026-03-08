<?php

namespace Tests\Unit\Core\Utils;

use Core\Utils\RateLimiter;
use Core\Utils\SessionService;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;

#[CoversClass(RateLimiter::class)]
#[UsesClass(SessionService::class)]
class RateLimiterTest extends TestCase
{
    protected function setUp(): void
    {
        // Reset session before each test
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
        @session_start();
        $_SESSION = [];
    }

    public function testCheckAndIncrement(): void
    {
        $action = 'test_action';

        // 1st attempt
        $this->assertTrue(RateLimiter::check($action, 3, 10));
        RateLimiter::increment($action);

        // 2nd attempt
        $this->assertTrue(RateLimiter::check($action, 3, 10));
        RateLimiter::increment($action);

        // 3rd attempt
        $this->assertTrue(RateLimiter::check($action, 3, 10));
        RateLimiter::increment($action);

        // 4th attempt (should fail)
        $this->assertFalse(RateLimiter::check($action, 3, 10));
    }

    public function testClear(): void
    {
        $action = 'test_action_clear';

        RateLimiter::increment($action);
        RateLimiter::increment($action);

        $this->assertTrue(SessionService::has('rate_limit_' . $action));

        RateLimiter::clear($action);

        $this->assertFalse(SessionService::has('rate_limit_' . $action));
    }
}
