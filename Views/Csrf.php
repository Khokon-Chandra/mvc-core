<?php

namespace khokonc\mvc\Views;

use khokonc\mvc\Application;

trait Csrf
{

    public function renderCsrf($content)
    {
        $token = Application::$app->session->getToken();
        $input = sprintf('<input type="hidden" name="_token" value="%s">',$token);
        return preg_replace('~@csrf~',$input,$content);
    }

}