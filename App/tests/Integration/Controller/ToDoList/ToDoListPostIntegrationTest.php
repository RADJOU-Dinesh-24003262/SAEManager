<?php

namespace test\Integration\Controller\ToDoList;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Controllers\ToDoList\ToDoListPost;
use App\Models\ToDoList\ToDoList;

/**
 * Integration test for ToDoListPost controller interacting with ToDoList model.
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
#[CoversClass(ToDoList::class)]
#[CoversClass(ToDoListPost::class)]
class ToDoListPostIntegrationTest extends TestCase
{
    /**
     * Test that the controller supports POST method for the correct route.
     *
     * @return void
     */
    public function testSupportReturnsTrueForValidPostRoute(): void
    {
        $this->assertTrue(ToDoListPost::support('/sae/1/to-do/add', 'POST'));
    }

    /**
     * Test that the controller does not support invalid routes or methods.
     *
     * @return void
     */
    public function testSupportReturnsFalseForInvalidRouteOrMethod(): void
    {
        $this->assertFalse(ToDoListPost::support('/invalid', 'POST'));
        $this->assertFalse(ToDoListPost::support('/to-do-list', 'GET'));
    }
}
