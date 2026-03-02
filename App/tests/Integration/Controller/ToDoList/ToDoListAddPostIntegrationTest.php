<?php

namespace Tests\Integration\Controller\ToDoList;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Controllers\ToDoList\ToDoListAddPostController;
use Models\Entity\ToDoItem\ToDoItem;

/**
 * Integration test for ToDoListAddPostController controller interacting with ToDoList model.
 *
 * @category Tests
 * @package  Tests\Controllers\ToDoList
 * @author   Alexandre Benhafessa <alexandre.benhafessa@etu.univ-amu.fr>
 * @author   François Dargentolle <francois.dargentolle@etu.univ-amu.fr>
 * @author   William Edelstein <william.edelstein@etu.univ-amu.fr>
 * @author   Nathan Griguer <nathan.griguer@etu.univ-amu.fr>
 * @author   Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license  MIT License https://opensource.org/licenses/MIT
 * @link     https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
#[CoversClass(ToDoItem::class)]
#[CoversClass(ToDoListAddPostController::class)]
class ToDoListAddPostIntegrationTest extends TestCase
{
    /**
     * Test that the controller supports POST method for the correct route.
     *
     * @return void
     */
    public function testSupportReturnsTrueForValidPostRoute(): void
    {
        $this->assertTrue(ToDoListAddPostController::support('/sae/1/to-do/add', 'POST'));
    }

    /**
     * Test that the controller does not support invalid routes or methods.
     *
     * @return void
     */
    public function testSupportReturnsFalseForInvalidRouteOrMethod(): void
    {
        $this->assertFalse(ToDoListAddPostController::support('/invalid', 'POST'));
        $this->assertFalse(ToDoListAddPostController::support('/to-do-list', 'GET'));
    }
}
