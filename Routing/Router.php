<?php

namespace khokonc\mvc\Routing;

use khokonc\mvc\Exceptions\RouteNameNotFound;
use khokonc\mvc\Routing\Routes\Route;
use khokonc\mvc\Routing\Routes\RouteFactory;


class Router
{
    /**
     * @var array key=>get|post |Route instance
     */
    public array $routes = [];
    /**
     * @var RouteResolver object for resolving route
     */
    private RouteResolver $resolver;
    /**
     * @var RouteFactory Single route Refactoring
     */
    private RouteFactory $routeFactory;
    /**
     * @var Route temporary instance of Route
     */
    private Route $route;
    /**
     * @var array Registered route by name
     */
    private array $routeNames = [];
    /**
     * @var string Route prefix
     */
    private ?string $prefix = null;

    /**
     * @var string Route middleware key
     */
    private ?string $middleware = null;


    public function __construct()
    {
        $this->routeFactory = new RouteFactory();
        $this->resolver = new RouteResolver();
    }



    public function get($path, $callback)
    {
        $route = $this->set($path, 'get', $callback);
        return $route;
    }

    public function post($path, $callback)
    {
        $route = $this->set($path, 'post', $callback);
        return $route;
    }

    /**
     * Register Resource route
     * @param string $path , $class
     *
     * @return Router
     */

    public function resource($path, $class)
    {
        $path = trim($path, '/');
        // registered Index method
        $this->get($path, [$class, 'index'])->name("$path.index");
        // registered create method
        $this->get("$path/create", [$class, 'create'])->name("$path.create");
        // Store method
        $this->post($path, [$class, 'store'])->name("$path.store");
        // show method
        $this->get("$path/{id}", [$class, 'show'])->name("$path.show");
        // edit method
        $this->get("$path/{id}/edit", [$class, 'edit'])->name("$path.edit");
        // update method
        $this->post("$path/{id}", [$class, 'update'])->name("$path.update");
        // destroy method
        $this->post("$path/{id}/delete", [$class, 'destroy'])->name("$path.destroy");
        return $this->route;
    }

    public function group($attribute = null, $callback = null)
    {
        if (is_array($attribute)) {
            $this->prefix = $attribute['prefix'] ?? null;
            $this->middleware = $attribute['middleware'] ?? null;
        }
        if (is_callable($attribute)) {
            call_user_func($attribute);
        }
        if (is_callable($callback)) {
            call_user_func($callback);
        }
        $this->prefix = null;
        $this->middleware = null;
    }


    public function resolve()
    {
        return $this->resolver->resolve($this);
    }

    public function getRouteByName($name, $params = null)
    {
        $routes = array_merge($this->routes['get'],$this->routes['post']);
        foreach ($routes as $route) {
            if ($route->name !== $name) continue;
            $path = config('app.app_url') . "/" . $route->noRegexPath;
            if (is_null($params)) return $path;
            if (is_array($params)) {
                foreach ($params as $key => $value) {
                    $path = str_replace("{{$key}}", $value, $path);
                }
                return $path;
            }
            return str_replace('{id}', $params, $path);
        }
        throw new RouteNameNotFound($name);
    }

    private function set($path, $method, $callback)
    {
        $route = $this->routeFactory->create($path, $method, $callback);
        $this->routes[$route->method][] = $route;
        $this->route = $route;
        $route->prefix($this->prefix);
        $route->middleware($this->middleware);
        return $route;
    }

}
