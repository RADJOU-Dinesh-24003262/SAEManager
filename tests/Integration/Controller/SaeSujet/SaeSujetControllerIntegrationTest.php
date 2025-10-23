<?php

declare(strict_types=1);

namespace Tests\Unit\Controller\SaeSujet;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Controllers\SaeSujet\SaeSujetController;

/**
 * Integration tests for SaeSujetController
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
     * INTEGRATION TESTS - GLOBAL BEHAVIOR
     */
    #[Test]
    public function supportMethodWorksWithoutControllerInstance(): void
    {
        // INTEGRATION TEST : the static method works without instance.
        $result = SaeSujetController::support('/new-sae', 'GET');
        $this->assertTrue($result);
    }

    #[Test]
    public function integrationTestWithReflection(): void
    {
        // Integration test use reflection to check intern state.
        $controller = new SaeSujetController();

        $reflection = new \ReflectionClass($controller);

        // Check that the class has the right method.
        $this->assertTrue($reflection->hasMethod('control'));
        $this->assertTrue($reflection->hasMethod('support'));

        // Check that support is static.
        $supportMethod = $reflection->getMethod('support');
        $this->assertTrue($supportMethod->isStatic());

        // Check thaht control is public and of instance.
        $controlMethod = $reflection->getMethod('control');
        $this->assertTrue($controlMethod->isPublic());
        $this->assertFalse($controlMethod->isStatic());
    }
}
