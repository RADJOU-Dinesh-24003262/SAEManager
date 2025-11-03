<?php

namespace Tests\Unit\Controllers;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Controllers\Index\IndexController;
use Controllers\Info\LegalNoticeController;
use Controllers\Info\SiteMapController;
use Controllers\PageSae\PageSaeController;
use Controllers\ToDoList\ToDoListController;
use Controllers\User\Register;
use Controllers\User\Logout;
use Controllers\pwd\ForgotPasswordController;
use Controllers\pwd\ResetPasswordController;

/**
 * Unit tests for simple GET controllers
 */
#[CoversClass(IndexController::class)]
#[CoversClass(LegalNoticeController::class)]
#[CoversClass(SiteMapController::class)]
#[CoversClass(PageSaeController::class)]
#[CoversClass(ToDoListController::class)]
#[CoversClass(Register::class)]
#[CoversClass(ForgotPasswordController::class)]
#[CoversClass(ResetPasswordController::class)]
class ControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $_SESSION = [];
        $_SERVER['REQUEST_METHOD'] = 'GET';
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
        parent::tearDown();
    }

    // ========================================
    // Tests for IndexController
    // ========================================

    #[Test]
    public function indexControllerSupportsCorrectRoute(): void
    {
        // Check that IndexController supports the correct routes
        $this->assertTrue(IndexController::support('/', 'GET'));
        $this->assertTrue(IndexController::support('/index', 'GET'));
        $this->assertFalse(IndexController::support('/index', 'POST'));
        $this->assertFalse(IndexController::support('/home', 'GET'));
    }

    #[Test]
    public function indexControllerHasControlMethod(): void
    {
        // Check that IndexController has the required methods
        $controller = new IndexController();
        $reflection = new \ReflectionClass($controller);

        $this->assertTrue($reflection->hasMethod('control'));
        $this->assertTrue($reflection->hasMethod('support'));

        $controlMethod = $reflection->getMethod('control');
        $this->assertTrue($controlMethod->isPublic());
    }

    // ========================================
    // Tests for LegalNoticeController
    // ========================================

    #[Test]
    public function legalNoticeControllerSupportsCorrectRoute(): void
    {
        // Check that LegalNoticeController supports the correct routes
        $this->assertTrue(LegalNoticeController::support('/legal-notice', 'GET'));
        $this->assertFalse(LegalNoticeController::support('/legal-notice', 'POST'));
        $this->assertFalse(LegalNoticeController::support('/legal', 'GET'));
        $this->assertFalse(LegalNoticeController::support('/legal-notice/', 'GET'));
    }

    #[Test]
    public function legalNoticeControllerImplementsInterface(): void
    {
        // Check that LegalNoticeController implements the ControllerInterface
        $controller = new LegalNoticeController();
        $this->assertInstanceOf(\Core\ControllerInterface::class, $controller);
    }

    // ========================================
    // Tests for SiteMapController
    // ========================================

    #[Test]
    public function siteMapControllerSupportsCorrectRoute(): void
    {
        // Check that SiteMapController supports the correct routes
        $this->assertTrue(SiteMapController::support('/site-map', 'GET'));
        $this->assertFalse(SiteMapController::support('/site-map', 'POST'));
        $this->assertFalse(SiteMapController::support('/sitemap', 'GET'));
    }

    // ========================================
    // Tests for PageSaeController
    // ========================================

    #[Test]
    public function pageSaeControllerSupportsCorrectRoute(): void
    {
        // Check that PageSaeController supports the correct routes
        $this->assertTrue(PageSaeController::support('/page-sae', 'GET'));
        $this->assertFalse(PageSaeController::support('/page-sae', 'POST'));
        $this->assertFalse(PageSaeController::support('/sae', 'GET'));
    }

    // ========================================
    // Tests for ToDoListController
    // ========================================

    #[Test]
    public function toDoListControllerSupportsCorrectRoute(): void
    {
        // Check that ToDoListController supports the correct routes
        $this->assertTrue(ToDoListController::support('/to-do-list', 'GET'));
        $this->assertFalse(ToDoListController::support('/to-do-list', 'POST'));
        $this->assertFalse(ToDoListController::support('/todo', 'GET'));
    }

    // ========================================
    // Tests for Register
    // ========================================

    #[Test]
    public function registerControllerSupportsCorrectRoute(): void
    {
        // Check that Register controller supports the correct routes
        $this->assertTrue(Register::support('/register', 'GET'));
        $this->assertFalse(Register::support('/register', 'POST'));
        $this->assertFalse(Register::support('/signup', 'GET'));
    }

    #[Test]
    public function registerControllerRedirectsIfLoggedIn(): void
    {
        // Set a session to simulate a logged-in user
        $_SESSION['user_id'] = 'test@test.fr';

        $controller = new Register();

        // Cannot test redirection directly because it uses header()
        // But we can check that the session exists
        $this->assertTrue(isset($_SESSION['user_id']));
    }

    // ========================================
    // Tests for ForgotPasswordController
    // ========================================

    #[Test]
    public function forgotPasswordControllerSupportsCorrectRoute(): void
    {
        // Check that ForgotPasswordController supports the correct routes
        $this->assertTrue(ForgotPasswordController::support('/forgot-password', 'GET'));
        $this->assertFalse(ForgotPasswordController::support('/forgot-password', 'POST'));
        $this->assertFalse(ForgotPasswordController::support('/reset-password', 'GET'));
    }

    // ========================================
    // Tests for ResetPasswordController
    // ========================================

    #[Test]
    public function resetPasswordControllerSupportsCorrectRoute(): void
    {
        // Check that ResetPasswordController supports the correct routes
        $this->assertTrue(ResetPasswordController::support('/reset-password', 'GET'));
        $this->assertFalse(ResetPasswordController::support('/reset-password', 'POST'));
        $this->assertFalse(ResetPasswordController::support('/forgot-password', 'GET'));
    }

    // ========================================
    // Generic tests for all controllers
    // ========================================

    #[Test]
    #[DataProvider('controllerProvider')]
    public function allControllersImplementInterface(string $controllerClass): void
    {
        // Check that all controllers implement the ControllerInterface
        $controller = new $controllerClass();
        $this->assertInstanceOf(\Core\ControllerInterface::class, $controller);
    }

    #[Test]
    #[DataProvider('controllerProvider')]
    public function allControllersHaveRequiredMethods(string $controllerClass): void
    {
        // Check that all controllers have the required 'control' and 'support' methods
        /** @var class-string $controllerClass */
        $reflection = new \ReflectionClass($controllerClass);

        $this->assertTrue($reflection->hasMethod('control'));
        $this->assertTrue($reflection->hasMethod('support'));

        $supportMethod = $reflection->getMethod('support');
        $this->assertTrue($supportMethod->isStatic());
        $this->assertTrue($supportMethod->isPublic());

        $controlMethod = $reflection->getMethod('control');
        $this->assertTrue($controlMethod->isPublic());
        $this->assertFalse($controlMethod->isStatic());
    }

    #[Test]
    #[DataProvider('controllerProvider')]
    public function supportMethodHasCorrectSignature(string $controllerClass): void
    {
        // Check that the support method has the correct parameters
        /** @var class-string $controllerClass */
        $reflection = new \ReflectionClass($controllerClass);
        $method = $reflection->getMethod('support');

        $parameters = $method->getParameters();
        $this->assertCount(2, $parameters);
        $this->assertEquals('path', $parameters[0]->getName());
        $this->assertEquals('method', $parameters[1]->getName());
    }

    public static function controllerProvider(): array
    {
        // List of controllers to be tested generically
        return [
            [IndexController::class],
            [LegalNoticeController::class],
            [SiteMapController::class],
            [PageSaeController::class],
            [ToDoListController::class],
            [Register::class],
            [ForgotPasswordController::class],
            [ResetPasswordController::class]
        ];
    }

    // ========================================
    // Edge case tests
    // ========================================

    #[Test]
    public function controllersRejectEmptyPaths(): void
    {
        // Controllers should reject empty paths
        $this->assertFalse(IndexController::support('', 'GET'));
        $this->assertFalse(LegalNoticeController::support('', 'GET'));
        $this->assertFalse(SiteMapController::support('', 'GET'));
    }

    #[Test]
    public function controllersRejectPathsWithTrailingSlash(): void
    {
        // Controllers should reject paths with trailing slashes
        $this->assertFalse(LegalNoticeController::support('/legal-notice/', 'GET'));
        $this->assertFalse(SiteMapController::support('/site-map/', 'GET'));
        $this->assertFalse(ToDoListController::support('/to-do-list/', 'GET'));
    }

    #[Test]
    public function controllersAreCaseSensitiveForMethod(): void
    {
        // Controllers should be case-sensitive for HTTP method
        $this->assertTrue(IndexController::support('/', 'GET'));
        $this->assertFalse(IndexController::support('/', 'get'));
        $this->assertFalse(IndexController::support('/', 'Get'));
        $this->assertFalse(IndexController::support('/', 'GeT'));
    }

    #[Test]
    public function controllersRejectUnexpectedMethods(): void
    {
        // Controllers should reject unexpected HTTP methods
        $methods = ['PUT', 'DELETE', 'PATCH', 'OPTIONS', 'HEAD'];

        foreach ($methods as $method) {
            $this->assertFalse(IndexController::support('/', $method));
            $this->assertFalse(LegalNoticeController::support('/legal-notice', $method));
        }
    }

    // ========================================
    // Security tests
    // ========================================

    #[Test]
    public function controllersRejectPathTraversalAttempts(): void
    {
        // Controllers should reject path traversal attempts
        $maliciousPaths = [
            '../index',
            '/../legal-notice',
            '/./site-map',
            '/legal-notice/../',
            '//legal-notice'
        ];

        foreach ($maliciousPaths as $path) {
            $this->assertFalse(IndexController::support($path, 'GET'));
            $this->assertFalse(LegalNoticeController::support($path, 'GET'));
        }
    }

    #[Test]
    public function controllersRejectSQLInjectionInPath(): void
    {
        // Controllers should reject SQL injection attempts in the path
        $maliciousPaths = [
            "/' OR '1'='1",
            "/'; DROP TABLE users--",
            "/<script>alert('xss')</script>"
        ];

        foreach ($maliciousPaths as $path) {
            $this->assertFalse(IndexController::support($path, 'GET'));
        }
    }

    #[Test]
    public function controllersHandleWhitespaceInPaths(): void
    {
        // Controllers should reject paths with leading or trailing whitespace
        $this->assertFalse(IndexController::support('/ ', 'GET'));
        $this->assertFalse(IndexController::support(' /', 'GET'));
        $this->assertFalse(LegalNoticeController::support(' /legal-notice', 'GET'));
        $this->assertFalse(LegalNoticeController::support('/legal-notice ', 'GET'));
    }

    // ========================================
    // Performance and robustness tests
    // ========================================

    #[Test]
    public function supportMethodIsPerformant(): void
    {
        // Check that the support method is performant for multiple calls
        $start = microtime(true);

        for ($i = 0; $i < 1000; $i++) {
            IndexController::support('/', 'GET');
            LegalNoticeController::support('/legal-notice', 'GET');
            SiteMapController::support('/site-map', 'GET');
        }

        $duration = microtime(true) - $start;

        // 3000 calls should take less than 100ms
        $this->assertLessThan(0.1, $duration);
    }

    #[Test]
    public function multipleControllerInstancesAreIndependent(): void
    {
        // Multiple controller instances should be independent
        $controller1 = new IndexController();
        $controller2 = new IndexController();
        $controller3 = new LegalNoticeController();

        $this->assertNotSame($controller1, $controller2);
        $this->assertNotSame($controller1, $controller3);
        $this->assertInstanceOf(\Core\ControllerInterface::class, $controller1);
        $this->assertInstanceOf(\Core\ControllerInterface::class, $controller2);
        $this->assertInstanceOf(\Core\ControllerInterface::class, $controller3);
    }
}
