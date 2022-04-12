<?php

namespace khokonc\mvc\Routing;

use khokonc\mvc\Application;

class Route
{
    private static Application $application;

    public function __construct(Application $application)
    {
        self::$application = $application;
    }


    public static function get($path, $callback)
    {
        return self::$application->router->get($path, $callback);
    }

    public static function post($path, $callback)
    {
        return self::$application->router->post($path, $callback);
    }

    public static function resource($path, $class)
    {
        return self::$application->router->resource($path, $class);
    }

    public static function group(array $attribute = [], $callback)
    {
        return self::$application->router->group($attribute, $callback);
    }

    public function name($name)
    {
        self::$application->router->name($name);
    }

}