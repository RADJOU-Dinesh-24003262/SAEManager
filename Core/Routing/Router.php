<?php

namespace Core\Routing;

use Core\Controllers\ControllerInterface;
use Core\Utils\SessionService;
use InvalidArgumentException;
use Models\Repository\Security\JsonIpBanRepository;

/**
 * Ultra-simple dynamic router.
 * Translates the URL purely mathematically into a class name and instantiates it.
 *
 * @category   Routing
 * @package    Core
 * @subpackage Routing
 * @author     Dinesh RADJOU <dinesh.radjou@etu.univ-amu.fr>
 * @license    MIT License https://opensource.org/licenses/MIT
 * @link       https://github.com/RADJOU-Dinesh-24003262/SAEManager
 * @see        https://laconsole.dev/formations/framework-php/routage
 */
class Router
{
    /**
     * @var string Requested path from URL
     */
    private string $requestedPath;

    /**
     * @var string Requested HTTP method
     */
    private string $requestedMethod;

    /**
     * Router constructor.
     *
     * @param string $path   The request path.
     * @param string $method The HTTP method.
     */
    public function __construct(string $path, string $method)
    {
        $this->verifyIpNotBanned();
        $this->requestedPath = rtrim(explode('?', $path)[0], '/');
        $this->requestedMethod = strtoupper($method);
        $this->parseAndDispatch();
    }

    /**
     * Checks if the client IP is banned.
     *
     * @return void
     */
    private function verifyIpNotBanned(): void
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
        $ipBanRepository = new JsonIpBanRepository();
        if ($ipBanRepository->isBanned($ip)) {
            http_response_code(403);
            echo "Access Denied. Your IP address is temporarily banned.";
            exit();
        }
    }

    /**
     * Parse path dynamically by deducing the exact controller FQCN.
     *
     * @return void
     */
    private function parseAndDispatch(): void
    {
        if ($this->requestedPath === '' || $this->requestedPath === '/index' || $this->requestedPath === '/') {
            $this->dispatch('\Controllers\Index\IndexController', []);
            return;
        }

        $segments = array_values(array_filter(explode('/', trim($this->requestedPath, '/'))));

        $words = [];
        $params = [];

        foreach ($segments as $segment) {
            if (is_numeric($segment)) {
                $params[] = (int)$segment;
            } else {
                // E.g. "edit-profile" -> "EditProfile".
                $word = str_replace('-', ' ', $segment);
                $word = str_replace(' ', '', ucwords($word));
                $words[] = $word;
            }
        }

        if (empty($words)) {
            $this->handleNotFound();
            return;
        }

        // Deduce controller name.
        $module = ucfirst($words[0]);
        $actionBaseName = implode('', $words);

        if ($this->requestedMethod === 'POST' && !str_ends_with($actionBaseName, 'Post')) {
            $actionBaseName .= 'Post';
        }

        $controllerName = $actionBaseName . 'Controller';
        $fqcn = '\\Controllers\\' . $module . '\\' . $controllerName;

        if (class_exists($fqcn)) {
            $this->dispatch($fqcn, $params);
        } else {
            error_log("Router Error: Strict mathematical deduction of $fqcn failed for path {$this->requestedPath}");
            $this->handleNotFound();
        }
    }

    /**
     * Dispatch to the matched controller.
     *
     * @param string     $controllerClass FQCN of the deduced controller.
     * @param array<int> $params          Extracted numeric parameters.
     *
     * @return void
     * @throws InvalidArgumentException If the controller does not implement the interface.
     */
    private function dispatch(string $controllerClass, array $params): void
    {
        try {
            if (!is_subclass_of($controllerClass, ControllerInterface::class)) {
                throw new InvalidArgumentException("Controller class must implement ControllerInterface");
            }

            /* @var ControllerInterface $controller */
            $controller = new $controllerClass();
            $controller->control(...$params);
            return;
        } catch (\Throwable $e) {
            SessionService::setFlash('errors', ["Une erreur inattendue est survenue."]);
            error_log("Erreur inattendue: " . $e->getTraceAsString() . $e->getMessage());
            http_response_code(500);
            header("Location: /");
            return;
        }
    }

    /**
     * Handle 404 - Route not found.
     *
     * @return void
     */
    private function handleNotFound(): void
    {
        http_response_code(404);
        SessionService::setFlash('errors', ["Page non existante."]);
        header("Location: /");
        return;
    }
}
