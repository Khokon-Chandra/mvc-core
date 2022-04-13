<?php

namespace khokonc\mvc\Routing;


use App\Exceptions\NotFoundException;
use khokonc\mvc\Application;
use khokonc\mvc\Exceptions\CsrfTokenNotVerified;
use khokonc\mvc\Exceptions\MethodNotFoundException;

class RouteResolver
{
    private const CALLBACK = 'callback';
    private const MIDDLEWARE = 'middleware';

    public function resolve(Router $router)
    {
        if ($router->request->verifyCsrfTocken() === false && $router->request->isPost()) {
            throw new CsrfTokenNotVerified();
        }
        $requestPath = trim($router->request->getPath(), '/');
        $routes = $router->routes[$router->request->getMethod()] ?? false;
        if ($routes == false) {
            throw new NotFoundException();
        }
        foreach ($routes as $route => $callback) {
            $middleware = $callback[self::MIDDLEWARE];
            $callback   = $callback[self::CALLBACK];

            $isMatch = preg_match('~^' . $route . '$~', $requestPath, $matches);
            if (!$isMatch) continue;
            if (is_array($callback)) {
                $classname = $callback[0];
                $callback[0] = new $callback[0](Application::$app);
                $controller = $callback[0];
                $controller->setRequest($router->request);
                $controller->setAuth($router->auth);
                if ($middleware !== null) {
                    $controller->middleware($middleware);
                }
                $middleware = $controller->getMiddleware();
                if (is_object($middleware)) {
                    $middleware->handle(Application::$app);
                }
                if (!method_exists($callback[0], $callback[1])) {
                    throw new MethodNotFoundException("'$callback[1]' Method does not Exists inside $classname", 404);
                }
            }
            $matches = array_slice($matches, 1);
            array_unshift($matches, $router->request);
            return call_user_func($callback, ...$matches);
        }
        throw new NotFoundException();
    }

}