<?php

namespace core;

class Request extends Validation
{


    public Session $session;

    public function __construct($session)
    {
        $this->session = $session;
    }

    public function ip()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            //ip from share internet
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            //ip pass from proxy
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }

    public function __get($name)
    {
        return $this->getBody()[$name] ?? null;
    }


    public function isAjax()
    {
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            return true;
        }
        return false;
    }

    private function getTokenWhenIsAjax()
    {
        return $_SERVER['X-CSRF-TOKEN'];
    }


    public function validate(array $rules = [])
    {
        $this->rules = $rules;
        $keys = array_keys($rules);
        foreach ($keys as $key) {
            $this->attributes[$key] = $this->getBody()[$key] ?? NULL;
        }

        $validate = $this->exicuteValidation();

        if ($validate) {
            return $this->attributes;
        }

        if ($validate === false && $this->isAjax()) {
            throw new \Exception(json_encode($this->errors), 422);
        }

        $this->session->setFlashMessage('errors', $this->errors);
        redirect($_SERVER["HTTP_REFERER"]);
    }


    public function getPath()
    {
        $path = rtrim($_SERVER['REQUEST_URI'], '/');
        $path = empty($path) ? '/' : $path;
        $position = strpos($path, '?');
        if ($position === false) {
            return $path;
        } else {
            return $path = substr($path, 0, $position);
        }
    }

    public function getMethod()
    {
        return strtolower($_SERVER['REQUEST_METHOD']);
    }

    public function isPost()
    {
        return $this->getMethod() === 'post' ? true : false;
    }

    public function getBody()
    {
        $body = [];
        if ($this->getMethod() === "get") {
            foreach ($_GET as $key => $value) {
                $body[$key] = filter_input(INPUT_GET, $key, FILTER_SANITIZE_SPECIAL_CHARS);
            }
        }

        if ($this->getMethod() === "post") {
            foreach ($_POST as $key => $value) {
                $body[$key] = filter_input(INPUT_POST, $key, FILTER_SANITIZE_SPECIAL_CHARS);
            }
        }
        return $body;
    }


    public function all()
    {
        return json_encode($this->getBody());
    }


    public function verifyCsrfTocken()
    {
        $attributes = $this->getBody();
        $token = $attributes['_token'] ?? false;

        if ($token !== $this->session->getToken()) {
            return false;
        }
        return true;
    }
}
