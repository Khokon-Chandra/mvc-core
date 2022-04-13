<?php

namespace khokonc\mvc\Exceptions;


class RouteNameNotFound extends \Exception
{

    public function __construct($message = "", $code = 500, Throwable $previous = null)
    {
        $message = "Route name '$message' not defined";
        parent::__construct($message, $code, $previous);
    }

}