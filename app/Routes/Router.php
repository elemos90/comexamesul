<?php

namespace App\Routes;

use App\Controllers\Controller;
use App\Http\Request;
use Closure;
use InvalidArgumentException;
use RuntimeException;

class Router
{
    private Request $request;

    /** @var array<string,array<int,array<string,mixed>>> */
    private array $routes = [];

    /** @var array<int,array<string,mixed>> */
    private array $groupStack = [];

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function get(string $uri, $action, array $middlewares = []): void
    {
        $this->addRoute(['GET'], $uri, $action, $middlewares);
    }

    public function post(string $uri, $action, array $middlewares = []): void
    {
        $this->addRoute(['POST'], $uri, $action, $middlewares);
    }

    public function any(string $uri, $action, array $middlewares = []): void
    {
        $this->addRoute(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], $uri, $action, $middlewares);
    }

    public function group(array $options, callable $callback): void
    {
        $this->groupStack[] = $options;
        $callback($this);
        array_pop($this->groupStack);
    }

    /**
     * @param array<int,string> $methods
     * @param array<int|string,mixed> $middlewares
     */
    private function addRoute(array $methods, string $uri, $action, array $middlewares): void
    {
        $uri = '/' . ltrim($uri, '/');

        $route = [
            'uri' => $uri,
            'action' => $action,
            'middlewares' => $middlewares,
        ];

        if (!empty($this->groupStack)) {
            foreach ($this->groupStack as $group) {
                if (isset($group['prefix'])) {
                    $prefix = '/' . trim((string) $group['prefix'], '/');
                    $route['uri'] = $this->normalizePath($prefix . $route['uri']);
                }

                if (isset($group['middleware'])) {
                    $route['middlewares'] = array_merge((array) $group['middleware'], $route['middlewares']);
                }
            }
        }

        foreach ($methods as $method) {
            $method = strtoupper($method);
            $this->routes[$method][] = $route;
        }
    }

    public function dispatch(): void
    {
        $method = $this->request->method();
        $path = $this->normalizePath($this->request->path());

        // DEBUG: Log da requisição
        if (strpos($path, '/api/') === 0) {
            error_log("DEBUG Router: Requisição $method $path");
            error_log("DEBUG Router: Total de rotas $method: " . count($this->routes[$method] ?? []));
        }

        $routes = $this->routes[$method] ?? [];
        foreach ($routes as $route) {
            $pattern = $this->convertUriToRegex($route['uri']);
            
            // DEBUG: Log de matching
            if (strpos($path, '/api/') === 0) {
                error_log("DEBUG Router: Testando pattern $pattern contra $path");
            }
            
            if (preg_match($pattern, $path, $matches)) {
                $params = [];
                foreach ($matches as $key => $value) {
                    if (!is_int($key)) {
                        $params[$key] = $value;
                    }
                }

                $this->request->setParams($params);
                $handler = $this->resolveAction($route['action']);
                $pipeline = $this->buildMiddlewarePipeline($route['middlewares'], $handler);

                echo $pipeline($this->request);
                return;
            }
        }

        http_response_code(404);
        $controller = new class extends Controller {
            public function renderNotFound(array $data): string
            {
                return $this->view('errors/404', $data);
            }
        };
        echo $controller->renderNotFound(['isPublic' => !\App\Utils\Auth::check()]);
    }

    private function normalizePath(string $path): string
    {
        $path = '/' . ltrim($path, '/');
        return rtrim($path, '/') ?: '/';
    }

    private function convertUriToRegex(string $uri): string
    {
        $uri = $this->normalizePath($uri);
        $regex = preg_replace('/\{([a-zA-Z_][a-zA-Z0-9_-]*)\}/', '(?P<$1>[^/]+)', $uri);

        return '#^' . $regex . '$#';
    }

    private function resolveAction($action): callable
    {
        if ($action instanceof Closure) {
            return $action;
        }

        if (is_string($action) && str_contains($action, '@')) {
            [$controller, $method] = explode('@', $action);
            $controllerClass = 'App\\Controllers\\' . $controller;

            if (!class_exists($controllerClass)) {
                throw new RuntimeException("Controller {$controllerClass} not found");
            }

            $instance = new $controllerClass();

            if (!method_exists($instance, $method)) {
                throw new RuntimeException("Method {$method} not defined in controller {$controllerClass}");
            }

            return function (Request $request) use ($instance, $method) {
                return $instance->{$method}($request);
            };
        }

        if (is_callable($action)) {
            return $action;
        }

        throw new InvalidArgumentException('Invalid route action');
    }

    private function buildMiddlewarePipeline(array $middlewares, callable $handler): callable
    {
        $next = $handler;

        foreach (array_reverse($middlewares) as $middleware) {
            $next = function (Request $request) use ($middleware, $next) {
                if (is_string($middleware)) {
                    $parameters = [];
                    $middlewareName = $middleware;

                    if (str_contains($middleware, ':')) {
                        [$middlewareName, $paramString] = explode(':', $middleware, 2);
                        $parameters = array_map('trim', explode(',', $paramString));
                    }

                    $middlewareClass = class_exists($middlewareName)
                        ? $middlewareName
                        : 'App\\Middlewares\\' . $middlewareName;

                    if (!class_exists($middlewareClass)) {
                        throw new RuntimeException("Middleware {$middlewareClass} not found");
                    }

                    $instance = new $middlewareClass();

                    if ($parameters && method_exists($instance, 'setParameters')) {
                        $instance->setParameters($parameters);
                    }
                } elseif (is_callable($middleware)) {
                    $instance = $middleware;
                } else {
                    throw new RuntimeException('Invalid middleware definition');
                }

                if (is_callable($instance)) {
                    return $instance($request, $next);
                }

                if (!method_exists($instance, 'handle')) {
                    throw new RuntimeException('Middleware must have a handle method');
                }

                return $instance->handle($request, $next);
            };
        }

        return $next;
    }
}