<?php

namespace tests\Unit\ToDoList;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Controllers\ToDoList\ToDoListPost;

/**
 * Unit test for ToDoListPost class.
 *
 * @category Tests
 * @package  Tests\Controllers\ToDoList
 * @author   Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license  MIT License https://opensource.org/licenses/MIT
 * @link     https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
#[CoversClass(ToDoListPost::class)]
class ToDoListPostTest extends TestCase
{
    /**
     * Test that the controller supports the correct path and HTTP method for add.
     *
     * @return void
     */
    public function testSupportReturnsTrueForAddAction(): void
    {
        $this->assertTrue(ToDoListPost::support('/sae/1/to-do/add', 'POST'));
    }

    /**
     * Test that the controller supports the correct path and HTTP method for update.
     *
     * @return void
     */
    public function testSupportReturnsTrueForUpdateAction(): void
    {
        $this->assertTrue(ToDoListPost::support('/sae/10/to-do/update/5', 'POST'));
    }

    /**
     * Test that the controller supports the correct path and HTTP method for delete.
     *
     * @return void
     */
    public function testSupportReturnsTrueForDeleteAction(): void
    {
        $this->assertTrue(ToDoListPost::support('/sae/99/to-do/delete/123', 'POST'));
    }

    /**
     * Test that the controller does not support invalid paths or HTTP methods.
     *
     * @return void
     */
    public function testSupportReturnsFalseForInvalidPathOrMethod(): void
    {
        $this->assertFalse(ToDoListPost::support('/sae/1/to-do/add', 'GET')); // Wrong method
        $this->assertFalse(ToDoListPost::support('/to-do-list', 'POST')); // Old path
        $this->assertFalse(ToDoListPost::support('/sae/1/to-do/unknown', 'POST')); // Invalid action
    }
}
