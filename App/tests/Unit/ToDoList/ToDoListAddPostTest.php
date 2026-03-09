<?php

namespace Tests\Unit\ToDoList;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Controllers\Sae\SaeToDoAddPostController;

/**
 * Unit test for ToDoListAddPost class.
 *
 * @category Tests
 * @package  Tests\Controllers\ToDoList
 * @author   Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license  MIT License https://opensource.org/licenses/MIT
 * @link     https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
#[CoversClass(SaeToDoAddPostController::class)]
class ToDoListAddPostTest extends TestCase
{
    /**
     * Test that the controller supports the correct path and HTTP method for add.
     *
     * @return void
     */
    public function testSupportReturnsTrueForAddAction(): void
    {
        $this->assertTrue(SaeToDoAddPostController::support('/sae/1/to-do/add', 'POST'));
    }

    /**
     * Test that the controller does not support invalid paths or HTTP methods.
     *
     * @return void
     */
    public function testSupportReturnsFalseForInvalidPathOrMethod(): void
    {
        $this->assertFalse(SaeToDoAddPostController::support('/sae/1/to-do/add', 'GET')); // Wrong method
        $this->assertFalse(SaeToDoAddPostController::support('/to-do-list', 'POST')); // Old path
        $this->assertFalse(SaeToDoAddPostController::support('/sae/1/to-do/unknown', 'POST')); // Invalid action
    }
}
