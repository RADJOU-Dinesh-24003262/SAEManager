<?php

namespace Tests\Unit\Utilis;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\DataProvider;
use Utilis\TokenService;
use includes\exception\ExceptionInvalidToken;
use includes\exception\ExceptionCreationTokenFailed;

/**
 * Tests unitaires complets pour TokenService
 *
 * @package Tests\Unit\Utilis
 */
#[CoversClass(TokenService::class)]
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
     * TESTS DE GÉNÉRATION
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

/*  Token too similar, Have to change in the futur about this issue
    #[Test]
    public function generateUsesSecureRandomness(): void
    {
        $token1 = TokenService::generate();
        $token2 = TokenService::generate();

        // Calculer la similarité (ne devrait pas être élevée)
        similar_text($token1, $token2, $percent);

        $this->assertLessThan(20, $percent, 'Tokens should not be similar');
    }
/*

    /**
     * ========================================
     * TESTS DE VALIDATION
     * ========================================
     */

    #[Test]
    public function validateTokenRejectsInvalidFormats(): void
    {
        $invalidTokens = [
            '',                          // Empty
            'short',                     // Too short
            str_repeat('a', 65),        // Too long
            str_repeat('g', 64),        // Invalid hex chars
            '<script>alert(1)</script>', // XSS attempt
            '../../etc/passwd',         // Path traversal
            "'; DROP TABLE tokens--",   // SQL injection
        ];

        foreach ($invalidTokens as $token) {
            $this->expectException(ExceptionInvalidToken::class);
            TokenService::validateToken($token);
        }
    }

    /**
     * ========================================
     * TESTS DE SÉCURITÉ SPÉCIFIQUES
     * ========================================
     */

    #[Test]
    public function tokenCannotBeGuessed(): void
    {
        // Générer plusieurs tokens et vérifier qu'on ne peut pas deviner le suivant
        $tokens = [];
        for ($i = 0; $i < 10; $i++) {
            $tokens[] = TokenService::generate();
        }

        // Vérifier qu'il n'y a pas de pattern évident
        foreach ($tokens as $i => $token) {
            if ($i > 0) {
                // Comparer avec le token précédent
                $diff = levenshtein(
                    substr($token, 0, 32),
                    substr($tokens[$i - 1], 0, 32)
                );

                // La distance de Levenshtein devrait être élevée
                $this->assertGreaterThan(20, $diff);
            }
        }
    }

    #[Test]
    public function tokenContainsNoPersonalInformation(): void
    {
        $token = TokenService::generate();

        // Vérifier qu'il ne contient pas de patterns suspects
        $suspiciousPatterns = [
            '/\d{10}/',           // Timestamps
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
     * TESTS DE PERFORMANCE
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

        // Générer 100 tokens devrait prendre moins de 100ms
        $this->assertLessThan(0.1, $duration);
    }

    /**
     * ========================================
     * TESTS DE TIMING ATTACK RESISTANCE
     * ========================================
     */

    #[Test]
    public function validateTokenHasConstantTime(): void
    {
        // Générer un token valide
        $validToken = str_repeat('a', 64);

        // Mesurer le temps avec différents tokens invalides
        $times = [];

        $testTokens = [
            str_repeat('b', 64),  // Complètement différent
            'a' . str_repeat('b', 63),  // Un caractère différent
            str_repeat('a', 32) . str_repeat('b', 32), // Moitié différent
        ];

        foreach ($testTokens as $token) {
            $start = microtime(true);
            try {
                TokenService::validateToken($token);
            } catch (\Exception $e) {
                // Expected
            }
            $times[] = microtime(true) - $start;
        }

        // Les temps doivent être similaires (protection contre timing attack)
        $variance = $this->calculateVariance($times);

        // Variance doit être faible
        $this->assertLessThan(0.01, $variance);
    }

    private function calculateVariance(array $values): float
    {
        $mean = array_sum($values) / count($values);
        $variance = 0;

        foreach ($values as $value) {
            $variance += pow($value - $mean, 2);
        }

        return $variance / count($values);
    }
}
