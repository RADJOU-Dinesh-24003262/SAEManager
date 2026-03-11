<?php

namespace Tests\Unit\Services;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Services\TokenService;

#[CoversClass(TokenService::class)]
class TokenServiceTest extends TestCase
{
    private TokenService $service;

    protected function setUp(): void
    {
        $this->service = new TokenService();
    }

    #[Test]
    public function tokenIsGeneratedAndHasCorrectLength(): void
    {
        $token = $this->service->generate();

        $this->assertIsString($token);
        // bin2hex(random_bytes(32)) is 64 characters long
        $this->assertEquals(64, strlen($token));
        // Ensure it contains only hex characters
        $this->assertMatchesRegularExpression('/^[a-f0-9]+$/', $token);
    }

    #[Test]
    public function tokensAreUnique(): void
    {
        $token1 = $this->service->generate();
        $token2 = $this->service->generate();

        $this->assertNotEquals($token1, $token2);
    }

    #[Test]
    public function isValidFormatDetectsCorrectTokens(): void
    {
        $validToken = $this->service->generate();
        $invalidToken1 = 'short';
        $invalidToken2 = str_repeat('z', 64); // Not hex

        $this->assertTrue($this->service->isValidFormat($validToken));
        $this->assertFalse($this->service->isValidFormat($invalidToken1));
        $this->assertFalse($this->service->isValidFormat($invalidToken2));
    }

    #[Test]
    public function isExpiredCorrectlyIdentifiesOldDates(): void
    {
        $pastDate = date('Y-m-d H:i:s', time() - 3600);
        $futureDate = date('Y-m-d H:i:s', time() + 3600);

        $this->assertTrue($this->service->isExpired($pastDate));
        $this->assertFalse($this->service->isExpired($futureDate));
    }
}
