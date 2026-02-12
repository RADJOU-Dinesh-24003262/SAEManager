<?php

namespace Core\Routing;

use Core\Utilis\SessionService;

/**
 * Simple router based on La Console framework pattern.
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
     * @var array<string, array<string, array{controller: string, method: string}>> Routes configuration
     */
    private array $routes;

    /**
     * @var array<string> Available paths from routes
     */
    private array $availablePaths;

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
        $this->routes = ROUTES;
        $this->availablePaths = array_keys($this->routes);
        $this->requestedPath = $path;
        $this->requestedMethod = strtoupper($method);
        $this->parseRoutes();
    }

    /**
     * Parse routes and dispatch to appropriate controller.
     *
     * @return void
     */
    private function parseRoutes(): void
    {
        $explodedRequestedPath = $this->explodePath($this->requestedPath);
        $params = [];

        foreach ($this->availablePaths as $candidatePath) {
            $foundMatch = true;
            $explodedCandidatePath = $this->explodePath($candidatePath);

            // Check if path segments count matches.
            if (count($explodedCandidatePath) === count($explodedRequestedPath)) {
                foreach ($explodedRequestedPath as $key => $requestedPathPart) {
                    $candidatePathPart = $explodedCandidatePath[$key];

                    if ($this->isParam($candidatePathPart)) {
                        // Extract parameter value.
                        $params[substr($candidatePathPart, 1, -1)] = $requestedPathPart;
                    } elseif ($candidatePathPart !== $requestedPathPart) {
                        $foundMatch = false;
                        break;
                    }
                }

                if ($foundMatch) {
                    $route = $this->routes[$candidatePath];
                    break;
                }
            }
        }

        if (isset($route)) {
            $this->dispatch($route, $params);
        } else {
            $this->handleNotFound();
        }
    }

    /**
     * Dispatch to controller.
     *
     * @param array<string, array{controller: string, method: string}> $route  Route configuration.
     * @param array<string, string>                                    $params Route parameters.
     *
     * @return void
     */
    private function dispatch(array $route, array $params): void
    {
        // Check if HTTP method is supported for this route.
        if (!isset($route[$this->requestedMethod])) {
            $this->handleNotFound();
            return;
        }

        $routeConfig = $route[$this->requestedMethod];
        $controllerClass = $routeConfig['controller'];
        $method = $routeConfig['method'];

        error_log($controllerClass);
        error_log($method);
        try {
            $controller = new $controllerClass();

            // TODO: Add support for parameters
            // $controller->$method(...$params);.
            $controller->$method();
            exit();
        } catch (\Throwable $e) {
            SessionService::setFlash('errors', ["Une erreur inattendue est survenue."]);
            error_log("Erreur inattendue: " . $e->getTraceAsString() . $e->getMessage());
            http_response_code(500);
            header("Location: /");
            exit();
        }
    }

    /**
     * Explode path into segments.
     *
     * @param string $path The path to explode.
     *
     * @return array<string>
     */
    private function explodePath(string $path): array
    {
        return explode('/', rtrim(ltrim($path, '/'), '/'));
    }

    /**
     * Check if a path part is a parameter (between {}).
     *
     * @param string $candidatePathPart The path part to check.
     *
     * @return boolean
     */
    private function isParam(string $candidatePathPart): bool
    {
        return str_contains($candidatePathPart, '{') && str_contains($candidatePathPart, '}');
    }

    /**
     * Handle 404 - Route not found.
     *
     * @return void
     */
    private function handleNotFound(): void
    {
        http_response_code(404);
        SessionService::setFlash('errors', "Page non existante.");
        header("Location: /");
        exit();
    }
}
