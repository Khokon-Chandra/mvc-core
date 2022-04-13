<?php
namespace khokonc\mvc\Exceptions;

use khokonc\mvc\Application;

class HttpRedirectException extends \Exception
{
    protected $code = 302;
    protected $message;

    public function __construct(string $message=null)
    {
        $this->message = $message;  
    }

}