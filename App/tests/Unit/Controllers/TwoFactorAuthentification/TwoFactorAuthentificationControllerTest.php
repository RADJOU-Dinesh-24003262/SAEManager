<?php

namespace Tests\Unit\Controllers\TwoFactorAuthentification;

use Controllers\TwoFactorAuthentification\TwoFactorAuthentificationController;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Unit tests for TwoFactorAuthentificationController::support()
 */
#[CoversClass(TwoFactorAuthentificationController::class)]
class TwoFactorAuthentificationControllerTest extends TestCase
{
    // -------------------------------------------------------------------------
    // Tests for support() method
    // -------------------------------------------------------------------------

    #[Test]
    public function supportReturnsTrueForValidPathAndMethod(): void
    {
        $this->assertTrue(
            TwoFactorAuthentificationController::support('/two-factor-authentification', 'GET')
        );
    }

    #[Test]
    public function supportReturnsFalseForWrongPath(): void
    {
        $this->assertFalse(
            TwoFactorAuthentificationController::support('/register', 'GET')
        );
        $this->assertFalse(
            TwoFactorAuthentificationController::support('/two-factor-authentification-wrong', 'GET')
        );
        $this->assertFalse(
            TwoFactorAuthentificationController::support('/', 'GET')
        );
    }

    #[Test]
    public function supportReturnsFalseForWrongMethod(): void
    {
        $this->assertFalse(
            TwoFactorAuthentificationController::support('/two-factor-authentification', 'POST')
        );
        $this->assertFalse(
            TwoFactorAuthentificationController::support('/two-factor-authentification', 'PUT')
        );
        $this->assertFalse(
            TwoFactorAuthentificationController::support('/two-factor-authentification', 'DELETE')
        );
    }

    #[Test]
    public function supportReturnsFalseForBothWrongPathAndMethod(): void
    {
        $this->assertFalse(
            TwoFactorAuthentificationController::support('/register', 'POST')
        );
    }
}
