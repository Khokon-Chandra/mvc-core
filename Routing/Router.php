<?php

namespace khokonc\mvc\Routing;

use khokonc\mvc\Application;
use khokonc\mvc\Auth;
use khokonc\mvc\Exceptions\InvalidRouteParametterException;
use khokonc\mvc\Request;
use App\Exceptions\CsrfTokenNotVerified;
use App\Exceptions\NotFoundException;
use khokonc\mvc\Exceptions\MethodNotFoundException;

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

    private array $patterns = [
        '~/~',
        '~{[^\/{}]+}~'
    ];

    private array $replacements = [
        '/',
        '([0-9a-zA-Z-_]++)',
    ];

    public function __construct($request, $session, $auth)
    {
        $this->request = $request;
        $this->session = $session;
        $this->auth    = $auth;
    }

    private function parseUrl($path)
    {
        if(str_contains($path,'(')){
            throw new InvalidRouteParametterException('Invalide route Parametter ',500);
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
        if ($this->request->verifyCsrfTocken() === false && $this->request->isPost()) {
            throw new CsrfTokenNotVerified();
        }
        $requestPath = trim($this->request->getPath(), '/');
        $routes      = $this->routes[$this->request->getMethod()];
        foreach ($routes as $route => $callback) {
            $middleware = $callback[self::MIDDLEWARE];
            $callback = $callback[self::CALLBACK];

            $isMatch = preg_match('~^'.$route.'$~', $requestPath,$matches);
            if (!$isMatch) continue;
            if (is_array($callback)) {
                $classname = $callback[0];
                $callback[0] = new $callback[0](Application::$app);
                $controller  =  $callback[0];
                $controller->setRequest($this->request);
                $controller->setAuth($this->auth);
                if ($middleware !== null) {
                    $controller->middleware($middleware);
                }
                $middleware  = $controller->getMiddleware();
                if (is_object($middleware)) {
                    $middleware->handle(Application::$app);
                }
                if(!method_exists($callback[0],$callback[1])){
                    throw new MethodNotFoundException("'$callback[1]' Method does not Exists inside $classname",404);
                }
            }
            $matches = array_slice($matches, 1);
            array_unshift($matches, $this->request);
            return call_user_func($callback, ...$matches);
        }
        throw new NotFoundException();
    }
}
