<?php

namespace khokonc\mvc;

use App\Exceptions\NotFoundException;
use khokonc\mvc\Auth;
use khokonc\mvc\Database\Database;
use khokonc\mvc\Exceptions\HttpRedirectException;
use khokonc\mvc\Http\HttpRedirectResponse;
use khokonc\mvc\Request;
use khokonc\mvc\Routing\Route;
use khokonc\mvc\Routing\Router;
use khokonc\mvc\Session;

use khokonc\mvc\Views\View;

/**
 *
 */
class Application
{
    public View $view;
    public Request $request;
    public Session $session;
    public Auth $auth;
    public Database $db;
    public Router $router;
    public array $middleware;
    public static $app;
    public function __construct()
    {
        $this->loadCredential();
        $this->session = new Session();
        $this->request = new Request($this->session);
        $this->view    = new View($this->request, $this->session);
        $this->auth    = new Auth($this->session);
        $this->router  = new Router();
        $this->db      = new Database();
        self::$app     = $this;
    }

    public  function  init()
    {
        new Route($this);
    }


    private function loadCredential()
    {
        require_once "Helpers.php";
        $this->middleware = require_once BASE_URL."/app/Kernel.php";
    }


    public function run()
    {
        try {
             $response = $this->router->resolve();
             if($response instanceof HttpRedirectResponse){
                 throw new HttpRedirectException($response->url);
             }
             return $response;
        } catch (NotFoundException $error) {
            http_response_code($error->getCode());
            $view = VIEW_PATH_FOR_ERRORS."/404.php";
            if (file_exists($view)) {
                return view('errors.404', [
                    'pageTitle' => 'Page not found',
                    'code' => $error->getCode(),
                    'message' => $error->getMessage()
                ]);
            }
            return $error->getMessage();
        }catch (HttpRedirectException $error){
            http_response_code($error->getCode());
            header("location:".$error->getMessage());
        }
        catch (\Exception $error) {
            http_response_code($error->getCode());
            if(APP_DEBUG){
                return $this->view->renderError($error->getMessage(),$error->getCode());
            }
            return $error->getMessage();
        }
    }
}
