<?php

namespace khokonc\mvc\Exceptions;


class ViewNotFoundException extends \Exception
{
    protected $code = 5000;
    protected $message = 'view directory not found';

    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}