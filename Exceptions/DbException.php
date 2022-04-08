<?php
namespace core\Exceptions;

class DbException extends \Exception
{
    protected $message;
    protected $code;

    public function __construct($message , $code)
    {
        $this->message = $message;
        $this->code    = $code;
    }
}