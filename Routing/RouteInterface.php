<?php

namespace khokonc\mvc\Routing;

interface RouteInterface
{
    public static function get(string $path,callable $callback):Router;

    public static function post():Route;

    public static function group():Route;

    public static function prefix():Route;

    public static function resource():Route;

}