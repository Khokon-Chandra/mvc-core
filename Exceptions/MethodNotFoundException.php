<?php

namespace khokonc\mvc\Exceptions;



class MethodNotFoundException extends \Exception
{
    public function __construct($message = "", $code = 500)
    {
        parent::__construct($message, $code);
    }
}