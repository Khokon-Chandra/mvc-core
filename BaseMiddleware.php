<?php
namespace khokonc\mvc;

use khokonc\mvc\Route\Route;

abstract class BaseMiddleware{

    abstract function handle(Route $app);
    
}