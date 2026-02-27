<?php

namespace Tests\Integration\Controller\ToDoList;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Controllers\ToDoList\ToDoListUpdatePost;
use Models\Entity\ToDoItem\ToDoItem;

/**
 * Integration test for ToDoListUpdatePost controller interacting with ToDoList model.
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
#[CoversClass(ToDoListUpdatePost::class)]
class ToDoListUpdatePostIntegrationTest extends TestCase
{
    /**
     * Test that the controller supports POST method for the correct route for update.
     *
     * @return void
     */
    public function testSupportReturnsTrueForValidPostRoute(): void
    {
        $this->assertTrue(ToDoListUpdatePost::support('/sae/10/to-do/update/5', 'POST'));
    }

    /**
     * Test that the controller does not support invalid routes or methods for update.
     *
     * @return void
     */
    public function testSupportReturnsFalseForInvalidRouteOrMethod(): void
    {
        $this->assertFalse(ToDoListUpdatePost::support('/invalid', 'POST'));
        $this->assertFalse(ToDoListUpdatePost::support('/to-do-list/update/5', 'GET'));
    }
}
