<?php

namespace Tests\Integration\Controller\Password;

use Controllers\ForgotPassword\ForgotPasswordController;
use Controllers\ForgotPassword\ForgotPasswordPostController;
use Core\Controllers\ControllerInterface;
use Core\Includes\Database;
use Core\Includes\Exception\ExceptionSpam;
use Core\Includes\Exception\ExceptionValidation\ExceptionValidationEmpty;
use Core\Includes\Exception\ExceptionValidation\ExceptionValidationEmptys;
use Core\Includes\Exception\ExceptionValidation\ExceptionValidationForgotPassword;
use Core\Utils\Config;
use Core\Utils\RateLimiter;
use Core\Utils\SessionService;
use Core\Views\AbstractView;
use Exception;
use Models\Entity\User\User;
use Models\Repository\User\PdoUserRepository;
use Core\Models\Repository\BaseRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Validator\ForgotPassword\ForgotPasswordValidator;

/**
 * Integration tests for Forgot Password functionality
 */
#[CoversClass(ForgotPasswordController::class)]
#[CoversClass(ForgotPasswordPostController::class)]
#[CoversClass(ForgotPasswordValidator::class)]
#[CoversClass(AbstractView::class)]
#[CoversClass(ExceptionValidationEmpty::class)]
#[CoversClass(ExceptionValidationEmptys::class)]
#[CoversClass(ExceptionValidationForgotPassword::class)]
#[CoversClass(SessionService::class)]
#[CoversClass(Database::class)]
#[CoversClass(User::class)]
#[UsesClass(BaseRepository::class)]
#[UsesClass(PdoUserRepository::class)]
#[CoversClass(ExceptionSpam::class)]
#[CoversClass(RateLimiter::class)]
#[CoversClass(Config::class)]
class ForgotPasswordControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $_SESSION = [];
        $_POST = [];
        $_SERVER['REQUEST_METHOD'] = 'GET';

        // Inject mock Database
        $mockDb = $this->createMock(Database::class);
        $mockStmt = $this->createMock(\PDOStatement::class);

        $mockDb->method('prepare')->willReturn($mockStmt);
        $mockStmt->method('execute')->willReturn(true);
        $mockStmt->method('fetchColumn')->willReturn(1); // User exists

        Database::setInstance($mockDb);
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
        $_POST = [];

        // Reset Database singleton
        $reflection = new \ReflectionClass(Database::class);
        $instance = $reflection->getProperty('instance');
        $instance->setAccessible(true);
        $instance->setValue(null, null);

        parent::tearDown();
    }

    // Tests for GET controller
    #[Test]
    public function getControllerSupportsCorrectRoute(): void
    {
        $this->assertTrue(ForgotPasswordController::support('/forgot-password', 'GET'));
        $this->assertFalse(ForgotPasswordController::support('/forgot-password', 'POST'));
        $this->assertFalse(ForgotPasswordController::support('/reset-password', 'GET'));
    }

    #[Test]
    public function getControllerRendersView(): void
    {
        $controller = new ForgotPasswordController();

        ob_start();
        $controller->control();
        $output = ob_get_clean();

        $this->assertNotEmpty($output);
        $this->assertStringContainsString('email', $output ?: '');
    }

    // Tests for POST controller
    #[Test]
    public function postControllerSupportsCorrectRoute(): void
    {
        $this->assertTrue(ForgotPasswordPostController::support('/forgot-password', 'POST'));
        $this->assertFalse(ForgotPasswordPostController::support('/forgot-password', 'GET'));
    }

    #[Test]
    public function postControllerRejectsEmptyEmail(): void
    {
        $_POST = [
            'email' => '',
            'h-captcha-response' => 'test-captcha-success'
        ];
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $controller = new ForgotPasswordPostController();

        ob_start();
        $controller->control();
        $content = ob_get_clean() ?: ' ';

        // Should have this error on the html page.
        $this->assertMatchesRegularExpression('/Le champ .{1,6}email.{1,6} ne doit pas être vide/', $content);
    }

    #[Test]
    public function postControllerRejectsInvalidEmail(): void
    {
        $_POST = [
            'email' => 'invalid-email',
            'h-captcha-response' => 'test-captcha-success'
        ];
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $controller = new ForgotPasswordPostController();

        ob_start();
        $controller->control();
        $content = ob_get_clean() ?: ' ';

        // Should have this error on the html page.
        $this->assertMatchesRegularExpression('/L.{1,6}adresse email n.{1,6}est pas valide/', $content);
    }


    #[Test]
    public function preventsTooManyRequests(): void
    {
        // Simulate recent requests to trigger limit
        RateLimiter::increment('forgot_password');
        RateLimiter::increment('forgot_password');

        $_POST = [
            'email' => 'jean.dupont@etu.univ-amu.fr',
            'h-captcha-response' => 'test-captcha-success'
        ];

        ob_start();

        $controller = new ForgotPasswordPostController();
        $controller->control();
        $content = ob_get_clean() ?: ' ';

        // Should set spam error message on the html page.
        $this->assertStringContainsString('Veuillez attendre au moins 2 minutes avant de refaire une demande.', $content);

        RateLimiter::clear('forgot_password');
    }

    #[Test]
    public function validatorAcceptsValidAmuEmail(): void
    {
        $validator = new ForgotPasswordValidator();
        $data = [
            'email' => 'jean.dupont@etu.univ-amu.fr',
            'h-captcha-response' => 'test-captcha-success'
        ];

        $escaped = $validator->escape($data);

        // Should not throw exception
        $this->expectNotToPerformAssertions();
        $validator->validate($escaped);
    }

    #[Test]
    public function validatorAcceptsValidNonEtuAmuEmail(): void
    {
        $validator = new ForgotPasswordValidator();
        $data = [
            'email' => 'prof.dupont@univ-amu.fr',
            'h-captcha-response' => 'test-captcha-success'
        ];

        $escaped = $validator->escape($data);

        // Should not throw exception
        $this->expectNotToPerformAssertions();
        $validator->validate($escaped);
    }

    #[Test]
    public function controllerImplementsCorrectInterface(): void
    {
        $getController = new ForgotPasswordController();
        $postController = new ForgotPasswordPostController();

        $this->assertInstanceOf(ControllerInterface::class, $getController);
        $this->assertInstanceOf(ControllerInterface::class, $postController);
    }
}
