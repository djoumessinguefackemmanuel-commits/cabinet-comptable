<?php
namespace App\Core;
class Router
{
    private array $routes = [];
    public function get(string $uri, $controller): void
    {
        $this->addRoute('GET', $uri, $controller);
    }
    public function post(string $uri, $controller): void
    {
        $this->addRoute('POST', $uri, $controller);
    }
    public function put(string $uri, $controller): void
    {
        $this->addRoute('PUT', $uri, $controller);
    }
    public function delete(string $uri, $controller): void
    {
        $this->addRoute('DELETE', $uri, $controller);
    }
    private function addRoute(string $method, string $uri, $controller): void
    {
        $uri = '/' . trim($uri, '/');
        if ($uri === '//') {
            $uri = '/';
        }
        $this->routes[] = [
            'method' => strtoupper($method),
            'uri' => $uri,
            'controller' => $controller
        ];
    }
    public function dispatch(): void
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        $basePath = parse_url(BASE_URL, PHP_URL_PATH) ?: '';
        if (
            $basePath !== ''
            && $basePath !== '/'
            && str_starts_with($uri, $basePath)
        ) {
            $uri = substr($uri, strlen($basePath));
        }
        $uri = '/' . trim($uri, '/');
        if ($uri === '//') {
            $uri = '/';
        }
        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }
            $pattern = preg_replace_callback(
                '/\\\\\{([a-zA-Z_][a-zA-Z0-9_]*)\\\\\}/',
                function ($matches) {
                    return '(?P<' . $matches[1] . '>[^/]+)';
                },
                preg_quote($route['uri'], '#')
            );
            if (preg_match('#^' . $pattern . '$#', $uri, $matches)) {
                if (!(new \App\Middleware\RouteAccessMiddleware())->handle($method, $uri)) {
                    return;
                }

                $params = [];
                foreach ($matches as $key => $value) {
                    if (is_string($key)) {
                        $params[] = $value;
                    }
                }
                if ($method !== 'GET' && $method !== 'HEAD' && $method !== 'OPTIONS') {
                    (new \App\Middleware\CsrfMiddleware())->handle();
                }
                $this->executeController(
                    $route['controller'],
                    $params
                );
                return;
            }
        }
        $this->notFound();
    }
    private function executeController($controller, array $params = []): void
    {
        // Cas 1 : fonction directement appelable.
        if (is_callable($controller)) {
            call_user_func_array($controller, $params);
            return;
        }
        // Cas 2 : tableau [Classe, méthode].
        if (
            is_array($controller)
            && count($controller) === 2
            && is_string($controller[0])
            && is_string($controller[1])
        ) {
            [$controllerClass, $methodName] = $controller;
            if (!class_exists($controllerClass)) {
                throw new \RuntimeException(
                    "Le contrôleur {$controllerClass} est introuvable."
                );
            }
            $instance = new $controllerClass();
            if (!method_exists($instance, $methodName)) {
                throw new \RuntimeException(
                    "La méthode {$methodName} est introuvable."
                );
            }
            if (!is_callable([$instance, $methodName])) {
                throw new \RuntimeException(
                    "La méthode {$methodName} n'est pas accessible."
                );
            }
            call_user_func_array(
                [$instance, $methodName],
                $params
            );
            return;
        }
        // Cas 3 : Classe@méthode.
        if (is_string($controller) && strpos($controller, '@') !== false) {
            [$controllerName, $methodName] = explode('@', $controller, 2);
            $controllerClass = 'App\\Controllers\\' . $controllerName;
            if (!class_exists($controllerClass)) {
                throw new \RuntimeException(
                    "Le contrôleur {$controllerClass} est introuvable."
                );
            }
            $instance = new $controllerClass();
            if (!method_exists($instance, $methodName)) {
                throw new \RuntimeException(
                    "La méthode {$methodName} est introuvable."
                );
            }
            if (!is_callable([$instance, $methodName])) {
                throw new \RuntimeException(
                    "La méthode {$methodName} n'est pas accessible."
                );
            }
            call_user_func_array(
                [$instance, $methodName],
                $params
            );
            return;
        }
        throw new \InvalidArgumentException(
            'Le contrôleur de cette route est invalide.'
        );
    }
    private function notFound(): void
    {
        http_response_code(404);
        $errorView = VIEW_PATH . '/errors/404.php';
        if (file_exists($errorView)) {
            require $errorView;
        } else {
            echo 'Erreur 404 : Page introuvable.';
        }
    }
}