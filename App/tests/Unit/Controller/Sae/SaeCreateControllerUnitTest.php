<?php

declare(strict_types=1);

namespace Tests\Unit\Controller\Sae;

use Controllers\Sae\SaeCreateController;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Unit Test for the controller SaeCreateController
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
#[CoversClass(SaeCreateController::class)]
class SaeCreateControllerUnitTest extends TestCase
{
    private SaeCreateController $controller;

    protected function setUp(): void
    {
        $this->controller = new SaeCreateController();
    }

    /**
     * INSTANTATION AND INTERFACE TESTS
     */
    #[Test]
    public function controllerHasCorrectNamespace(): void
    {
        $this->assertStringContainsString(
            'Controllers\Sae',
            get_class($this->controller)
        );
    }

    /**
     * UNIT TESTS FOR THE METHOD control()
     */
    #[Test]
    public function controlMethodReturnsVoid(): void
    {
        $reflection = new \ReflectionMethod($this->controller, 'control');
        $returnType = $reflection->getReturnType();

        $this->assertNotNull($returnType);
        if (method_exists($returnType, 'getName')) {
            $this->assertEquals('void', $returnType->getName());
        } else {
            $this->assertEquals('void', (string)$returnType);
        }
    }

    #[Test]
    public function controlMethodHasNoParameters(): void
    {
        $reflection = new \ReflectionMethod($this->controller, 'control');
        $parameters = $reflection->getParameters();

        // Reverted to 0 because the ControllerInterface no longer enforces parameters.
        $this->assertCount(0, $parameters);
    }

    #[Test]
    public function supportMethodIsStatic(): void
    {
        $reflection = new \ReflectionMethod(SaeCreateController::class, 'support');
        $this->assertTrue($reflection->isStatic());
    }

    #[Test]
    public function supportMethodHasCorrectParameters(): void
    {
        $reflection = new \ReflectionMethod(SaeCreateController::class, 'support');
        $parameters = $reflection->getParameters();

        $this->assertCount(2, $parameters);
        $this->assertEquals('path', $parameters[0]->getName());
        $this->assertEquals('method', $parameters[1]->getName());
        $this->assertEquals('string', (string)$parameters[0]->getType());
        $this->assertEquals('string', (string)$parameters[1]->getType());
    }

    #[Test]
    public function supportMethodReturnsBoolean(): void
    {
        $reflection = new \ReflectionMethod(SaeCreateController::class, 'support');
        $returnType = $reflection->getReturnType();

        $this->assertNotNull($returnType);

        if (method_exists($returnType, 'getName')) {
            $this->assertEquals('bool', $returnType->getName());
        } else {
            $this->assertEquals('bool', (string)$returnType);
        }
    }

    /**
     * DATA PROVIDERS FOR THE UNIT TESTS OF support()
     */
    public static function validSupportProvider(): array
    {
        return [
            'chemin valide avec méthode GET' => ['/sae/create', 'GET', true],
        ];
    }

    public static function invalidPathProvider(): array
    {
        return [
            'chemin invalide' => ['/invalid', 'GET', false],
            'chemin vide' => ['', 'GET', false],
            'chemin similaire 1' => ['/sae/create/', 'GET', false],
            'chemin similaire 2' => ['/sae/create/extra', 'GET', false],
            'chemin avec underscore' => ['/sae_create', 'GET', false],
            'chemin en majuscules' => ['/SAE/CREATE', 'GET', false],
            'chemin avec espaces' => ['/sae/create ', 'GET', false],
        ];
    }

    public static function invalidMethodProvider(): array
    {
        return [
            'méthode POST' => ['/sae/create', 'POST', false],
            'méthode PUT' => ['/sae/create', 'PUT', false],
            'méthode DELETE' => ['/sae/create', 'DELETE', false],
            'méthode PATCH' => ['/sae/create', 'PATCH', false],
            'méthode vide' => ['/sae/create', '', false],
            'méthode en minuscules' => ['/sae/create', 'get', false],
            'méthode mixte' => ['/sae/create', 'Get', false],
        ];
    }

