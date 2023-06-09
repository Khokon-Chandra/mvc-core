<?php


namespace khokonc\mvc\Database;

class Database extends \PDO
{

    public function __construct()
    {
        
        $dsn      = "mysql:host=" . config('app.database.host') . ";port=". config('app.database.port') .";dbname=" . config('app.database.name');
        $username = config('app.database.user');
        $password = config('app.database.password');
        try {
            parent::__construct($dsn, $username, $password);
            $this->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        } catch (\PDOException $error) {
            http_response_code($error->getCode());
            echo $error->getMessage();
            exit();
        }
    }
}
