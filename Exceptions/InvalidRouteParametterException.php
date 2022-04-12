<?php

namespace khokonc\mvc\Exceptions;


class InvalidRouteParametterException extends \Exception
{

    protected $message;
    protected $code;

    public function __construct($message = "", $code = 0)
    {
        parent::__construct($message,$code);
    }

}