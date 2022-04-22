<?php

namespace khokonc\mvc\Exceptions;



class MethodNotFoundException extends \Exception
{
    protected $message;
    protected $code = 405;
    public function __construct($action,$controller)
    {
        $this->message = "$action method does'nt exists inside $controller";
    }
}