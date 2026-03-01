<?php

namespace Tests\Unit\ToDoList;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Controllers\ToDoList\ToDoListUpdatePostController;

/**
 * Unit test for ToDoListUpdatePost class.
 *
 * @category Tests
 * @package  Tests\Controllers\ToDoList
 * @author   Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license  MIT License https://opensource.org/licenses/MIT
 * @link     https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
#[CoversClass(ToDoListUpdatePostController::class)]
class ToDoListUpdatePostTest extends TestCase
{
    /**
     * Test that the controller supports the correct path and HTTP method for update.
     *
     * @return void
     */
    public function testSupportReturnsTrueForUpdateAction(): void
    {
        $this->assertTrue(ToDoListUpdatePostController::support('/sae/10/to-do/update/5', 'POST'));
    }

    /**
     * Test that the controller does not support invalid paths or HTTP methods.
     *
     * @return void
     */
    public function testSupportReturnsFalseForInvalidPathOrMethod(): void
    {
        $this->assertFalse(ToDoListUpdatePostController::support('/sae/10/to-do/update/5', 'GET')); // Wrong method
        $this->assertFalse(ToDoListUpdatePostController::support('/to-do-list/update/5', 'POST')); // Old path
        $this->assertFalse(ToDoListUpdatePostController::support('/sae/10/to-do/unknown/5', 'POST')); // Invalid action
    }
}
