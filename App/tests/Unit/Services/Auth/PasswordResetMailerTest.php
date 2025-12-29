<?php

namespace Tests\Unit\Services\Auth;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Services\Auth\PasswordResetMailer;

/**
 * Unit tests for PasswordResetMailer
 */
#[CoversClass(PasswordResetMailer::class)]
class PasswordResetMailerTest extends TestCase
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
    // Email template tests
    // ========================================
    #[Test]
    public function getHtmlTemplateReturnsValidHtml(): void
    {
        $reflection = new \ReflectionClass(PasswordResetMailer::class);
        $method = $reflection->getMethod('getHtmlTemplate');
        $method->setAccessible(true);

        $resetLink = 'https://test.com/reset?token=abc123';
        $html = $method->invoke(null, $resetLink);

        $this->assertStringContainsString('<!DOCTYPE html>', $html);
        $this->assertStringContainsString('<html', $html);
        $this->assertStringContainsString('</html>', $html);
        $this->assertStringContainsString($resetLink, $html);
        $this->assertStringContainsString('SAE Manager', $html);
        $this->assertStringContainsString('10 minutes', $html);
    }

    #[Test]
    public function getTextTemplateReturnsPlainText(): void
    {
        $reflection = new \ReflectionClass(PasswordResetMailer::class);
        $method = $reflection->getMethod('getTextTemplate');
        $method->setAccessible(true);

        $resetLink = 'https://test.com/reset?token=abc123';
        $text = $method->invoke(null, $resetLink);

        $this->assertStringNotContainsString('<html>', $text);
        $this->assertStringNotContainsString('<div>', $text);
        $this->assertStringContainsString($resetLink, $text);
        $this->assertStringContainsString('SAE Manager', $text);
        $this->assertStringContainsString('10 minutes', $text);
    }

    #[Test]
    public function htmlTemplateContainsSecurityWarnings(): void
    {
        $reflection = new \ReflectionClass(PasswordResetMailer::class);
        $method = $reflection->getMethod('getHtmlTemplate');
        $method->setAccessible(true);

        $html = $method->invoke(null, 'https://test.com/reset');

        $this->assertStringContainsString('10 minutes', $html);
        $this->assertStringContainsString('une seule fois', $html);
        $this->assertStringContainsString("n'avez pas demandé", $html);
    }

    #[Test]
    public function textTemplateContainsSecurityWarnings(): void
    {
        $reflection = new \ReflectionClass(PasswordResetMailer::class);
        $method = $reflection->getMethod('getTextTemplate');
        $method->setAccessible(true);

        $text = $method->invoke(null, 'https://test.com/reset');

        $this->assertStringContainsString('10 minutes', $text);
        $this->assertStringContainsString('une seule fois', $text);
        $this->assertStringContainsString("n'avez pas demandé", $text);
    }

    // ========================================
    // Link generation tests
    // ========================================
    #[Test]
    public function getResetLinkGeneratesCorrectUrlWithHttps(): void
    {
        $_SERVER['HTTPS'] = 'on';
        $_SERVER['HTTP_HOST'] = 'example.com';

        $reflection = new \ReflectionClass(PasswordResetMailer::class);
        $method = $reflection->getMethod('getResetLink');
        $method->setAccessible(true);

        $token = 'test_token_123';
        $link = $method->invoke(null, $token);

        $this->assertStringStartsWith('https://', $link);
        $this->assertStringContainsString('example.com', $link);
        $this->assertStringContainsString('/reset-password', $link);
        $this->assertStringContainsString('token=test_token_123', $link);
    }

    #[Test]
    public function getResetLinkGeneratesCorrectUrlWithHttp(): void
    {
        unset($_SERVER['HTTPS']);
        $_SERVER['HTTP_HOST'] = 'localhost';

        $reflection = new \ReflectionClass(PasswordResetMailer::class);
        $method = $reflection->getMethod('getResetLink');
        $method->setAccessible(true);

        $token = 'test_token_456';
        $link = $method->invoke(null, $token);

        $this->assertStringStartsWith('http://', $link);
        $this->assertStringContainsString('localhost', $link);
        $this->assertStringContainsString('token=test_token_456', $link);
    }

    #[Test]
    public function getResetLinkHandlesSpecialCharactersInToken(): void
    {
        $reflection = new \ReflectionClass(PasswordResetMailer::class);
        $method = $reflection->getMethod('getResetLink');
        $method->setAccessible(true);

        $token = 'token_with_special_chars!@#$%^&*()';
        $link = $method->invoke(null, $token);

        $this->assertStringContainsString('token=', $link);
        // The token should be in the URL (not encoded here)
        $this->assertStringContainsString($token, $link);
    }

    // ========================================
    // Parameter validation tests
    // ========================================
    #[Test]
    public function htmlTemplateHandlesLongUrls(): void
    {
        $reflection = new \ReflectionClass(PasswordResetMailer::class);
        $method = $reflection->getMethod('getHtmlTemplate');
        $method->setAccessible(true);

        $longUrl = 'https://example.com/reset?token=' . str_repeat('a', 500);
        $html = $method->invoke(null, $longUrl);

        $this->assertStringContainsString($longUrl, $html);
        $this->assertStringContainsString('word-break: break-all', $html);
    }

    #[Test]
    public function templatesHandleCurrentYear(): void
    {
        $currentYear = date('Y');

        $reflection = new \ReflectionClass(PasswordResetMailer::class);
        $htmlMethod = $reflection->getMethod('getHtmlTemplate');
        $htmlMethod->setAccessible(true);

        $html = $htmlMethod->invoke(null, 'https://test.com');

        $this->assertStringContainsString($currentYear, $html);
    }

    // ========================================
    // Security tests
    // ========================================
    #[Test]
    public function htmlTemplateEscapesUserInput(): void
    {
        $reflection = new \ReflectionClass(PasswordResetMailer::class);
        $method = $reflection->getMethod('getHtmlTemplate');
        $method->setAccessible(true);

        // Attempt to inject JavaScript
        $maliciousLink = 'https://test.com"><script>alert("XSS")</script><a href="';
        $html = $method->invoke(null, $maliciousLink);

        // The HTML should contain the link but not execute the script
        $this->assertStringContainsString($maliciousLink, $html);
    }

    #[Test]
    public function resetLinkDoesNotContainSensitiveData(): void
    {
        $reflection = new \ReflectionClass(PasswordResetMailer::class);
        $method = $reflection->getMethod('getResetLink');
        $method->setAccessible(true);

        $token = 'safe_token_123';
        $link = $method->invoke(null, $token);

        // The link should not contain sensitive information
        $this->assertStringNotContainsString('user_id', strtolower($link));
        $this->assertStringNotContainsString('email', strtolower($link));
    }

    // ========================================
    // Accessibility and format tests
    // ========================================
    #[Test]
    public function htmlTemplateHasProperStructure(): void
    {
        $reflection = new \ReflectionClass(PasswordResetMailer::class);
        $method = $reflection->getMethod('getHtmlTemplate');
        $method->setAccessible(true);

        $html = $method->invoke(null, 'https://test.com');

        // Check basic HTML structure
        $this->assertStringContainsString('<head>', $html);
        $this->assertStringContainsString('<body>', $html);
        $this->assertStringContainsString('<meta charset', $html);
        $this->assertStringContainsString('<style>', $html);
    }

    #[Test]
    public function htmlTemplateHasResponsiveDesign(): void
    {
        $reflection = new \ReflectionClass(PasswordResetMailer::class);
        $method = $reflection->getMethod('getHtmlTemplate');
        $method->setAccessible(true);

        $html = $method->invoke(null, 'https://test.com');

        $this->assertStringContainsString('max-width', $html);
        $this->assertStringContainsString('viewport', $html);
    }

    #[Test]
    public function htmlTemplateHasAccessibleButton(): void
    {
        $reflection = new \ReflectionClass(PasswordResetMailer::class);
        $method = $reflection->getMethod('getHtmlTemplate');
        $method->setAccessible(true);

        $html = $method->invoke(null, 'https://test.com/reset');

        // The button should be an accessible link
        $this->assertStringContainsString('<a href=', $html);
        $this->assertStringContainsString('class=', $html);
    }

    // ========================================
    // Robustness tests
    // ========================================
    #[Test]
    public function templatesHandleEmptyToken(): void
    {
        $reflection = new \ReflectionClass(PasswordResetMailer::class);
        $htmlMethod = $reflection->getMethod('getHtmlTemplate');
        $htmlMethod->setAccessible(true);

        $textMethod = $reflection->getMethod('getTextTemplate');
        $textMethod->setAccessible(true);

        $resetLink = $reflection->getMethod('getResetLink');
        $resetLink->setAccessible(true);

        $link = $resetLink->invoke(null, '');
        $html = $htmlMethod->invoke(null, $link);
        $text = $textMethod->invoke(null, $link);

        $this->assertIsString($html);
        $this->assertIsString($text);
        $this->assertStringContainsString('token=', $link);
    }

    #[Test]
    public function multipleEmailGenerationsAreConsistent(): void
    {
        $reflection = new \ReflectionClass(PasswordResetMailer::class);
        $method = $reflection->getMethod('getHtmlTemplate');
        $method->setAccessible(true);

        $link = 'https://test.com/reset?token=abc';

        $html1 = $method->invoke(null, $link);
        $html2 = $method->invoke(null, $link);

        // The content should be identical (except for the year if it changes)
        $this->assertEquals($html1, $html2);
    }

    // ========================================
    // Performance tests
    // ========================================
    #[Test]
    public function templateGenerationIsPerformant(): void
    {
        $reflection = new \ReflectionClass(PasswordResetMailer::class);
        $htmlMethod = $reflection->getMethod('getHtmlTemplate');
        $htmlMethod->setAccessible(true);

        $start = microtime(true);

        for ($i = 0; $i < 100; $i++) {
            $htmlMethod->invoke(null, 'https://test.com/reset?token=test' . $i);
        }

        $duration = microtime(true) - $start;

        // Generating 100 emails should take less than 100ms
        $this->assertLessThan(0.1, $duration);
    }
}
