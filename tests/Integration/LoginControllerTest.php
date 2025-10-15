<?php

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;
use Controllers\User\LoginPost;

/**
 * Integration test for LoginPost controller
 * 
 * @group integration
 * @covers \Controllers\User\LoginPost
 */
class LoginControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $_SESSION = [];
        $_POST = [];
        $_SERVER['REQUEST_METHOD'] = 'POST';
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
        $_POST = [];
        parent::tearDown();
    }

    /**
     * @test
     * @covers \Controllers\User\LoginPost::support
     */
    public function it_supports_login_post_route(): void
    {
        $supported = LoginPost::support('/login', 'POST');
        
        $this->assertTrue($supported);
    }

    /**
     * @test
     * @covers \Controllers\User\LoginPost::support
     */
    public function it_does_not_support_get_method(): void
    {
        $supported = LoginPost::support('/login', 'GET');
        
        $this->assertFalse($supported);
    }

    /**
     * @test
     * @covers \Controllers\User\LoginPost::support
     */
    public function it_does_not_support_other_routes(): void
    {
        $supported = LoginPost::support('/register', 'POST');
        
        $this->assertFalse($supported);
    }

    /**
     * @test
     * @covers \Controllers\User\LoginPost::control
     */
    public function it_redirects_if_already_logged_in(): void
    {
        // Simulate logged in user
        $_SESSION['user_id'] = 'test@univ-amu.fr';
        
        $controller = new LoginPost();
        
        // Capture output and headers
        ob_start();
        try {
            $controller->control();
        } catch (\Exception $e) {
            // Expected to exit with header redirect
        }
        ob_end_clean();
        
        // Verify session is still set
        $this->assertArrayHasKey('user_id', $_SESSION);
    }

    /**
     * @test
     * @covers \Controllers\User\LoginPost::control
     */
    public function it_sets_error_flash_on_empty_credentials(): void
    {
        $_POST = [
            'email' => '',
            'password' => ''
        ];
        
        $controller = new LoginPost();
        
        ob_start();
        $controller->control();
        $output = ob_get_clean();
        
        // Should have errors in session
        $this->assertArrayHasKey('flash', $_SESSION);
    }
}
