<?php

namespace tests\Unit\ToDoList;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Controllers\ToDoList\ToDoListController;

/**
 * Unit test for ToDoListController class.
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
#[CoversClass(ToDoListController::class)]
class ToDoListControllerTest extends TestCase
{
    /**
     * Test that the controller supports the correct path and HTTP method.
     *
     * @return void
     */
    public function testSupportReturnsTrueForValidPathAndMethod(): void
    {
        $this->assertTrue(ToDoListController::support('/to-do-list', 'GET'));
    }

    /**
     * Test that the controller does not support invalid paths or HTTP methods.
     *
     * @return void
     */
    public function testSupportReturnsFalseForInvalidPathOrMethod(): void
    {
        $this->assertFalse(ToDoListController::support('/invalid', 'GET'));
        $this->assertFalse(ToDoListController::support('/to-do-list', 'POST'));
    }
}
