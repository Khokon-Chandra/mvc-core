<?php

namespace khokonc\mvc;

class Auth
{
    private $session;

    private $key ;

    public function __construct(Session $session)
    {
        $this->key = 'auth_' . config('app.app_key');
        $this->session = $session;
    }

    public function attemt($attr)
    {
        foreach ($attr as $key => $value) {
            $this->{$key} = $value;
        }
        $this->session->set($this->key, $attr);
    }

    public function id()
    {
        return $this->session->get($this->key)['id'] ?? false;
    }

    public function user()
    {
        return $this->session->get($this->key);
    }

    public function logout()
    {
        $this->session->remove($this->key);
    }
    
}
