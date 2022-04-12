<?php


use App\Exceptions\HttpRedirectException;
use khokonc\mvc\Application;

$route = new Application();

if (!function_exists('str_contains')) {
    function str_contains($haystack, $needle) {
        return $needle !== '' && mb_strpos($haystack, $needle) !== false;
    }
}

function view(string $view, array $params = [])
{
    return Application::$app->view->renderView($view, $params);
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


function route($name, $params = null)
{
    $routeName =  Application::$app->router->routeNames[$name] ?? false;
    if ($routeName === false) {
        Application::$app->view->renderError("Route name <i class='text-danger'>$name</i> not found");
        return Route::$view->viewContent;
    }
    if(is_null($params)){
        return $routeName;
    }
    if (is_array($params)) {
        foreach ($params as $key => $value) {
            $routeName = str_replace("{{$key}}", $value, $routeName);
        }
        return $routeName;
    }

   return str_replace('{id}',$params,$routeName);

}

function session_flash($key)
{
    return Application::$app->session->getFlashMessage($key);
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
