<?php

namespace khokonc\mvc\Views;

use khokonc\mvc\Request;
use khokonc\mvc\Routes\Route;
use khokonc\mvc\Session;

class View
{
    use Path, Component, CompileInclude;

    const BASE_VIEW = BASE_URL . '/views';

    const ERROR_PATH = BASE_URL . '/khokonc\mvc/errors.php';

    private const PATTERN = '~@include\("[a-zA-Z0-9._]*"\)~m';

    private ?array $matches = null;

    public Request $request;
    public Session $session;

    public function __construct($request, $session)
    {
        $this->request = $request;
        $this->session = $session;
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

    }


    public function renderView(string $view, array $params = [])
    {
        $path = $this->getViewDirectory($view);
        $content = $this->getViewContent($path, $params);
        $content = $this->renderInclude($content, $params);
        return $this->renderComponent($content);


    }


}
