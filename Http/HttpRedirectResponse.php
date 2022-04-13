<?php
namespace khokonc\mvc\Http;

use khokonc\mvc\Application;

class HttpRedirectResponse
{

    public $url;

    public function __construct($to=null)
    {
        $this->url = $to;
    }

    public function route($routeName)
    {
        $this->url = route($routeName);
        return $this;
    }

    public function back()
    {
        $this->url = $_SERVER["HTTP_REFERER"];
        return $this;
    }

    public function with(string $key,string $value)
    {
        Application::$app->session->setFlashMessage($key,$value);
        return $this;
    }

}