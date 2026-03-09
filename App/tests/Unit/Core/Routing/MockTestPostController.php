<?php

namespace Controllers\Mock;

use Core\Controllers\ControllerInterface;

class MockTestPostController implements ControllerInterface
{
    public function control(...$params): void
    {
        echo "MOCK_POST_CALLED_WITH_PARAMS_" . implode('_', $params);
    }

    public static function support(string $path, string $method): bool
    {
        return true;
    }
}
