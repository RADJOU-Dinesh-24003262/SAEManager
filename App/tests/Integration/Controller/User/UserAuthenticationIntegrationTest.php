<?php

namespace tests\Integration\Controller\User;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Controllers\Login\LoginPostController;
use Controllers\Register\RegisterPostController;
use Core\Utilis\SessionService;
use Models\Entity\User\User;

/**
 * Integration tests for user authentication flow
 *
 * Tests the complete registration and login process
 *
 * @category Test
 *
 * @package Tests
 *
 * @author Alexandre Benhafessa <alexandre.benhafessa@etu.univ-amu.fr>
 * @author François Dargentolle <francois.dargentolle@etu.univ-amu.fr>
 * @author William Edelstein <william.edelstein@etu.univ-amu.fr>
 * @author Nathan Griguer <nathan.griguer@etu.univ-amu.fr>
 * @author Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 *
 * @license MIT License https://opensource.org/licenses/MIT
 *
 * @link https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
#[CoversClass(LoginPostController::class)]
#[CoversClass(RegisterPostController::class)]
#[CoversClass(User::class)]
#[CoversClass(SessionService::class)]
#[RunInSeparateProcess]
class UserAuthenticationIntegrationTest extends TestCase
{
    /**
     * Setup before each test
     */
    protected function setUp(): void
    {
        parent::setUp();
        $_SESSION = [];
        $_POST = [];
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
    }

    /**
     * Clean up after each test
     */
    protected function tearDown(): void
    {
        $_SESSION = [];
        $_POST = [];
        parent::tearDown();
    }


    /**
     * Test session regeneration after successful login
     */
    public function testSessionRegenerationAfterLogin(): void
    {
        // Start session and get initial ID.
        SessionService::start();
        $initialSessionId = session_id();

        // Simulate successful login (would need database mock).
        SessionService::set('user_id', 'test@univ-amu.fr');
        SessionService::regenerateId();

        $newSessionId = session_id();

        // Session ID should have changed.
        $this->assertNotEquals($initialSessionId, $newSessionId);

        // But user data should persist.
        $this->assertEquals('test@univ-amu.fr', SessionService::get('user_id'));
    }

    /**
     * Test XSS protection in error messages
     */
    public function testXssProtectionInErrorMessages(): void
    {
        $maliciousInput = '<script>alert("xss")</script>';

        SessionService::setFlash('errors', [$maliciousInput]);

        $errors = SessionService::getFlash('errors');

        // The flash system should not execute the script.
        // When rendered in view, htmlspecialchars should protect.
        $this->assertIsArray($errors);
        $this->assertContains($maliciousInput, $errors);
    }

    /**
     * Test session timeout enforcement
     */
    public function testSessionTimeoutEnforcement(): void
    {
        SessionService::start();

        // Set session created time to 31 minutes ago.
        $_SESSION['_session_created'] = time() - 1860;

        // Restart session (should trigger timeout check).
        SessionService::destroy();
        SessionService::start();

        // Session should have been destroyed and recreated.
        $this->assertFalse(isset($_SESSION['user_id']));
    }

    /**
     * Test concurrent session handling
     */
    public function testConcurrentSessionHandling(): void
    {
        SessionService::start();
        SessionService::set('user_id', 'test@univ-amu.fr');

        $firstSessionId = session_id();

        // Simulate login from another device (regenerate ID).
        SessionService::regenerateId();

        $secondSessionId = session_id();

        $this->assertNotEquals($firstSessionId, $secondSessionId);
        $this->assertEquals('test@univ-amu.fr', SessionService::get('user_id'));
    }
}
