<?php

use khokonc\mvc\Application;

if (!function_exists('str_contains')) {
    function str_contains($haystack, $needle) {
        return $needle !== '' && mb_strpos($haystack, $needle) !== false;
    }
}

function view(string $view, array $params = [])
{
    return Application::$app->view->renderView($view, $params);
}


function _token()
{
    return Application::$app->session->getToken();
}

function csrf_token()
{
    return sprintf("<input type='hidden' name='_token' value='%s'>", _token());
}


function asset(string $file)
{
    return APP_URL . '/' . trim($file, '/');
}


function route($name, $params = null)
{
   return Application::$app->router->getRouteByName($name,$params);
}

function session_flash($key)
{
    return Application::$app->session->getFlashMessage($key);
}

function redirect(string $to = null)
{
    return new \khokonc\mvc\Http\HttpRedirectResponse($to);
}

function dd($object)
{
    echo "<pre>";
    var_dump($object);
    die();
}


function app($classname)
{
    return new $classname();
}
