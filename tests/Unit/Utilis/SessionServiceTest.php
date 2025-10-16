<?php

namespace tests\Unit\Utilis;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Utilis\SessionService;

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

    /**
     * Test that a session value can be set and retrieved.
     */
    public function testCanSetAndGetSessionValue(): void
    {
        SessionService::set('test_key', 'test_value');

        $this->assertEquals('test_value', SessionService::get('test_key'));
    }

    /**
     * Test checking if a session key exists.
     */
    public function testCanCheckIfSessionKeyExists(): void
    {
        SessionService::set('existing_key', 'value');

        $this->assertTrue(SessionService::has('existing_key'));
        $this->assertFalse(SessionService::has('non_existing_key'));
    }

    /**
     * Test that getting a non-existing key returns the default value.
     */
    public function testReturnsDefaultValueWhenKeyNotExists(): void
    {
        $result = SessionService::get('non_existing', 'default_value');

        $this->assertEquals('default_value', $result);
    }

    /**
     * Test that a session value can be removed.
     */
    public function testCanRemoveSessionValue(): void
    {
        SessionService::set('keyToRemove', 'value');
        SessionService::remove('keyToRemove');

        $this->assertFalse(SessionService::has('keyToRemove'));
    }

    /**
     * Test setting and getting flash messages.
     */
    public function testCanSetAndGetFlashMessage(): void
    {
        SessionService::setFlash('message', 'Flash message');

        // First call should return the flash message
        $this->assertEquals('Flash message', SessionService::getFlash('message'));

        // Second call should return null as flash is consumed
        $this->assertNull(SessionService::getFlash('message'));
    }

    /**
     * Test checking if a flash message exists.
     */
    public function testCanCheckIfFlashExists(): void
    {
        SessionService::setFlash('flash_key', 'value');

        $this->assertTrue(SessionService::hasFlash('flash_key'));
        $this->assertFalse(SessionService::hasFlash('non_existing_flash'));
    }

    /**
     * Test that getting a non-existing flash returns the default value.
     */
    public function testReturnsDefaultForNonExistingFlash(): void
    {
        $result = SessionService::getFlash('non_existing', 'default');

        $this->assertEquals('default', $result);
    }

    /**
     * Test storing and retrieving an array in session.
     */
    public function testCanStoreArrayInSession(): void
    {
        $data = ['name' => 'John', 'age' => 30];
        SessionService::set('user_data', $data);

        $this->assertEquals($data, SessionService::get('user_data'));
    }

    /**
     * Test storing and retrieving an array as flash message.
     */
    public function testCanStoreArrayAsFlash(): void
    {
        $errors = ['error1', 'error2'];
        SessionService::setFlash('errors', $errors);

        $this->assertEquals($errors, SessionService::getFlash('errors'));
    }
}
