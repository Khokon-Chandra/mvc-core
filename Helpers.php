<?php


use App\Exceptions\HttpRedirectException;
use khokonc\mvc\Routes\Route;

$route = new Route();



function view(string $view, array $params = [])
{
    return Route::$app->view->renderView($view, $params);
}

function getApp()
{
    global $route;
    return $route;
}

function _token()
{
    $app = getApp();
    if (empty($app->session->getToken())) {
        $app->session->setToken();
    }
    return $app->session->getToken();
}

function csrf_token()
{
    return sprintf("<input type='hidden' name='_token' value='%s'>", _token());
}


function asset(string $file)
{
    echo APP_URL . '/' . trim($file, '/');
}


function route($name, $params = [])
{
    $routeName =  Route::$app->router->routeNames[$name] ?? false;
    if ($routeName === false) {
        Route::$app->view->renderError("Route name <i class='text-danger'>$name</i> not found");
        return Route::$view->viewContent;
    }
    if (!empty($params)) {
        foreach ($params as $key => $value) {
            $routeName = str_replace("{{$key}}", $value, $routeName);
        }
    }
    return $routeName;
}

function session_flash($key)
{
    return Route::$app->session->getFlashMessage($key);
}

function redirect(string $To)
{
    throw new HttpRedirectException($To);
}

function dd($object)
{
    printf("<pre>%s</pre>",var_dump($object));
    exit();
}


function app($classname)
{
    return new $classname();
}
