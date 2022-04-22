<?php

namespace khokonc\mvc\Routing\Routes;

trait RouteAble
{

    public function name($name)
    {
        $this->name = $name;
        return $this;
    }

    public function middleware($name)
    {
        $this->middleware = $name;
    }

    public function prefix($path)
    {
        $path = trim($path,'/');
        if(empty($path)) return $this;
        $this->path = trim($path."/".$this->path,'/');
        $this->noRegexPath = trim($path."/".$this->noRegexPath,'/');
        return $this;
    }


}