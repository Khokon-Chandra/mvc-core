<?php

namespace khokonc\mvc\Routing;

interface RouteInterface
{
    public function get($path, $callback);

    public function post():Router;

    public function group():Router;

    public function prefix():Router;

    public function resource():Router;

}