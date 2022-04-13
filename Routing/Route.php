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

    public static function __callStatic($name, $arguments)
    {
         return self::$application->router->{$name}(...$arguments);
    }


}