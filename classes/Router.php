<?php
/**
 * Simple Router class
 */
class Router {
    private $routes = [];

    public function get($path, $handler) {
        $this->addRoute('GET', $path, $handler);
    }

    public function post($path, $handler) {
        $this->addRoute('POST', $path, $handler);
    }

    private function addRoute($method, $path, $handler) {
        // Convert route path to regex
        // Special handling for {path} parameter to allow slashes (for nested files)
        if (strpos($path, '{path}') !== false) {
            $pattern = str_replace('{path}', '(?P<path>.+)', $path);
            $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $pattern);
        } else {
            $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $path);
        }
        $pattern = '#^' . $pattern . '$#';

        $this->routes[] = [
            'method' => $method,
            'pattern' => $pattern,
            'handler' => $handler,
            'path' => $path
        ];
    }

    public function dispatch() {
        $method = $_SERVER['REQUEST_METHOD'];
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        // Remove script name from path if present
        $scriptName = dirname($_SERVER['SCRIPT_NAME']);
        if ($scriptName !== '/') {
            $path = substr($path, strlen($scriptName));
        }
        $path = $path ?: '/';

        foreach ($this->routes as $route) {
            if ($route['method'] === $method && preg_match($route['pattern'], $path, $matches)) {
                // Extract parameters
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                // Call handler
                list($controller, $action) = explode('@', $route['handler']);
                $controllerInstance = new $controller();

                echo $controllerInstance->$action($params);
                return;
            }
        }

        // 404 Not found
        http_response_code(404);
        echo View::render('404', ['path' => $path]);
    }
}