    public static function completelyInvalidProvider(): array
    {
        return [
            'chemin et méthode invalides' => ['/invalid', 'POST', false],
            'chemin vide et méthode invalide' => ['', 'POST', false],
            'chemin invalide et méthode vide' => ['/invalid', '', false],
            'tout vide' => ['', '', false],
        ];
    }

    /**
     * UNIT TESTS FOR support() WITH DATA PROVIDERS
     */
    #[Test]
    #[DataProvider('validSupportProvider')]
    public function supportReturnsTrueForValidCases(string $path, string $method, bool $expected): void
    {
        $result = SaeCreateController::support($path, $method);
        $this->assertSame($expected, $result);
    }

    #[Test]
    #[DataProvider('invalidPathProvider')]
    public function supportReturnsFalseForInvalidPaths(string $path, string $method, bool $expected): void
    {
        $result = SaeCreateController::support($path, $method);
        $this->assertSame($expected, $result);
    }

    #[Test]
    #[DataProvider('invalidMethodProvider')]
    public function supportReturnsFalseForInvalidMethods(string $path, string $method, bool $expected): void
    {
        $result = SaeCreateController::support($path, $method);
        $this->assertSame($expected, $result);
    }

    #[Test]
    #[DataProvider('completelyInvalidProvider')]
    public function supportReturnsFalseForCompletelyInvalidCases(string $path, string $method, bool $expected): void
    {
        $result = SaeCreateController::support($path, $method);
        $this->assertSame($expected, $result);
    }

    /**
     * UNIT TESTS FOR support() - SPECIFIC CASES
     */
    #[Test]
    public function supportIsCaseSensitiveForMethod(): void
    {
        $this->assertFalse(SaeCreateController::support('/sae/create', 'get'));
        $this->assertFalse(SaeCreateController::support('/sae/create', 'Get'));
        $this->assertTrue(SaeCreateController::support('/sae/create', 'GET'));
    }

    #[Test]
    public function supportIsExactMatchForPath(): void
    {
        $this->assertTrue(SaeCreateController::support('/sae/create', 'GET'));
        $this->assertFalse(SaeCreateController::support('/sae/create/', 'GET'));
        $this->assertFalse(SaeCreateController::support('/sae/create/extra', 'GET'));
    }

    /**
     * UNIT TESTS OF ROBUSTNESS
     */
    #[Test]
    public function supportHandlesVariousInputFormats(): void
    {
        $testCases = [
            ['path' => '/sae/create', 'method' => 'GET', 'expected' => true],
            ['path' => '/sae/create', 'method' => 'GET ', 'expected' => false],
            ['path' => ' /sae/create', 'method' => 'GET', 'expected' => false],
            ['path' => '/sae/create', 'method' => ' GET', 'expected' => false],
        ];

        foreach ($testCases as $case) {
            $result = SaeCreateController::support($case['path'], $case['method']);
            $this->assertSame($case['expected'], $result);
        }
    }

    #[Test]
    public function controllerFollowsNamingConventions(): void
    {
        $reflection = new \ReflectionClass($this->controller);

        $this->assertStringEndsWith('Controller', $reflection->getShortName());
        $this->assertTrue($reflection->hasMethod('control'));
        $this->assertTrue($reflection->hasMethod('support'));
    }

    #[Test]
    public function multipleControllerInstancesAreIndependent(): void
    {
        $controller1 = new SaeCreateController();
        $controller2 = new SaeCreateController();

        $this->assertNotSame($controller1, $controller2);
        $this->assertInstanceOf(\Core\Controllers\ControllerInterface::class, $controller1);
        $this->assertInstanceOf(\Core\Controllers\ControllerInterface::class, $controller2);
    }
}
