<?php

class Router {
    private $routes = [];

    public function add(string $method, string $pattern, $handler) {
        $this->routes[] = [
            'method' => strtoupper($method),
            'pattern' => $pattern,
            'handler' => $handler
        ];
    }

    public function dispatch() {
        $requestMethod = $_SERVER['REQUEST_METHOD'];
        $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        foreach ($this->routes as $route) {
            if ($route['method'] !== $requestMethod) {
                continue;
            }   

            // Convert {param} placeholders into regex groups
            $pattern = preg_replace('#\{([a-zA-Z0-9_]+)\}#', '([^/]+)', $route['pattern']);
            $pattern = "#^" . $pattern . "$#";

            if (preg_match($pattern, $requestUri, $matches)) {
                array_shift($matches);
                $handler = $route['handler'];

                if (is_array($handler)) {
                    $class = $handler[0];
                    $method = $handler[1];
                    if (method_exists($class, $method)) {
                        call_user_func_array([new $class, $method], $matches);
                        return;
                    }
                } elseif (is_callable($handler)) {
                    call_user_func_array($handler, $matches);
                    return;
                }

                http_response_code(500);
                echo json_encode(['message' => 'Handler not found']);
                return;
            }
        }   

        http_response_code(404);
        echo json_encode(['message' => 'Not found']);
    }
}

?>
