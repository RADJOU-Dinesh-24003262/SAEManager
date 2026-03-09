<?php

namespace Controllers\Mock;

use Core\Controllers\ControllerInterface;

class MockGetController implements ControllerInterface
{
    public function control(...$params): void
    {
        echo "MOCK_GET_CALLED";
    }

    public static function support(string $path, string $method): bool
    {
        return true;
    }
}
