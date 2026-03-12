<?php

namespace Tests\Unit\Models\Repository\ToDoList;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Models\Repository\ToDoList\PdoToDoListRepository;
use Models\Entity\ToDoItem\ToDoItem;
use Core\Includes\Database;
use PDO;
use PDOStatement;

/**
 * Unit test for PdoToDoListRepository interactions with PDO.
 *
 * @category Tests
 * @package  Tests\Unit\Models\Repository\ToDoList
 * @author   Ai Assistant
 */
#[CoversClass(PdoToDoListRepository::class)]
class PdoToDoListRepositoryTest extends TestCase
{
    private PDO $mockPdo;
    private PDOStatement $mockStmt;
    private PdoToDoListRepository $repository;

    protected function setUp(): void
    {
        // Mock PDO and PDOStatement
        $this->mockPdo = $this->createMock(PDO::class);
        $this->mockStmt = $this->createMock(PDOStatement::class);
        
        // Disable original constructor which hits Database singleton
        $this->repository = $this->getMockBuilder(PdoToDoListRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();
        
        $reflectionRepo = new \ReflectionClass(PdoToDoListRepository::class);
        $tableProp = $reflectionRepo->getProperty('table');
        $tableProp->setAccessible(true);
        $tableProp->setValue($this->repository, 'sae_todolists');

        $entityClassProp = $reflectionRepo->getProperty('entityClass');
        $entityClassProp->setAccessible(true);
        $entityClassProp->setValue($this->repository, ToDoItem::class);
        
        // Force the connection property for safety
        $refConn = $reflectionRepo->getParentClass()->getProperty('connection');
        $connProp = $refConn;
        $connProp->setAccessible(true);
        $connProp->setValue($this->repository, $this->mockPdo);
    }

    protected function tearDown(): void
    {
    }

    /**
     * Test inserting a task binds end_date, priority and checked successfully.
     */
    public function testInsertBindsEndDatePriorityAndChecked(): void
    {
        $task = new ToDoItem([
            'sae_group_id' => 10,
            'tododesc' => 'Test task with date',
            'priority' => 3,
            'checked' => false,
            'end_date' => '2026-05-15'
        ]);

        $this->mockPdo->expects($this->once())
            ->method('beginTransaction');

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('INSERT INTO sae_todolists'))
            ->willReturn($this->mockStmt);

        // Expect exact bindings including end_date, priority, checked
        $matcher = $this->exactly(5);
        $this->mockStmt->expects($matcher)
            ->method('bindValue')
            ->willReturnCallback(function($param, $value, $type) use ($matcher) {
                $expected = [
                    1 => [':sae_group_id', 10, PDO::PARAM_INT],
                    2 => [':tododesc', 'Test task with date', PDO::PARAM_STR],
                    3 => [':checked', false, PDO::PARAM_BOOL],
                    4 => [':priority', 3, PDO::PARAM_INT],
                    5 => [':end_date', '2026-05-15', PDO::PARAM_STR],
                ][$matcher->numberOfInvocations()];

                $this->assertEquals($expected[0], $param);
                $this->assertEquals($expected[1], $value);
                $this->assertEquals($expected[2], $type);
                return true;
            });

        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        $this->mockPdo->expects($this->once())
            ->method('lastInsertId')
            ->willReturn('42');

        $this->mockPdo->expects($this->once())
            ->method('commit');

        $result = $this->repository->insert($task);

        $this->assertEquals(42, $result);
    }

    /**
     * Test inserting a task with null end_date properly binds parameter as NULL.
     */
    public function testInsertBindsNullEndDate(): void
    {
        $task = new ToDoItem([
            'sae_group_id' => 10,
            'tododesc' => 'Test task no date',
            'priority' => 1,
            'checked' => true,
            'end_date' => null
        ]);

        $this->mockPdo->method('prepare')->willReturn($this->mockStmt);
        $this->mockStmt->method('execute')->willReturn(true);

        $matcher = $this->exactly(5);
        $this->mockStmt->expects($matcher)
            ->method('bindValue')
            ->willReturnCallback(function($param, $value, $type) use ($matcher) {
                $expected = [
                    1 => [':sae_group_id', 10, PDO::PARAM_INT],
                    2 => [':tododesc', 'Test task no date', PDO::PARAM_STR],
                    3 => [':checked', true, PDO::PARAM_BOOL],
                    4 => [':priority', 1, PDO::PARAM_INT],
                    5 => [':end_date', null, PDO::PARAM_NULL],
                ][$matcher->numberOfInvocations()];

                $this->assertEquals($expected[0], $param);
                $this->assertEquals($expected[1], $value);
                $this->assertEquals($expected[2], $type);
                return true;
            });

        $this->repository->insert($task);
    }

    /**
     * Test updating a task appropriately sets new priorities, checked status, and dates.
     */
    public function testUpdateBindsAppropriateVariables(): void
    {
        $task = new ToDoItem([
            'todoid' => 42,
            'sae_group_id' => 10,
            'tododesc' => 'Modified description',
            'priority' => 2,
            'checked' => true,
            'end_date' => '2026-10-31'
        ]);

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('UPDATE sae_todolists SET'))
            ->willReturn($this->mockStmt);

        // Bind should happen for all 6 arguments including PK where clause
        $this->mockStmt->expects($this->exactly(6))
            ->method('bindValue');

        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        $result = $this->repository->update($task);

        $this->assertTrue($result);
    }

    /**
     * Test retrieving tasks dynamically maps checked boolean along with existing fields.
     */
    public function testFindByGroupIdMapsFieldsProperly(): void
    {
        $mockData = [
            [
                'todoid' => 1,
                'sae_group_id' => 99,
                'tododesc' => 'DB Task 1',
                'priority' => 3,
                'checked' => 1,
                'end_date' => '2026-12-01'
            ]
        ];

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->with('SELECT * FROM sae_todolists WHERE sae_group_id = :groupId ORDER BY priority ASC, todoid ASC')
            ->willReturn($this->mockStmt);

        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->with(['groupId' => 99]);

        $this->mockStmt->expects($this->once())
            ->method('fetchAll')
            ->willReturn($mockData);

        $result = $this->repository->findByGroupId(99);

        $this->assertCount(1, $result);
        $this->assertInstanceOf(ToDoItem::class, $result[0]);
        $this->assertEquals(1, $result[0]->getTodoId());
        $this->assertEquals(3, $result[0]->getPriority());
        $this->assertTrue($result[0]->isChecked()); // 1 should be cast to true
        $this->assertEquals('2026-12-01', $result[0]->getEndDate());
    }
}
