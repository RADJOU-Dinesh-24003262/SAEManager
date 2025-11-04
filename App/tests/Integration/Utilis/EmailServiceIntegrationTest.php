<?php

namespace Tests\Integration\Utilis;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Core\Utilis\EmailService;
use Core\Utilis\TokenService;

/**
 * Tests d'intégration pour EmailService
 */
#[CoversClass(EmailService::class)]
#[CoversClass(TokenService::class)]
class EmailServiceIntegrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $_SERVER['HTTP_HOST'] = 'test.example.com';
        $_SERVER['HTTPS'] = 'on';
    }

    protected function tearDown(): void
    {
        unset($_SERVER['HTTP_HOST']);
        unset($_SERVER['HTTPS']);
        parent::tearDown();
    }

    // ========================================
    // Tests with TokenService
    // ========================================

    #[Test]
    public function emailTemplateContainsValidTokenLink(): void
    {
        $token = TokenService::generate();
        
        $reflection = new \ReflectionClass(EmailService::class);
        $getLinkMethod = $reflection->getMethod('getResetLink');
        $getLinkMethod->setAccessible(true);
        
        $link = $getLinkMethod->invoke(null, $token);
        
        $htmlMethod = $reflection->getMethod('getHtmlTemplate');
        $htmlMethod->setAccessible(true);
        
        $html = $htmlMethod->invoke(null, $link);
        
        $this->assertStringContainsString($token, $html);
        $this->assertStringContainsString('https://', $html);
        $this->assertStringContainsString('/reset-password', $html);
    }

    #[Test]
    public function emailContainsBothHtmlAndTextVersions(): void
    {
        $token = TokenService::generate();
        
        $reflection = new \ReflectionClass(EmailService::class);
        $getLinkMethod = $reflection->getMethod('getResetLink');
        $getLinkMethod->setAccessible(true);
        
        $link = $getLinkMethod->invoke(null, $token);
        
        $htmlMethod = $reflection->getMethod('getHtmlTemplate');
        $htmlMethod->setAccessible(true);
        
        $textMethod = $reflection->getMethod('getTextTemplate');
        $textMethod->setAccessible(true);
        
        $html = $htmlMethod->invoke(null, $link);
        $text = $textMethod->invoke(null, $link);

        // Both versions must contain the link
        $this->assertStringContainsString($link, $html);
        $this->assertStringContainsString($link, $text);

        // HTML must have tags, not the text
        $this->assertStringContainsString('<html', $html);
        $this->assertStringNotContainsString('<html', $text);
    }

    // ========================================
    // Tests of template consistency
    // ========================================

    #[Test]
    public function htmlAndTextTemplatesContainSameInformation(): void
    {
        $link = 'https://test.com/reset?token=test123';
        
        $reflection = new \ReflectionClass(EmailService::class);
        
        $htmlMethod = $reflection->getMethod('getHtmlTemplate');
        $htmlMethod->setAccessible(true);
        
        $textMethod = $reflection->getMethod('getTextTemplate');
        $textMethod->setAccessible(true);
        
        $html = $htmlMethod->invoke(null, $link);
        $text = $textMethod->invoke(null, $link);

        // Key information to find in both versions
        $keyInfo = [
            '10 minutes',
            'SAEManager',
            'une seule fois',
        ];
        
        foreach ($keyInfo as $info) {
            $this->assertStringContainsString($info, $html);
            $this->assertStringContainsString($info, $text);
        }
    }

    #[Test]
    public function emailTemplatesAreConsistentAcrossMultipleGenerations(): void
    {
        $link = 'https://test.com/reset?token=abc123';
        
        $reflection = new \ReflectionClass(EmailService::class);
        $htmlMethod = $reflection->getMethod('getHtmlTemplate');
        $htmlMethod->setAccessible(true);
        
        $html1 = $htmlMethod->invoke(null, $link);
        $html2 = $htmlMethod->invoke(null, $link);

        // Templates must be identical for the same link
        $this->assertEquals($html1, $html2);
    }

    // ========================================
    // Security tests
    // ========================================

    #[Test]
    public function emailDoesNotLeakSensitiveInformation(): void
    {
        $token = TokenService::generate();
        
        $reflection = new \ReflectionClass(EmailService::class);
        $getLinkMethod = $reflection->getMethod('getResetLink');
        $getLinkMethod->setAccessible(true);
        
        $link = $getLinkMethod->invoke(null, $token);
        
        $htmlMethod = $reflection->getMethod('getHtmlTemplate');
        $htmlMethod->setAccessible(true);
        
        $html = $htmlMethod->invoke(null, $link);
        
        // Check that no sensitive keywords are present
        $this->assertStringNotContainsString('user_id ', strtolower($html));
        // Does not contain email addresses or passwords
        $this->assertStringNotContainsString('@', strtolower($html));
        $this->assertStringNotContainsString('password ', strtolower($html));
    }

    #[Test]
    public function emailLinksUseSecureProtocol(): void
    {
        $_SERVER['HTTPS'] = 'on';
        
        $token = TokenService::generate();
        
        $reflection = new \ReflectionClass(EmailService::class);
        $getLinkMethod = $reflection->getMethod('getResetLink');
        $getLinkMethod->setAccessible(true);
        
        $link = $getLinkMethod->invoke(null, $token);
        
        $this->assertStringStartsWith('https://', $link);
    }

    // ========================================
    // Validation and format tests
    // ========================================

    #[Test]
    public function htmlTemplateIsValidHtml(): void
    {
        $link = 'https://test.com/reset';
        
        $reflection = new \ReflectionClass(EmailService::class);
        $htmlMethod = $reflection->getMethod('getHtmlTemplate');
        $htmlMethod->setAccessible(true);
        
        $html = $htmlMethod->invoke(null, $link);
        
        // Checks the basic HTML structure
        $this->assertStringContainsString('<!DOCTYPE html>', $html);
        $this->assertStringContainsString('<html', $html);
        $this->assertStringContainsString('</html>', $html);
        $this->assertStringContainsString('<head>', $html);
        $this->assertStringContainsString('</head>', $html);
        $this->assertStringContainsString('<body>', $html);
        $this->assertStringContainsString('</body>', $html);

        // Check that all tags are closed
        $this->assertEquals(
            substr_count($html, '<div'),
            substr_count($html, '</div')
        );
    }

    #[Test]
    public function emailTemplateHandlesSpecialCharactersInUrl(): void
    {
        $specialToken = 'token_with_special!@#$%';
        
        $reflection = new \ReflectionClass(EmailService::class);
        $getLinkMethod = $reflection->getMethod('getResetLink');
        $getLinkMethod->setAccessible(true);
        
        $link = $getLinkMethod->invoke(null, $specialToken);
        
        $htmlMethod = $reflection->getMethod('getHtmlTemplate');
        $htmlMethod->setAccessible(true);
        
        $html = $htmlMethod->invoke(null, $link);
        
        $this->assertStringContainsString($specialToken, $html);
    }

    // ========================================
    // Performance tests
    // ========================================

    #[Test]
    public function emailGenerationIsPerformant(): void
    {
        $link = 'https://test.com/reset?token=test123';
        
        $reflection = new \ReflectionClass(EmailService::class);
        $htmlMethod = $reflection->getMethod('getHtmlTemplate');
        $htmlMethod->setAccessible(true);
        
        $start = microtime(true);
        
        for ($i = 0; $i < 50; $i++) {
            $htmlMethod->invoke(null, $link);
        }
        
        $duration = microtime(true) - $start;

        // 50 generations should take less than 50ms
        $this->assertLessThan(0.05, $duration);
    }

    // ========================================
    // Accessibility tests
    // ========================================

    #[Test]
    public function htmlTemplateHasAccessibleElements(): void
    {
        $link = 'https://test.com/reset';
        
        $reflection = new \ReflectionClass(EmailService::class);
        $htmlMethod = $reflection->getMethod('getHtmlTemplate');
        $htmlMethod->setAccessible(true);
        
        $html = $htmlMethod->invoke(null, $link);
        
        // Check accessibility elements
        $this->assertStringContainsString('lang=', $html);
        $this->assertStringContainsString('charset', $html);
    }

    #[Test]
    public function emailButtonHasProperLinkStructure(): void
    {
        $link = 'https://test.com/reset?token=abc123';
        
        $reflection = new \ReflectionClass(EmailService::class);
        $htmlMethod = $reflection->getMethod('getHtmlTemplate');
        $htmlMethod->setAccessible(true);
        
        $html = $htmlMethod->invoke(null, $link);
        
        // Checks that the button link is properly formed
        $this->assertMatchesRegularExpression('/<a[^>]+href=\'[^\']*' . preg_quote($link, '/') . '[^\']*\'/', $html);
    }

    // ========================================
    // Robustness tests
    // ========================================

    #[Test]
    public function emailHandlesVeryLongTokens(): void
    {
        $longToken = str_repeat('a', 200);
        
        $reflection = new \ReflectionClass(EmailService::class);
        $getLinkMethod = $reflection->getMethod('getResetLink');
        $getLinkMethod->setAccessible(true);
        
        $link = $getLinkMethod->invoke(null, $longToken);
        
        $htmlMethod = $reflection->getMethod('getHtmlTemplate');
        $htmlMethod->setAccessible(true);
        
        $html = $htmlMethod->invoke(null, $link);
        
        $this->assertStringContainsString($longToken, $html);
        // Check that the HTML contains word-break for long links
        $this->assertStringContainsString('word-break', $html);
    }

    #[Test]
    public function emailHandlesDifferentDomains(): void
    {
        $domains = [
            'localhost',
            'test.com',
            'sub.domain.test.com',
            'test-site.fr'
        ];
        
        $reflection = new \ReflectionClass(EmailService::class);
        $getLinkMethod = $reflection->getMethod('getResetLink');
        $getLinkMethod->setAccessible(true);
        
        foreach ($domains as $domain) {
            $_SERVER['HTTP_HOST'] = $domain;
            
            $link = $getLinkMethod->invoke(null, 'token123');
            
            $this->assertStringContainsString($domain, $link);
        }
    }
}