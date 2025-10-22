<?php

declare(strict_types=1);

namespace Tests\Unit\Controller\SaeSujet;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Controllers\SaeSujet\SaeSujetController;

/**
 * Tests d'intégration pour SaeSujetController
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
class SaeSujetControllerIntegrationTest extends TestCase
{
    /**
     * TESTS D'INTÉGRATION - COMPORTEMENT GLOBAL.
     */
    #[Test]
    public function supportMethodWorksWithoutControllerInstance(): void
    {
        // Test d'intégration : la méthode statique fonctionne sans instance.
        $result = SaeSujetController::support('/new-sae', 'GET');
        $this->assertTrue($result);
    }

    #[Test]
    public function integrationTestWithReflection(): void
    {
        // Test d'intégration utilisant la réflexion pour vérifier l'état interne.
        $controller = new SaeSujetController();

        $reflection = new \ReflectionClass($controller);

        // Vérifie que la classe a les méthodes attendues.
        $this->assertTrue($reflection->hasMethod('control'));
        $this->assertTrue($reflection->hasMethod('support'));

        // Vérifie que support est bien statique.
        $supportMethod = $reflection->getMethod('support');
        $this->assertTrue($supportMethod->isStatic());

        // Vérifie que control est bien public et d'instance.
        $controlMethod = $reflection->getMethod('control');
        $this->assertTrue($controlMethod->isPublic());
        $this->assertFalse($controlMethod->isStatic());
    }
}
