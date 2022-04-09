<?php

namespace khokonc\mvc\Exceptions;

class InvalidComponentException extends \Exception
{
    public function __construct($prefix,$postfix)
    {
        $message = "component names are not correctly formated <i class='text-danger'> $prefix</i> and <i class='text-danger'> $postfix</i>"
        parent::__construct($message,500);
    }

}