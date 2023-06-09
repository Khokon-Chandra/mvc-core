<?php

namespace khokonc\mvc\Views;

use khokonc\mvc\Request;
use khokonc\mvc\Application;
use khokonc\mvc\Session;

class View
{
    use Path, Component, CompileInclude,Csrf;

    const ERROR_PATH = __DIR__.'/../errors.php';

    private const PATTERN = '~@include\("[a-zA-Z0-9._]*"\)~m';

    private ?array $matches = null;

    public Request $request;
    public Session $session;

    public function __construct($request, $session)
    {
        $this->request = $request;
        $this->session = $session;
    }


    public function renderError($message,$code)
    {
        return $this->getViewContent(self::ERROR_PATH,[
            'message' =>$message,
            'code' =>$code
        ]);
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
                'auth' => Application::$app->auth
            ]);
            include $path;
            $content = ob_get_clean();
            return $content;
        }catch (\Exception $error)
        {
            return $error->getMessage();
        }

    }


    public function renderView(string $view, array $params = [])
    {
        $path = $this->getViewDirectory($view);
        $content = $this->getViewContent($path, $params);
        $content = $this->renderComponent($content);
        $content = $this->renderInclude($content, $params);
        $content = $this->renderCsrf($content);
        return $content;
    }


}
