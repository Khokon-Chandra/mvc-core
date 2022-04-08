<?php
namespace core;

use core\Route\Route;

abstract class BaseMiddleware{

    abstract function handle(Route $app);
    
}