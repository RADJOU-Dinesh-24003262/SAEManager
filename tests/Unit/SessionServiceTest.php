<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Utilis\SessionService;

/**
 * Test class for SessionService
 * 
 * @covers \Utilis\SessionService
 */
class SessionServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Clear session before each test
        $_SESSION = [];
    }

    protected function tearDown(): void
    {
        // Clean up session after each test
        $_SESSION = [];
        parent::tearDown();
    }

    /**
     * @test
     * @covers \Utilis\SessionService::set
     * @covers \Utilis\SessionService::get
     */
    public function it_can_set_and_get_session_value(): void
    {
        SessionService::set('test_key', 'test_value');
        
        $this->assertEquals('test_value', SessionService::get('test_key'));
    }

    /**
     * @test
     * @covers \Utilis\SessionService::has
     */
    public function it_can_check_if_session_key_exists(): void
    {
        SessionService::set('existing_key', 'value');
        
        $this->assertTrue(SessionService::has('existing_key'));
        $this->assertFalse(SessionService::has('non_existing_key'));
    }

    /**
     * @test
     * @covers \Utilis\SessionService::get
     */
    public function it_returns_default_value_when_key_not_exists(): void
    {
        $result = SessionService::get('non_existing', 'default_value');
        
        $this->assertEquals('default_value', $result);
    }

    /**
     * @test
     * @covers \Utilis\SessionService::remove
     */
    public function it_can_remove_session_value(): void
    {
        SessionService::set('key_to_remove', 'value');
        SessionService::remove('key_to_remove');
        
        $this->assertFalse(SessionService::has('key_to_remove'));
    }

    /**
     * @test
     * @covers \Utilis\SessionService::setFlash
     * @covers \Utilis\SessionService::getFlash
     */
    public function it_can_set_and_get_flash_message(): void
    {
        SessionService::setFlash('message', 'Flash message');
        
        // First call should return the value
        $this->assertEquals('Flash message', SessionService::getFlash('message'));
        
        // Second call should return default (flash is consumed)
        $this->assertNull(SessionService::getFlash('message'));
    }

    /**
     * @test
     * @covers \Utilis\SessionService::hasFlash
     */
    public function it_can_check_if_flash_exists(): void
    {
        SessionService::setFlash('flash_key', 'value');
        
        $this->assertTrue(SessionService::hasFlash('flash_key'));
        $this->assertFalse(SessionService::hasFlash('non_existing_flash'));
    }

    /**
     * @test
     * @covers \Utilis\SessionService::getFlash
     */
    public function it_returns_default_for_non_existing_flash(): void
    {
        $result = SessionService::getFlash('non_existing', 'default');
        
        $this->assertEquals('default', $result);
    }

    /**
     * @test
     * @covers \Utilis\SessionService::set
     */
    public function it_can_store_array_in_session(): void
    {
        $data = ['name' => 'John', 'age' => 30];
        SessionService::set('user_data', $data);
        
        $this->assertEquals($data, SessionService::get('user_data'));
    }

    /**
     * @test
     * @covers \Utilis\SessionService::setFlash
     */
    public function it_can_store_array_as_flash(): void
    {
        $errors = ['error1', 'error2'];
        SessionService::setFlash('errors', $errors);
        
        $this->assertEquals($errors, SessionService::getFlash('errors'));
    }
}
