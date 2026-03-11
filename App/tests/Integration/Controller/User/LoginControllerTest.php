<?php

namespace tests\Integration\Controller\User;

use Controllers\Login\LoginPostController;
use Core\Includes\Exception\ExceptionCsrf;
use Core\Includes\Exception\ExceptionValidation\ExceptionValidationEmpty;
use Core\Includes\Exception\ExceptionValidation\ExceptionValidationEmptys;
use Core\Utils\Logger;
use Core\Utils\SessionService;
use Core\Views\AbstractView;
use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Core\Utils\Config;
use Validator\FormValidator;

/**
 * Integration test for the LoginPostController controller.
 */
#[CoversClass(LoginPostController::class)]
#[CoversClass(SessionService::class)]
#[CoversClass(FormValidator::class)]
#[CoversClass(ExceptionValidationEmpty::class)]
#[CoversClass(ExceptionValidationEmptys::class)]
#[CoversClass(AbstractView::class)]
#[CoversClass(ExceptionCsrf::class)]
#[CoversClass(Config::class)]
#[CoversClass(Logger::class)]
#[RunInSeparateProcess]
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
     * Test that the LoginPostController controller supports the POST /login route.
     */
    public function testSupportsLoginPostControllerRoute(): void
    {
        $supported = LoginPostController::support('/login', 'POST');
        $this->assertTrue($supported);
    }

    /**
     * Test that the LoginPostController controller does not support GET requests.
     */
    public function testDoesNotSupportGetMethod(): void
    {
        $supported = LoginPostController::support('/login', 'GET');
        $this->assertFalse($supported);
    }

    /**
     * Test that the LoginPostController controller does not support other routes.
     */
    public function testDoesNotSupportOtherRoutes(): void
    {
        $supported = LoginPostController::support('/register', 'POST');
        $this->assertFalse($supported);
    }

    /**
     * Test that a user already logged in is redirected appropriately.
     */
    public function testRedirectsIfAlreadyLoggedIn(): void
    {
        // Simulate a logged-in user
        $_SESSION['user_id'] = 'test@univ-amu.fr';

        $controller = new LoginPostController();

        ob_start();
        try {
            $controller->control();
        } catch (Exception $e) {
            // Expected exception due to redirect or exit
        }
        ob_end_clean();

        // Assert that user_id is still set in session
        $this->assertArrayHasKey('user_id', $_SESSION);
        SessionService::destroy();
    }

    /**
     * Test that submitting empty credentials sets an error flash message.
     */
    public function testSetsErrorFlashOnEmptyCredentials(): void
    {
        SessionService::start();
        $_POST = [
            'csrf_token' => SessionService::generateCsrfToken(),
            'email' => '',
            'password' => ''
        ];

        $controller = new LoginPostController();

        ob_start();
        $controller->control();
        ob_end_clean();

        // Assert that the flash key is set in the session (indicating an error)
        $this->assertArrayHasKey('flash', $_SESSION);
    }
}
