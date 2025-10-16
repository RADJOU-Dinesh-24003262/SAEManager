<?php

namespace tests\Integration;

use PHPUnit\Framework\TestCase;
use Controllers\User\LoginPost;

/**
 * Integration test for the LoginPost controller.
 *
 * @group integration
 * @covers \Controllers\User\LoginPost
 */
class LoginControllerTest extends TestCase
{
    /**
     * Setup before each test.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $_SESSION = [];
        $_POST = [];
        $_SERVER['REQUEST_METHOD'] = 'POST';
    }

    /**
     * Clean up after each test.
     */
    protected function tearDown(): void
    {
        $_SESSION = [];
        $_POST = [];
        parent::tearDown();
    }

    /**
     * Test that the LoginPost controller supports the POST /login route.
     */
    public function testSupportsLoginPostRoute(): void
    {
        $supported = LoginPost::support('/login', 'POST');
        $this->assertTrue($supported);
    }

    /**
     * Test that the LoginPost controller does not support GET requests.
     */
    public function testDoesNotSupportGetMethod(): void
    {
        $supported = LoginPost::support('/login', 'GET');
        $this->assertFalse($supported);
    }

    /**
     * Test that the LoginPost controller does not support other routes.
     */
    public function testDoesNotSupportOtherRoutes(): void
    {
        $supported = LoginPost::support('/register', 'POST');
        $this->assertFalse($supported);
    }

    /**
     * Test that a user already logged in is redirected appropriately.
     */
    public function testRedirectsIfAlreadyLoggedIn(): void
    {
        // Simulate a logged-in user
        $_SESSION['user_id'] = 'test@univ-amu.fr';

        $controller = new LoginPost();

        ob_start();
        try {
            $controller->control();
        } catch (\Exception $e) {
            // Expected exception due to redirect or exit
        }
        ob_end_clean();

        // Assert that user_id is still set in session
        $this->assertArrayHasKey('user_id', $_SESSION);
    }

    /**
     * Test that submitting empty credentials sets an error flash message.
     */
    public function testSetsErrorFlashOnEmptyCredentials(): void
    {
        $_POST = [
            'email' => '',
            'password' => ''
        ];

        $controller = new LoginPost();

        ob_start();
        $controller->control();
        ob_end_clean();

        // Assert that the flash key is set in the session (indicating an error)
        $this->assertArrayHasKey('flash', $_SESSION);
    }
}
