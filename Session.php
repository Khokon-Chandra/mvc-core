<?php


namespace khokonc\mvc;


class Session
{
    public const FLASH_KEY = 'flash_messages';
    private const TOKEN    = '_token';

    public function __construct()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $flashMessages = $_SESSION[self::FLASH_KEY] ?? [];
        foreach ($flashMessages as $key => &$flashmessage) {
            $flashmessage['remove'] = true;
        }
        $_SESSION[self::FLASH_KEY] = $flashMessages;
        $this->setToken();
    }

    public function setToken()
    {
       if(empty($this->getToken())){
           $this->set(self::TOKEN, $this->getRandomString(30));
       }
    }

    public function getToken()
    {
        return $this->get(self::TOKEN);
    }


    public function setFlashMessage($key, $message)
    {
        $_SESSION[self::FLASH_KEY][$key] = [
            "remove" => false,
            "message" => $message
        ];
    }

    public function getFlashMessage($key)
    {
        return $_SESSION[self::FLASH_KEY][$key]["message"] ?? false;
    }

    public function set($key, $value)
    {
        $_SESSION[$key] = $value;
    }


    public function get($key)
    {
        return $_SESSION[$key] ?? false;
    }


    public function remove($key)
    {
        unset($_SESSION[$key]);
    }


    private function getRandomString($n)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';

        for ($i = 0; $i < $n; $i++) {
            $index = rand(0, strlen($characters) - 1);
            $randomString .= $characters[$index];
        }
        return $randomString;
    }


    public function __destruct()
    {
        $flashMessages = $_SESSION[self::FLASH_KEY] ?? [];
        foreach ($flashMessages as $key => &$flashMessage) {
            if ($flashMessage['remove']) {
                unset($flashMessages[$key]);
            }
        }
        $_SESSION[self::FLASH_KEY] = $flashMessages;
    }
}
