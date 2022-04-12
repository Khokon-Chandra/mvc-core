<?php

namespace khokonc\mvc;

use App\Exceptions\NotFoundException;
use App\Exceptions\HttpRedirectException;
use khokonc\mvc\Auth;
use khokonc\mvc\Database\Database;
use khokonc\mvc\Request;
use khokonc\mvc\Routing\Router;
use khokonc\mvc\Session;

use khokonc\mvc\Views\View;

class Application
{
    public View $view;
    public Request $request;
    public Session $session;
    public Auth $auth;
    public Database $db;
    public Router $router;
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
