<?php

namespace khokonc\mvc\Routing;

use khokonc\mvc\Application;
use khokonc\mvc\Auth;
use khokonc\mvc\Exceptions\InvalidRouteParametterException;
use khokonc\mvc\Exceptions\RouteNameNotFound;
use khokonc\mvc\Request;
use App\Exceptions\CsrfTokenNotVerified;
use App\Exceptions\NotFoundException;
use khokonc\mvc\Exceptions\MethodNotFoundException;

class Router
{
    public Request $request;
    public Auth $auth;

    private RouteResolver $resolver;

    public $routes = [];
    public $tempRoute = '';
    public $routeNames = [];


    private $prefix = '';
    private $middleware = null;

    private const CALLBACK = 'callback';
    private const MIDDLEWARE = 'middleware';

    private array $patterns = [
        '~/~',
        '~{[^\/{}]+}~'
    ];

    private array $replacements = [
        '/',
        '([0-9a-zA-Z-_]++)',
    ];

    public function __construct(Request $request, Auth $auth)
    {
        $this->request = $request;
        $this->auth = $auth;
        $this->resolver = new RouteResolver();
    }

    private function parseUrl($path)
    {
        if (str_contains($path, '(')) {
            throw new InvalidRouteParametterException('Invalide route Parametter ', 500);
        }
        $newPath = preg_replace($this->patterns, $this->replacements, $path);
        $newPath = trim($newPath, '\/?');
        $newPath = trim($newPath, '\/');
        return $newPath;
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
        $this->routes['get'][$this->parseUrl($path)] = [
            self::MIDDLEWARE => $this->middleware,
            self::CALLBACK => $callback
        ];
        return $this;
    }

    public function post($path, $callback)
    {
        $path = $this->prefix . $this->trimPath($path);
        $this->setTempRoute($path);
        $this->routes['post'][$this->parseUrl($path)] = [
            self::MIDDLEWARE => $this->middleware,
            self::CALLBACK => $callback
        ];
        return $this;
    }

    /**
     * Register Resource route
     * @param string $path , $class
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
        $this->post("$path/{id}", [$class, 'update'])->name("$path.update");
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
        return $this->resolver->resolve($this);
    }

    public function getRouteByName($routeName, $params = null)
    {
        $path = $this->routeNames[$routeName] ?? false;
        if ($path === false) {
            throw new RouteNameNotFound($routeName);
        }
        if (is_null($params)) {
            return $path;
        }
        if (is_array($params)) {
            foreach ($params as $key => $value) {
                $path = str_replace("{{$key}}", $value, $path);
            }
            return $path;
        }
        return str_replace('{id}', $params, $path);
    }

}
