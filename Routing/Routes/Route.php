<?php

namespace khokonc\mvc\Routing\Routes;

class Route
{
    use RouteAble;

    public $method;

    public $path;

    public $callback;

    public $classname;

    public $action;

    public $controller;

    public $middleware = null;

    public $name;

    public $noRegexPath;


    public function __construct($path, $method, $callback)
    {
        $this->parseUrl($path);
        $this->method = $method;
        $this->callback = $callback;
    }

    private function parseUrl($path)
    {
        $this->path = $path['with_regex'];
        $this->noRegexPath = $path['no_regex'];
    }

    private function setTempRoute($path)
    {
        $this->tempRoute = APP_URL . $path;
    }

}