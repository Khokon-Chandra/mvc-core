<?php

namespace khokonc\mvc\Routes;

use app\Controllers\Controller;
use app\Exceptions\NotFoundException;
use app\Exceptions\HttpRedirectException;
use khokonc\mvc\Auth;
use khokonc\mvc\Database\Database;
use khokonc\mvc\Request;
use khokonc\mvc\Session;
use khokonc\mvc\View;

class Route
{
    public View $view;
    public Request $request;
    public Session $session;
    public Router $router;
    public Auth $auth;
    public Controller $controller;
    public Database $db;
    public static $app;
    public function __construct()
    {
        $this->session = new Session();
        $this->request = new Request($this->session);
        $this->view    = new View($this->request, $this->session);
        $this->auth    = new Auth($this->session);
        $this->router  = new Router($this->request, $this->session, $this->auth);
        $this->db      = new Database();
        self::$app     = $this;
    }

    public static function get($path, $callback)
    {
        return self::$app->router->get($path, $callback);
    }

    public static function post($path, $callback)
    {
        return self::$app->router->post($path, $callback);
    }

    public static function resource($path, $class)
    {
        return self::$app->router->resource($path, $class);
    }

    public static function group(array $attribute = [], $callback)
    {
        return self::$app->router->group($attribute, $callback);
    }

    public function name($name)
    {
        self::$app->router->name($name);
    }


    public function run()
    {
        try {
            return self::$app->router->resolve();
        } catch (NotFoundException $error) {
            http_response_code($error->getCode());
            $view = BASE_URL . "/views/errors/404.php";
            if (file_exists($view)) {
                return view('errors.404', [
                    'pageTitle' => 'Page not found',
                    'code' => $error->getCode(),
                    'message' => $error->getMessage()
                ]);
            } else {
                return $error->getMessage();
            }
        } catch (HttpRedirectException $error) {
            http_response_code($error->getCode());
            header("location:" . $error->getMessage());
        } catch (\Exception $error) {
            http_response_code($error->getCode());
            return $error->getMessage();
        }
    }
}
