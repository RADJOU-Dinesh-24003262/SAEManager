<?php

namespace Tests\Unit\ToDoList;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Controllers\Sae\SaeToDoDeletePostController;

/**
 * Unit test for ToDoListDeletePost class.
 *
 * @category Tests
 * @package  Tests\Controllers\ToDoList
 * @author   Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license  MIT License https://opensource.org/licenses/MIT
 * @link     https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
#[CoversClass(SaeToDoDeletePostController::class)]
class ToDoListDeletePostTest extends TestCase
{
    /**
     * Test that the controller supports the correct path and HTTP method for delete.
     *
     * @return void
     */
    public function testSupportReturnsTrueForDeleteAction(): void
    {
        $this->assertTrue(SaeToDoDeletePostController::support('/sae/99/to-do/delete/123', 'POST'));
    }

    /**
     * Test that the controller does not support invalid paths or HTTP methods.
     *
     * @return void
     */
    public function testSupportReturnsFalseForInvalidPathOrMethod(): void
    {
        $this->assertFalse(SaeToDoDeletePostController::support('/sae/99/to-do/delete/123', 'GET')); // Wrong method
        $this->assertFalse(SaeToDoDeletePostController::support('/to-do-list/delete/123', 'POST')); // Old path
        $this->assertFalse(SaeToDoDeletePostController::support('/sae/99/to-do/unknown/123', 'POST')); // Invalid action
    }
}
