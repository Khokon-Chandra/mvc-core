<?php
namespace khokonc\mvc;

use khokonc\mvc\Application; 

abstract class BaseMiddleware{

    abstract function handle(Application $app);
    
}