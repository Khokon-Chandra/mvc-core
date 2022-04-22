<?php

namespace khokonc\mvc\Exceptions;

use JetBrains\PhpStorm\Internal\LanguageLevelTypeAware;

class MiddlewareNotFoundException extends \Exception
{
    protected $message;
    protected $code = 500;

    public function __construct($middleware)
    {
        $this->message = "Unknown middleware '$middleware' not registered inside Kernel";
    }

}