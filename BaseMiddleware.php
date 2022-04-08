<?php
namespace khokonc\mvc;

use khokonc\mvc\Routes\Route;

abstract class BaseMiddleware{

    abstract function handle(Route $app);
    
}