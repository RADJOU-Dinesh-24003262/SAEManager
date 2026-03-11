<?php

namespace Tests\Unit\Core\Routing;

use Models\Repository\Security\JsonIpBanRepository;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\Attributes\UsesClass;
use Core\Routing\Router;
use Core\Utils\SessionService;

#[CoversClass(Router::class)]
#[CoversClass(SessionService::class)]
#[UsesClass(JsonIpBanRepository::class)]
class RouterUnitTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
    }

    #[RunInSeparateProcess]
    public function testValidGetRouteDispatching()
    {
        require_once __DIR__ . '/MockGetController.php';

        $this->expectOutputString("MOCK_GET_CALLED");
        new Router('/mock/get', 'GET');
    }

    #[RunInSeparateProcess]
    public function testValidPostRouteDispatching()
    {
        require_once __DIR__ . '/MockTestPostController.php';

        $this->expectOutputString("MOCK_POST_CALLED_WITH_PARAMS_1");
        new Router('/mock/test/1', 'POST');
    }

    #[RunInSeparateProcess]
    public function testRouteNotFoundReturns404()
    {
        ini_set('error_log', '/dev/null');
        $this->expectOutputString("");

        new Router('/non-existent-route-for-sure', 'GET');

        $this->assertTrue(true);
    }
}
