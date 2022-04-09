<?php

namespace khokonc\mvc\Views;
use khokonc\mvc\Request;
use khokonc\mvc\Routes\Route;
use khokonc\mvc\Session;
use khokonc\mvc\Exceptions\ViewNotFoundException;

class View
{
    use Component;

    const BASE_VIEW = BASE_URL . '/views';
    const ERROR_PATH = BASE_URL . '/khokonc\mvc/errors.php';

    public Request $request;
    public Session $session;

    public function __construct($request, $session)
    {
        $this->request = $request;
        $this->session = $session;
    }

    private function removeExtention($view)
    {
        return str_replace('.php', '', $view);
    }

    private function parseDirectory($view)
    {
        $path = str_replace('.', '/', $this->removeExtention($view));
        return $this->path = self::BASE_VIEW . "/$path.php";
    }


    private function getViewDirectory($view)
    {
        $path = $this->parseDirectory($view);

        if (!file_exists($path)) {
            $errorMessage = $view . " directory not found";
            throw new ViewNotFoundException($errorMessage,5000);
        }
        return $path;
    }


    private function applyVariable($content, $params = [])
    {
        if (!empty($params)) {
            extract($params);
        }
        preg_match_all('~{{[a-zA-z0-9.$-> ]*}}~mi', $content, $matches);
        foreach ($matches[0] as $match) {
            $placeholder = $match;
            $variable = trim(ltrim(rtrim($placeholder, '}}'), '{{'), '$');
            $content = str_replace($match, $variable, $content);
        }
        return $content;
    }


    public function getViewContent($path, $params = [])
    {
        try {
            ob_start();
            if (!is_null($params)) {
                extract($params);
            }
            extract([
                'error' => $this->session->getFlashMessage('errors'),
                'auth' => Route::$app->auth
            ]);
            include $path;
            $content = ob_get_clean();
            return $content;
        }catch (\Exception $error){
            return $error->getMessage();
        }

    }




    public function renderView(string $view, array $params = [])
    {
        $path    = $this->getViewDirectory($view);
        $content = $this->getViewContent($path,$params);
        $content = new CompileInclude($content);
//        $this->renderComponent($content);


    }


}
