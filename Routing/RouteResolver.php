<?php

namespace khokonc\mvc\Routing;


use App\Exceptions\NotFoundException;
use khokonc\mvc\Application;
use khokonc\mvc\Exceptions\CsrfTokenNotVerified;
use khokonc\mvc\Exceptions\MethodNotFoundException;
use khokonc\mvc\Exceptions\MiddlewareNotFoundException;
use khokonc\mvc\Routing\Routes\Route;

class RouteResolver
{
    private const CALLBACK = 'callback';
    private const MIDDLEWARE = 'middleware';

    public function resolve(Router $router)
    {
        $requestPath = Application::$app->request->getPath();
        $routes      = $router->routes[Application::$app->request->getMethod()] ?? false;

        if (Application::$app->request->verifyCsrfTocken() === false && Application::$app->request->isPost()) {
            throw new CsrfTokenNotVerified();
        }

        if ($routes == false) {
            throw new NotFoundException();
        }
        foreach ($routes as $route) {
            $isMatch = preg_match('~^' . $route->path . '$~', $requestPath, $matches);
            if (!$isMatch) continue;
            if(is_string($route->callback)) return $route->callback;
            $this->refactorCallback($route);
            $this->callMiddleware($route);
            $matches = array_slice($matches, 1);
            array_unshift($matches, Application::$app->request);
            return call_user_func($route->callback, ...$matches);
        }
        throw new NotFoundException();
    }


    private function refactorCallback(Route $route)
    {
        if (is_array($route->callback)) {
            $route->classname = $route->callback[0];
            $route->action = $route->callback[1];
            $route->controller = new $route->classname();
            $route->controller->set(Application::$app);
            $route->callback = [$route->controller, $route->action];
            $this->setMiddleware($route);
            if (!method_exists($route->controller, $route->action)) {
                throw new MethodNotFoundException($route->action,$route->classname);
            }
        }

    }


    private function setMiddleware(Route $route)
    {
        if($route->middleware == null)
        {
            $route->middleware = $route->controller->getMiddleware();
        }
    }



    private function callMiddleware($route)
    {
        if ($route->middleware !== null) {
            $middleware = Application::$app->middleware[$route->middleware] ?? false;
            if($middleware == false)
            {
                throw new MiddlewareNotFoundException($route->middleware);
            }
            $middleware = new $middleware();
            $middleware->handle(Application::$app);
        }
    }

}