<?php


namespace core\Database;

class Database extends \PDO
{

    public function __construct()
    {
        $dsn      = "mysql:host=" . DB_HOST . ";port=3306;dbname=" . DB_NAME;
        $username = USER_NAME;
        $password = PASSWORD;
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
