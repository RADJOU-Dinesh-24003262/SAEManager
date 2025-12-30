<?php

namespace Tests\Unit\Utilis;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\DataProvider;
use Core\Utilis\SessionService;
use stdClass;

/**
 * Unit tests for SessionService class.
 */
#[CoversClass(SessionService::class)]
class SessionServiceTest extends TestCase
{
    /**
     * Setup before each test.
     */
    protected function setUp(): void
    {
        parent::setUp();
        // Clear session before each test
        $_SESSION = [];
    }

    /**
     * Clean up after each test.
     */
    protected function tearDown(): void
    {
        // Clear session after each test
        $_SESSION = [];
        parent::tearDown();
    }

    // ========================================
    // Basic Tests set/get
    // ========================================
    #[Test]
    public function canSetAndGetSimpleValue(): void
    {
        SessionService::set('key', 'value');

        $this->assertEquals('value', SessionService::get('key'));
    }

    #[Test]
    public function canSetAndGetArray(): void
    {
        $data = ['name' => 'John', 'age' => 30];
        SessionService::set('user', $data);

        $this->assertEquals($data, SessionService::get('user'));
    }

    #[Test]
    public function canSetAndGetObject(): void
    {
        $obj = new stdClass();
        $obj->name = 'Test';

        SessionService::set('object', $obj);

        $retrieved = SessionService::get('object');
        $this->assertInstanceOf(stdClass::class, $retrieved);
        $this->assertEquals('Test', $retrieved->name);
    }

    #[Test]
    public function getNonExistentKeyReturnsDefault(): void
    {
        $result = SessionService::get('nonexistent', 'default');

        $this->assertEquals('default', $result);
    }

    #[Test]
    public function getNonExistentKeyReturnsNullByDefault(): void
    {
        $result = SessionService::get('nonexistent');

        $this->assertNull($result);
    }

    // ========================================
    // Tests has()
    // ========================================
    #[Test]
    public function hasReturnsTrueForExistingKey(): void
    {
        SessionService::set('existing', 'value');

        $this->assertTrue(SessionService::has('existing'));
    }

    #[Test]
    public function hasReturnsFalseForNonExistingKey(): void
    {
        $this->assertFalse(SessionService::has('nonexistent'));
    }

    #[Test]
    public function hasReturnsTrueForEmptyString(): void
    {
        SessionService::set('empty', '');

        $this->assertTrue(SessionService::has('empty'));
    }

    #[Test]
    public function hasReturnsTrueForZero(): void
    {
        SessionService::set('zero', 0);

        $this->assertTrue(SessionService::has('zero'));
    }

    #[Test]
    public function hasReturnsTrueForFalse(): void
    {
        SessionService::set('false', false);

        $this->assertTrue(SessionService::has('false'));
    }

    // ========================================
    // Tests remove()
    // ========================================
    #[Test]
    public function removeDeletesKey(): void
    {
        SessionService::set('to_remove', 'value');
        SessionService::remove('to_remove');

        $this->assertFalse(SessionService::has('to_remove'));
    }

    #[Test]
    public function removeNonExistentKeyDoesNotError(): void
    {
        $this->expectNotToPerformAssertions();

        SessionService::remove('nonexistent');
    }

    #[Test]
    public function removeDoesNotAffectOtherKeys(): void
    {
        SessionService::set('keep', 'value1');
        SessionService::set('remove', 'value2');

        SessionService::remove('remove');

        $this->assertTrue(SessionService::has('keep'));
        $this->assertEquals('value1', SessionService::get('keep'));
    }

    // ========================================
    // Tests Flash messages
    // ========================================
    #[Test]
    public function flashMessageIsReadOnce(): void
    {
        SessionService::setFlash('message', 'Hello');

        $first = SessionService::getFlash('message');
        $second = SessionService::getFlash('message');

        $this->assertEquals('Hello', $first);
        $this->assertNull($second);
    }

    #[Test]
    public function flashMessageWithDefault(): void
    {
        $result = SessionService::getFlash('nonexistent', 'default');

        $this->assertEquals('default', $result);
    }

    #[Test]
    public function canSetMultipleFlashMessages(): void
    {
        SessionService::setFlash('msg1', 'Hello');
        SessionService::setFlash('msg2', 'World');

        $this->assertEquals('Hello', SessionService::getFlash('msg1'));
        $this->assertEquals('World', SessionService::getFlash('msg2'));
    }

    #[Test]
    public function hasFlashReturnsTrueForExistingFlash(): void
    {
        SessionService::setFlash('flash', 'value');

        $this->assertTrue(SessionService::hasFlash('flash'));
    }

