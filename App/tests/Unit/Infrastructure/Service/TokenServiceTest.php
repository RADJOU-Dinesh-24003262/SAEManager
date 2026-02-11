<?php

namespace Tests\Unit\Infrastructure\Service;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\DataProvider;
use App\Infrastructure\Service\TokenService;
use App\Domain\Auth\Exception\InvalidTokenException;
use App\Infrastructure\Exception\TokenCreationException;

/**
 * Complete unit tests for TokenService
 *
 * @package Tests\Unit\Utilis
 */
#[CoversClass(TokenService::class)]
#[CoversClass(InvalidTokenException::class)]
class TokenServiceTest extends TestCase
{
    private $tokenRepository;
    private $tokenService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tokenRepository = $this->createMock(\App\Domain\Auth\IRepository\ITokenRepository::class);
        $this->tokenService = new TokenService($this->tokenRepository);
    }

    /**
     * ========================================
     * GENERATION TESTS
     * ========================================
     */
    #[Test]
    public function generateCreatesHexadecimalToken(): void
    {
        $token = TokenService::generateRandomTokenValue();

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
            $tokens[] = TokenService::generateRandomTokenValue();
        }

        $uniqueTokens = array_unique($tokens);
        $this->assertCount(1000, $uniqueTokens, 'All tokens must be unique');
    }

    #[Test]
    public function generateCreatesTokenOfCorrectLength(): void
    {
        $token = TokenService::generateRandomTokenValue();

        $this->assertEquals(64, strlen($token));
    }

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
            try {
                $this->tokenService->validateToken($token);
                $this->fail('Should have thrown InvalidTokenException for: ' . $token);
            } catch (InvalidTokenException $e) {
                $this->assertTrue(true);
            }
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
            $tokens[] = TokenService::generateRandomTokenValue();
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
        $token = TokenService::generateRandomTokenValue();

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
            TokenService::generateRandomTokenValue();
        }

        $duration = microtime(true) - $start;

        // Generating 100 tokens should take less than 100ms
        $this->assertLessThan(0.1, $duration);
    }
}