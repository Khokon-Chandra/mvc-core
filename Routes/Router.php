<?php

namespace khokonc\mvc\Routes;

use app\Exceptions\CsrfTokenNotVerified;
use app\Exceptions\NotFoundException;
use khokonc\mvc\Auth;
use khokonc\mvc\Request;

class Router
{
    private Request $request;
    private Auth $auth;

    public $routes = [];
    public $tempRoute   = '';
    public $routeNames = [];


    private $prefix     = '';
    private $middleware = null;

    private const CALLBACK = 'callback';
    private const MIDDLEWARE = 'middleware';

    public function __construct($request, $session, $auth)
    {
        $this->request = $request;
        $this->session = $session;
        $this->auth    = $auth;
    }

    private function makeRoutePattern($path)
    {
        $pattern = "~{(\w+)}~";
        $replacement = "(.*)";
        $path    = preg_replace($pattern, $replacement, $path);
        return "~^" . $path . "?$~";
    }


    private function trimPath($path)
    {
        return '/' . trim($path, '/');
    }

    private function setTempRoute($path)
    {
        $this->tempRoute = APP_URL . $path;
    }


    public function get($path, $callback)
    {
        $path = $this->prefix . $this->trimPath($path);
        $this->setTempRoute($path);
        $this->routes['get'][$this->makeRoutePattern($path)] = [
            self::MIDDLEWARE => $this->middleware,
            self::CALLBACK => $callback
        ];
        return $this;
    }

    public function post($path, $callback)
    {
        $path = $this->prefix . $this->trimPath($path);
        $this->setTempRoute($path);
        $this->routes['post'][$this->makeRoutePattern($path)] = [
            self::MIDDLEWARE => $this->middleware,
            self::CALLBACK => $callback
        ];
        return $this;
    }

    /**
     * Register Resource route
     * @param string $path, $class
     * @return Router
     */

    public function resource($path, $class)
    {
        // registered Index method
        $this->get($path, [$class, 'index'])->name("$path.index");
        // registered create method
        $this->get("$path/create", [$class, 'create'])->name("$path.create");
        // Store metod
        $this->post($path, [$class, 'store'])->name("$path.store");
        // show method
        $this->get("$path/{id}", [$class, 'show'])->name("$path.show");
        // edit method
        $this->get("$path/{id}/edit", [$class, 'edit'])->name("$path.edit");
        // update method
        $this->post("$path/{id}/edit", [$class, 'update'])->name("$path.update");
        // destroy method
        $this->post("$path/{id}/delete", [$class, 'destroy'])->name("$path.destroy");

        return $this;
    }


    public function group($attribute, $callback)
    {
        $this->prefix = isset($attribute['prefix']) ? '/' . trim($attribute['prefix'], '/') : '';
        $this->middleware = $attribute['middleware'] ?? null;
        if (is_callable($callback)) {
            call_user_func($callback);
        }
        return $this;
    }


    /**
     * Register Route name here
     */

    public function name($name)
    {
        $this->routeNames[$name] = $this->tempRoute;
    }


    public function resolve()
    {
        if ($this->request->verifyCsrfTocken() === false && $this->request->isPost()) {
            throw new CsrfTokenNotVerified();
        }
        $requestPath = $this->request->getPath();
        $routes      = $this->routes[$this->request->getMethod()];
        foreach ($routes as $pattern => $callback) {

            $middleware = $callback[self::MIDDLEWARE];
            $callback = $callback[self::CALLBACK];

            $isMatch = preg_match($pattern, $requestPath);
            if (!$isMatch) continue;

            preg_match($pattern, $requestPath, $matches);

            if (is_array($callback)) {
                $callback[0] = new $callback[0](Route::$app);
                $controller  =  $callback[0];
                $controller->setRequest($this->request);
                $controller->setAuth($this->auth);
                if ($middleware !== null) {
                    $controller->middleware($middleware);
                }
                $middleware  = $controller->getMiddleware();
                if (is_object($middleware)) {
                    $middleware->handle(Route::$app);
                }
            }
            $matches = array_slice($matches, 1);
            array_unshift($matches, $this->request);
            return call_user_func($callback, ...$matches);
        }
        throw new NotFoundException();
    }
}
