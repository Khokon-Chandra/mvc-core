<?php

namespace khokonc\mvc\Views;

use khokonc\mvc\Exceptions\ViewNotFoundException;

trait Path
{
    private function removeExtention($view)
    {
        return str_replace('.php', '', $view);
    }

    private function parseDirectory($view)
    {
        $path = str_replace('.', '/', $this->removeExtention($view));
        return $this->path = config('app.view_path') . "/$path.php";
    }


    private function getViewDirectory($view)
    {
        $path = $this->parseDirectory($view);

        if (!file_exists($path)) {
            $errorMessage = $view . " directory not found";
            throw new ViewNotFoundException($errorMessage, 5000);
        }
        return $path;
    }
}