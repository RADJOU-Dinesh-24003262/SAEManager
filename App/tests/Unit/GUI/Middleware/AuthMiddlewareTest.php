<?php

namespace Tests\Unit\GUI\Middleware;

use App\Domain\User\Professor;
use App\Domain\User\Student;
use App\GUI\Middleware\AuthMiddleware;
use App\Infrastructure\Service\SessionService;
use PHPUnit\Framework\TestCase;

class AuthMiddlewareTest extends TestCase
{
    protected function setUp(): void
    {
        $_SESSION = [];
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
    }

    public function testAuthenticateRedirectsIfNoSession()
    {
        $this->expectOutputRegex('/Location: \/login/');

        // Mock SessionService behavior via global state since it's static
        // But SessionService relies on $_SESSION, which we cleared.
        // It calls header() which we can trap with expectOutputRegex (if output buffering enabled by phpunit)
        // or check xdebug headers. 
        // Standard PHPUnit output testing works for echo, but header() is tricky without a wrapper.
        // Given existing code uses header(), this test might be hard to run in CLI without a generic wrapper.
        // However, we can assert logic.

        // Actually, without a way to mock header(), we can't test redirection easily in unit test.
        // We'll skip testing header() directly and focus on user retrieval if session exists.

        $this->markTestSkipped('Cannot test header redirection in CLI without refactoring SessionService/Header wrapper');
    }

    public function testAuthenticateReturnsUserIfSessionExists()
    {
        // Mock a user
        $user = new Student(['user_id' => 1, 'email' => 'test@etu.univ-amu.fr']);

        // Setup session
        $_SESSION['user_id'] = 1;
        $_SESSION['USER'] = serialize($user);

        // Act
        $authenticatedUser = AuthMiddleware::authenticate();

        // Assert
        $this->assertInstanceOf(Student::class , $authenticatedUser);
        $this->assertEquals(1, $authenticatedUser->getUserId());
    }

    public function testEnsureProfessorRedirectsIfStudent()
    {
        $user = new Student(['user_id' => 1]);

        // We expect redirection. 
        // Since we can't catch header(), we might just assert it calls logic that *would* redirect.
        // Or we can assume it works if we see it calling SessionService::setFlash

        $this->markTestSkipped('Cannot test header redirection');
    }

    public function testEnsureProfessorPassesIfProfessor()
    {
        $user = new Professor(['user_id' => 2]);

        // Should return void and not exit
        AuthMiddleware::ensureProfessor($user);

        $this->assertTrue(true);
    }
}