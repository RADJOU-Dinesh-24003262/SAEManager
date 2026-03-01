<?php

namespace Tests\Integration\Controller\ToDoList;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Controllers\ToDoList\ToDoListDeletePostController;
use Models\Entity\ToDoItem\ToDoItem;

/**
 * Integration test for ToDoListDeletePostController controller interacting with ToDoList model.
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
#[CoversClass(ToDoListDeletePostController::class)]
class ToDoListDeletePostIntegrationTest extends TestCase
{
    /**
     * Test that the controller supports POST method for the correct route for delete.
     *
     * @return void
     */
    public function testSupportReturnsTrueForValidPostRoute(): void
    {
        $this->assertTrue(ToDoListDeletePostController::support('/sae/99/to-do/delete/123', 'POST'));
    }

    /**
     * Test that the controller does not support invalid routes or methods for delete.
     *
     * @return void
     */
    public function testSupportReturnsFalseForInvalidRouteOrMethod(): void
    {
        $this->assertFalse(ToDoListDeletePostController::support('/invalid', 'POST'));
        $this->assertFalse(ToDoListDeletePostController::support('/to-do-list/delete/123', 'GET'));
    }
}
