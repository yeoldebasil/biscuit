<?php

declare(strict_types=1);

namespace Yeoldebasil\Biscuit;

use Exception;
use RuntimeException;
use ValueError;

class Router
{
    function __construct(
        public Request $request, 
        public array $routes, 
        public string $namespace = ''
    ) {}

    function run(): Response
    {
        $url    = $this->request->url;
        $method = strtoupper($this->request->method);

        $plain_routes = $this->routes['plain'] ?? [];
        $regex_routes = $this->routes['regex'] ?? [];

        if (blank($plain_routes, $regex_routes))
            throw new RuntimeException("Вы не указали никаких путей для маршрутизации", 1);

        $route = $plain_routes["{$method} {$url}"] ?? false;

        /**
         * Простые
         */
        if ($route) {
            $route = $this->parseRoute("{$method} {$url}", $route);

            $class = $this->namespace . '\\' . $route['class'];
            $controller = new $class($this->request);
            return call_user_func([$controller, $route['function']]);
        }

        /**
         * С регулярными выражениями
         */
        foreach ($regex_routes as $route => $controller) {
            $route = $this->parseRoute($route, $controller);

            if ($this->patternMatches($route['pattern'], $url, $matches)) {
                $matches = array_slice($matches, 1);
                $pocket  = [];

                foreach ($matches as $array) {
                    $pocket[] = $array[0];
                }

                $class = $this->namespace . '\\' . $route['class'];

                $pocket     = specify_types($pocket);
                $controller = new $class($this->request);

                return call_user_func_array([$controller, $route['function']], $pocket);
            }
        }

        return new Controller($this->request)->notFound();
    }

    private function parseRoute(string $route, string $controller): array
    {
        try {
            [$method, $pattern]  = explode(' ', $route, 2);
            [$class, $function] = explode('.', $controller, 2);
        } catch (Exception $e) {
            throw new ValueError("Invalid routing map");
        }

        return [
            'method'   => $method,
            'pattern'  => $pattern,
            'class'    => $class,
            'function' => $function
        ];
    }

    private function patternMatches(string $pattern, string $uri, &$matches): bool
    {
        // Заменить все слова в фигурных скобках на паттерны (как в Laravel)
        $pattern = preg_replace('/\/{(.*?)}/', '/(.*?)', $pattern);

        // we may have a match!
        return boolval(preg_match('#^' . $pattern . '$#', $uri, $matches, PREG_OFFSET_CAPTURE));
    }
}