    #[Test]
    public function hasFlashReturnsFalseForNonExistingFlash(): void
    {
        $this->assertFalse(SessionService::hasFlash('nonexistent'));
    }

    #[Test]
    public function hasFlashReturnsFalseAfterFlashIsRead(): void
    {
        SessionService::setFlash('flash', 'value');
        SessionService::getFlash('flash'); // Consume the flash

        $this->assertFalse(SessionService::hasFlash('flash'));
    }

    #[Test]
    public function flashCanStoreArrays(): void
    {
        $errors = ['error1', 'error2'];
        SessionService::setFlash('errors', $errors);

        $retrieved = SessionService::getFlash('errors');
        $this->assertEquals($errors, $retrieved);
    }

    // ========================================
    // Tests with various data types
    // ========================================
    #[Test]
    #[DataProvider('variousDataTypesProvider')]
    public function canStoreVariousDataTypes(mixed $value): void
    {
        SessionService::set('test', $value);
        $retrieved = SessionService::get('test');

        $this->assertEquals($value, $retrieved);
    }

    public static function variousDataTypesProvider(): array
    {
        return [
            'string' => ['hello'],
            'integer' => [42],
            'float' => [3.14],
            'boolean_true' => [true],
            'boolean_false' => [false],
            'null' => [null],
            'array' => [['a', 'b', 'c']],
            'nested_array' => [['user' => ['name' => 'John', 'age' => 30]]],
        ];
    }

    // ========================================
    // Security Tests
    // ========================================
    #[Test]
    public function sessionDataIsIsolatedBetweenKeys(): void
    {
        SessionService::set('user1', 'data1');
        SessionService::set('user2', 'data2');

        SessionService::remove('user1');

        $this->assertFalse(SessionService::has('user1'));
        $this->assertTrue(SessionService::has('user2'));
        $this->assertEquals('data2', SessionService::get('user2'));
    }

    #[Test]
    public function canOverwriteExistingValue(): void
    {
        SessionService::set('key', 'old');
        SessionService::set('key', 'new');

        $this->assertEquals('new', SessionService::get('key'));
    }

    #[Test]
    public function flashDoesNotInterfereWithRegularSession(): void
    {
        SessionService::set('regular', 'value');
        SessionService::setFlash('flash', 'flash_value');

        $this->assertTrue(SessionService::has('regular'));
        $this->assertTrue(SessionService::hasFlash('flash'));

        SessionService::getFlash('flash'); // Consume flash

        $this->assertTrue(SessionService::has('regular'));
        $this->assertFalse(SessionService::hasFlash('flash'));
    }

    // ========================================
    // Tests with edge cases
    // ========================================
    #[Test]
    public function canStoreEmptyString(): void
    {
        SessionService::set('empty', '');

        $this->assertTrue(SessionService::has('empty'));
        $this->assertEquals('', SessionService::get('empty'));
    }

    #[Test]
    public function canStoreZero(): void
    {
        SessionService::set('zero', 0);

        $this->assertTrue(SessionService::has('zero'));
        $this->assertEquals(0, SessionService::get('zero'));
    }

    #[Test]
    public function canStoreFalse(): void
    {
        SessionService::set('false', false);

        $this->assertTrue(SessionService::has('false'));
        $this->assertFalse(SessionService::get('false'));
    }

    #[Test]
    public function canStoreLargeArray(): void
    {
        $largeArray = array_fill(0, 1000, 'value');
        SessionService::set('large', $largeArray);

        $retrieved = SessionService::get('large');
        $this->assertCount(1000, $retrieved);
    }

    #[Test]
    public function canStoreNestedArrays(): void
    {
        $nested = [
            'level1' => [
                'level2' => [
                    'level3' => 'deep value'
                ]
            ]
        ];

        SessionService::set('nested', $nested);
        $retrieved = SessionService::get('nested');

        $this->assertEquals('deep value', $retrieved['level1']['level2']['level3']);
    }

    // ========================================
    // Tests of performance
    // ========================================
    #[Test]
    public function multipleOperationsAreEfficient(): void
    {
        $start = microtime(true);

        for ($i = 0; $i < 100; $i++) {
            SessionService::set("key$i", "value$i");
            SessionService::get("key$i");
            SessionService::has("key$i");
        }

        $duration = microtime(true) - $start;

        // 300 operations should take less than 100ms
        $this->assertLessThan(0.1, $duration);
    }

    #[Test]
    public function sessionServiceIsStateless(): void
    {
        $service1 = SessionService::class;
        $service2 = SessionService::class;

        $this->assertEquals($service1, $service2);
    }
}
