<?php

namespace Tests\Integration\Controller\Password;

use Controllers\ForgotPassword\ForgotPasswordController;
use Controllers\ForgotPassword\ForgotPasswordPostController;
use Core\Controllers\ControllerInterface;
use Core\includes\Database;
use Core\includes\exception\ExceptionSpam;
use Core\includes\exception\ExceptionValidation\ExceptionValidationEmpty;
use Core\includes\exception\ExceptionValidation\ExceptionValidationEmptys;
use Core\includes\exception\ExceptionValidation\ExceptionValidationForgotPassword;
use Core\Utilis\SessionService;
use Core\Views\AbstractView;
use Exception;
use Models\Entity\User\User;
use Models\Repository\User\PdoUserRepository;
use Core\Models\Repository\BaseRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Validator\ForgotPasswordValidator;

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
class ForgotPasswordControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $_SESSION = [];
        $_POST = [];
        $_SERVER['REQUEST_METHOD'] = 'GET';
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
        $_POST = [];
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
        $_POST = ['email' => ''];
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
        $_POST = ['email' => 'invalid-email'];
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $controller = new ForgotPasswordPostController();

        ob_start();
        $controller->control();
        $content = ob_get_clean() ?: ' ';

        // Should have this error on the html page.
        $this->assertMatchesRegularExpression('/L.{1,6}adresse email n.{1,6}est pas valide/', $content);
    }

    #[Test]
    public function postControllerSetsGenericSuccessMessage(): void
    {
        $_POST = ['email' => 'jean.dupont@etu.univ-amu.fr'];
        $_SERVER['REQUEST_METHOD'] = 'POST';
        SessionService::remove('last_forgot_password_request');

        ob_start();
        $controller = new ForgotPasswordPostController();

        try {
            $controller->control();
        } catch (Exception $e) {
            // May throw due to database/email issues
        } finally {
            $content = ob_get_clean();
        }

        // Should either have success or error message on the html page.
        $this->assertTrue(
            str_contains($content ?: '', 'vous recevrez un lien de réinitialisation dans quelques minutes.') ||
            str_contains($content ?: '', 'Une erreur est survenue lors de l\'envoi de l\'email. Veuillez réessayer plus tard.')
        );
    }

    #[Test]
    public function preventsTooManyRequests(): void
    {
        // Simulate recent request
        $_SESSION['last_forgot_password_request'] = time();
        $_POST = ['email' => 'jean.dupont@etu.univ-amu.fr'];

        ob_start();

        $controller = new ForgotPasswordPostController();
        $controller->control();
        $content = ob_get_clean() ?: ' ';

        // Should set spam error message on the html page.
        $this->assertStringContainsString('Veuillez attendre au moins 2 minutes avant de refaire une demande.', $content);
    }

    #[Test]
    public function validatorAcceptsValidAmuEmail(): void
    {
        $validator = new ForgotPasswordValidator();
        $data = ['email' => 'jean.dupont@etu.univ-amu.fr'];

        $escaped = $validator->escape($data);

        // Should not throw exception
        $this->expectNotToPerformAssertions();
        $validator->validate($escaped);
    }

    #[Test]
    public function validatorAcceptsValidNonEtuAmuEmail(): void
    {
        $validator = new ForgotPasswordValidator();
        $data = ['email' => 'prof.dupont@univ-amu.fr'];

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
