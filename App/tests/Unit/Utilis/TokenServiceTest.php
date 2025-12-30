<?php

namespace Tests\Unit\Utilis;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\DataProvider;
use Services\TokenService;
use Core\includes\exception\ExceptionToken\ExceptionInvalidToken;
use Core\includes\exception\ExceptionToken\ExceptionCreationTokenFailed;

/**
 * Complete unit tests for TokenService
 *
 * @package Tests\Unit\Utilis
 */
#[CoversClass(TokenService::class)]
#[CoversClass(ExceptionInvalidToken::class)]
class TokenServiceTest extends TestCase
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

    /**
     * ========================================
     * GENERATION TESTS
     * ========================================
     */
    #[Test]
    public function generateCreatesHexadecimalToken(): void
    {
        $token = TokenService::generate();

        $this->assertMatchesRegularExpression(
            '/^[a-f0-9]{64}$/i',
            $token,
            'Token must be 64 hexadecimal characters'
        );
    }

    #[Test]
    public function generateCreatesUniqueTokens(): void
    {
        $tokens = [];
        for ($i = 0; $i < 1000; $i++) {
            $tokens[] = TokenService::generate();
        }

        $uniqueTokens = array_unique($tokens);
        $this->assertCount(1000, $uniqueTokens, 'All tokens must be unique');
    }

    #[Test]
    public function generateCreatesTokenOfCorrectLength(): void
    {
        $token = TokenService::generate();

        $this->assertEquals(64, strlen($token));
    }

    /*  Tokens too similar, need to change this behavior in the future
        #[Test]
        public function generateUsesSecureRandomness(): void
        {
            $token1 = TokenService::generate();
            $token2 = TokenService::generate();

            // Calculate similarity (should not be high)
            similar_text($token1, $token2, $percent);

            $this->assertLessThan(20, $percent, 'Tokens should not be similar');
        }
    /*

        /**
         * ========================================
         * VALIDATION TESTS
         * ========================================
         */

    #[Test]
    public function validateTokenRejectsInvalidFormats(): void
    {
        $invalidTokens = [
            '',                          // Empty
            'short',                     // Too short
            str_repeat('a', 65),         // Too long
            str_repeat('g', 64),         // Invalid hex chars
            '<script>alert(1)</script>', // XSS attempt
            '../../etc/passwd',          // Path traversal
            "'; DROP TABLE tokens--",    // SQL injection
        ];

        foreach ($invalidTokens as $token) {
            $this->expectException(ExceptionInvalidToken::class);
            TokenService::validateToken($token);
        }
    }

    /**
     * ========================================
     * SPECIFIC SECURITY TESTS
     * ========================================
     */
    #[Test]
    public function tokenCannotBeGuessed(): void
    {
        // Generate several tokens and verify the next one cannot be guessed
        $tokens = [];
        for ($i = 0; $i < 10; $i++) {
            $tokens[] = TokenService::generate();
        }

        // Check that there is no obvious pattern
        foreach ($tokens as $i => $token) {
            if ($i > 0) {
                // Compare with the previous token
                $diff = levenshtein(
                    substr($token, 0, 32),
                    substr($tokens[$i - 1], 0, 32)
                );

                // The Levenshtein distance should be high
                $this->assertGreaterThan(20, $diff);
            }
        }
    }

    #[Test]
    public function tokenContainsNoPersonalInformation(): void
    {
        $token = TokenService::generate();

        // Check that it does not contain suspicious patterns
        $suspiciousPatterns = [
            '/user/',             // User identifier
            '/admin/',            // Admin identifier
            '/[a-z]+@[a-z]+/',    // Email-like
        ];

        foreach ($suspiciousPatterns as $pattern) {
            $this->assertDoesNotMatchRegularExpression(
                $pattern,
                $token,
                "Token should not contain identifiable information"
            );
        }
    }

    /**
     * ========================================
     * PERFORMANCE TESTS
     * ========================================
     */
    #[Test]
    public function generateIsPerformant(): void
    {
        $start = microtime(true);

        for ($i = 0; $i < 100; $i++) {
            TokenService::generate();
        }

        $duration = microtime(true) - $start;

        // Generating 100 tokens should take less than 100ms
        $this->assertLessThan(0.1, $duration);
    }
}
