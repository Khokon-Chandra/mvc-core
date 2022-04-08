<?php

namespace khokonc\mvc;
use khokonc\mvc\Routes\Route;
class View
{
    const ERROR_PATH = BASE_URL . '/khokonc\mvc/errors.php';
    public $viewContent = '';
    private $path = '';

    public Request $request;
    public Session $session;

    public function __construct($request, $session)
    {
        $this->request = $request;
        $this->session = $session;
    }


    private function setViwPath($view)
    {
        $path = str_replace('.', '/', $this->removeExtention($view));
        $this->path = BASE_URL . "/views/$path.php";

        if (!file_exists($this->path)) {
            $errorMessage = $view . " file not exists";
            $this->renderError($errorMessage);
        }
    }


    private function removeExtention($view)
    {
        return str_replace('.php', '', $view);
    }


    public function renderError($errors)
    {
        $this->path = self::ERROR_PATH;
        $this->viewContent = $this->getObject(['errors' => $errors]);
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


    public function getObject($params = [])
    {
        ob_start();
        if (!is_null($params)) {
            extract($params);
        }

        extract(['error' => $this->session->getFlashMessage('errors'), 'auth' => Route::$app->auth]);
        include $this->path;
        return ob_get_clean();
    }


    public function getComponentDirectory($component = [])
    {
        $firstMatch = $component[0];
        $componentName = ltrim($firstMatch, '<x-');
        $componentName = rtrim($componentName, '>');
        $componentName = str_replace('.', '/', $componentName);
        $componentPath = BASE_URL . "/views/$componentName.php";
        if (file_exists($componentPath) === false) {
            $this->renderError("component name " . $componentName . " not found");
            return false;
        }
        return $componentPath;
    }



    public function hasComponent($string)
    {
        preg_match_all('~<x-[a-zA-z0-9.]*>|</x-[a-zA-z0-9.]*>~mi', $string, $matches);
        if (count($matches[0]) === 2) {
            $matches = $matches[0];
            $firstName = rtrim(ltrim($matches[0], '<x-'), '>');
            $lastName = rtrim(ltrim($matches[1], '</x-'), '>');

            if ($firstName === $lastName) {
                return $matches;
            }
            $errorMessage = "component names are not correctly formated <i class='text-danger'> $firstName</i> and <i class='text-danger'> $lastName</i>";
            $this->renderError($errorMessage);
        }
        return false;
    }

    public function sliceViewContent(string $content, array $matches)
    {
        $content = str_replace($matches[0], '', $content);
        $content = str_replace($matches[1], '', $content);
        return $content;
    }



    public function processComponent($view, $params = [])
    {
        $viewObject = $this->getObject($params);
        $hasComponent = $this->hasComponent($viewObject);
        $componentDirectory = $hasComponent ? $this->getComponentDirectory($hasComponent) : false;
        $viewContent = $componentDirectory ?  $this->sliceViewContent($viewObject, $hasComponent) : false;
        if ($viewContent !== false) {
            $params['slot'] = $viewContent;
            $this->path = $componentDirectory;
            $this->viewContent = $this->getObject($params);
        }else{
            $this->viewContent = $viewObject;
        }
    }


    public function renderView(string $view, array $params = [])
    {
        $this->setViwPath($view);
        $this->processComponent($view, $params);
        return $this->viewContent;
    }
}
