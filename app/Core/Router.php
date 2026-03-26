<?php

declare(strict_types=1);

namespace App\Core;

class Router
{
    private array $routes = [];

    public function get(string $path, array $handler): void
    {
        $this->routes['GET'][$path] = $handler;
    }

    public function post(string $path, array $handler): void
    {
        $this->routes['POST'][$path] = $handler;
    }

    public function dispatch(Request $request): void
    {
        $method = $request->method();
        $path = $request->path();

        if (isset($this->routes[$method][$path])) {
            [$class, $action] = $this->routes[$method][$path];
            (new $class())->$action($request);
            return;
        }

        foreach ($this->routes[$method] ?? [] as $route => $handler) {
            $pattern = preg_replace('/\{(\w+)\}/', '(?P<$1>[^/]+)', $route);
            $pattern = '#^' . $pattern . '$#';
            if (preg_match($pattern, $path, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                $request->setParams($params);
                [$class, $action] = $handler;
                (new $class())->$action($request);
                return;
            }
        }

        http_response_code(404);
        echo '404 - Not Found';
    }
}
