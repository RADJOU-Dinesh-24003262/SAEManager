<?php

declare(strict_types=1);

namespace Tests\Unit\SaeSujet;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Controllers\SaeSujet\SaeSujetController;

/**
 * Tests unitaires pour SaeSujetController
 *
 * @category Test
 * 
 * @package Tests
 * 
 * @author  Alexandre Benhafessa <alexandre.benhafessa@etu.univ-amu.fr>
 * @author  François Dargentolle <francois.dargentolle@etu.univ-amu.fr>
 * @author  William Edelstein <william.edelstein@etu.univ-amu.fr>
 * @author  Nathan Griguer <nathan.griguer@etu.univ-amu.fr>
 * @author  Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * 
 * @license MIT License https://opensource.org/licenses/MIT
 * 
 * @link https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
#[CoversClass(SaeSujetController::class)]
class SaeSujetControllerUnitTest extends TestCase
{
    private SaeSujetController $controller;

    protected function setUp(): void
    {
        $this->controller = new SaeSujetController();
    }

    /**
     * TESTS D'INSTANCIATION ET D'INTERFACE
     */
    #[Test]
    public function controllerImplementsControllerInterface(): void
    {
        $this->assertInstanceOf(
            'Controllers\ControllerInterface',
            $this->controller
        );
    }

    #[Test]
    public function controllerHasCorrectNamespace(): void
    {
        $this->assertStringContainsString(
            'Controllers\SaeSujet',
            get_class($this->controller)
        );
    }

    #[Test]
    public function controllerCanBeInstantiatedWithoutErrors(): void
    {
        $this->assertInstanceOf(
            SaeSujetController::class,
            $this->controller
        );
    }

    /**
     * TESTS UNITAIRES DE LA MÉTHODE control()
     */
    #[Test]
    public function controlMethodReturnsVoid(): void
    {
        $reflection = new \ReflectionMethod($this->controller, 'control');
        $returnType = $reflection->getReturnType();

        $this->assertNotNull($returnType);
        // REMPLACER : $this->assertEquals('void', $returnType->getName()); PAR :
        $this->assertEquals('void', (string)$returnType);
    }

    #[Test]
    public function controlMethodHasNoParameters(): void
    {
        $reflection = new \ReflectionMethod($this->controller, 'control');
        $parameters = $reflection->getParameters();

        $this->assertCount(0, $parameters);
    }

    /**
     * TESTS UNITAIRES DE LA MÉTHODE support() - CARACTÉRISTIQUES
     */
    #[Test]
    public function supportMethodIsStatic(): void
    {
        $reflection = new \ReflectionMethod(SaeSujetController::class, 'support');
        $this->assertTrue($reflection->isStatic());
    }

    #[Test]
    public function supportMethodHasCorrectParameters(): void
    {
        $reflection = new \ReflectionMethod(SaeSujetController::class, 'support');
        $parameters = $reflection->getParameters();

        $this->assertCount(2, $parameters);
        $this->assertEquals('path', $parameters[0]->getName());
        $this->assertEquals('method', $parameters[1]->getName());
        // REMPLACER :
        // $this->assertEquals('string', $parameters[0]->getType()->getName());
        // $this->assertEquals('string', $parameters[1]->getType()->getName());
        // PAR :
        $this->assertEquals('string', (string)$parameters[0]->getType());
        $this->assertEquals('string', (string)$parameters[1]->getType());
    }

    #[Test]
    public function supportMethodReturnsBoolean(): void
    {
        $reflection = new \ReflectionMethod(SaeSujetController::class, 'support');
        $returnType = $reflection->getReturnType();

        $this->assertNotNull($returnType);

        // ALTERNATIVE COMPATIBLE :
        if (method_exists($returnType, 'getName')) {
            $this->assertEquals('bool', $returnType->getName());
        } else {
            $this->assertEquals('bool', (string)$returnType);
        }
    }

    /**
     * DATA PROVIDERS POUR LES TESTS UNITAIRES support()
     */
    public static function validSupportProvider(): array
    {
        return [
            'chemin valide avec méthode GET' => ['/new-sae', 'GET', true],
        ];
    }

    public static function invalidPathProvider(): array
    {
        return [
            'chemin invalide' => ['/invalid', 'GET', false],
            'chemin vide' => ['', 'GET', false],
            'chemin similaire 1' => ['/new-sae/', 'GET', false],
            'chemin similaire 2' => ['/new-sae/extra', 'GET', false],
            'chemin avec underscore' => ['/new_sae', 'GET', false],
            'chemin en majuscules' => ['/NEW-SAE', 'GET', false],
            'chemin avec espaces' => ['/new-sae ', 'GET', false],
        ];
    }

    public static function invalidMethodProvider(): array
    {
        return [
            'méthode POST' => ['/new-sae', 'POST', false],
            'méthode PUT' => ['/new-sae', 'PUT', false],
            'méthode DELETE' => ['/new-sae', 'DELETE', false],
            'méthode PATCH' => ['/new-sae', 'PATCH', false],
            'méthode vide' => ['/new-sae', '', false],
            'méthode en minuscules' => ['/new-sae', 'get', false],
            'méthode mixte' => ['/new-sae', 'Get', false],
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
     * TESTS UNITAIRES support() AVEC DATA PROVIDERS
     */
    #[Test]
    #[DataProvider('validSupportProvider')]
    public function supportReturnsTrueForValidCases(string $path, string $method, bool $expected): void
    {
        $result = SaeSujetController::support($path, $method);
        $this->assertSame($expected, $result);
    }

    #[Test]
    #[DataProvider('invalidPathProvider')]
    public function supportReturnsFalseForInvalidPaths(string $path, string $method, bool $expected): void
    {
        $result = SaeSujetController::support($path, $method);
        $this->assertSame($expected, $result);
    }

    #[Test]
    #[DataProvider('invalidMethodProvider')]
    public function supportReturnsFalseForInvalidMethods(string $path, string $method, bool $expected): void
    {
        $result = SaeSujetController::support($path, $method);
        $this->assertSame($expected, $result);
    }

    #[Test]
    #[DataProvider('completelyInvalidProvider')]
    public function supportReturnsFalseForCompletelyInvalidCases(string $path, string $method, bool $expected): void
    {
        $result = SaeSujetController::support($path, $method);
        $this->assertSame($expected, $result);
    }

    /**
     * TESTS UNITAIRES support() - CAS SPÉCIFIQUES
     */
    #[Test]
    public function supportIsCaseSensitiveForMethod(): void
    {
        $this->assertFalse(SaeSujetController::support('/new-sae', 'get'));
        $this->assertFalse(SaeSujetController::support('/new-sae', 'Get'));
        $this->assertTrue(SaeSujetController::support('/new-sae', 'GET'));
    }

    #[Test]
    public function supportIsExactMatchForPath(): void
    {
        $this->assertTrue(SaeSujetController::support('/new-sae', 'GET'));
        $this->assertFalse(SaeSujetController::support('/new-sae/', 'GET'));
        $this->assertFalse(SaeSujetController::support('/new-sae/extra', 'GET'));
    }

    /**
     * TESTS UNITAIRES DE ROBUSTESSE
     */
    #[Test]
    public function supportHandlesVariousInputFormats(): void
    {
        $testCases = [
            ['path' => '/new-sae', 'method' => 'GET', 'expected' => true],
            ['path' => '/new-sae', 'method' => 'GET ', 'expected' => false],
            ['path' => ' /new-sae', 'method' => 'GET', 'expected' => false],
            ['path' => '/new-sae', 'method' => ' GET', 'expected' => false],
        ];

        foreach ($testCases as $case) {
            $result = SaeSujetController::support($case['path'], $case['method']);
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
        $controller1 = new SaeSujetController();
        $controller2 = new SaeSujetController();

        $this->assertNotSame($controller1, $controller2);
        $this->assertInstanceOf(SaeSujetController::class, $controller1);
        $this->assertInstanceOf(SaeSujetController::class, $controller2);
    }
}
