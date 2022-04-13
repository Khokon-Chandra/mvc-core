<?php
namespace khokonc\mvc\Exceptions;

class CsrfTokenNotVerified extends \Exception
{
    protected $message = "Csrf token is not verified";
    protected $code = 419;

    public function __construct(string $message=null)
    {
        if(!is_null($message)){
            $this->message = $message;
        }
    }

}