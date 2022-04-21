<?php

namespace khokonc\mvc\Database;

use khokonc\mvc\Database\Traits\ConditionString;
use khokonc\mvc\Database\Traits\Relations;
use khokonc\mvc\Request;
use khokonc\mvc\Application;

abstract class Model
{
    use ConditionString, Relations;

    private const BELONGSTO = 'belongsTo';
    private const HASMANY   = 'hasMany';

    /*
     * Database connection
     * */
    private Database $db;
    /*
     * Http Request
     */
    private Request $request;
    /*
     * Condition string
     */
    private string $conditionString = '';
    /*
     * @Array conditional bind paramitter
     */
    private array $valueAndParameters = [];

    /*
     * Array of relationships
     */
    private $relations = [];

    /*
     * Table retrive select query
     */
    private $selectSql;

    /*
     * Model table name
     */
    protected  $table;

    public function __construct()
    {
        $this->db = Application::$app->db;
        $this->request = Application::$app->request;
        $this->selectSql = "SELECT * FROM $this->table";
    }


    public function all()
    {
        $statement  = $this->db->prepare($this->selectSql);
        $statement->execute();
        $statement->setFetchMode(\PDO::FETCH_CLASS, get_called_class());
        return $statement->fetchAll();
    }

    public function get()
    {

        if (!empty($this->conditionString)) {
            $this->selectSql .= " WHERE $this->conditionString";
        }
        $statement = $this->db->prepare($this->selectSql);
        $this->bindValue($statement);
        $statement->execute();
        $data = $statement->fetchAll(\PDO::FETCH_CLASS, get_called_class());
        return $this->hasRelation($data);
    }

    public function pluck(...$fields)
    {
        $columnName = implode(',', $fields);

        $sql = "SELECT $columnName FROM $this->table";
        if (!empty($this->conditionString)) {
            $sql = "SELECT $columnName FROM $this->table WHERE $this->conditionString";
        }

        $statement = $this->db->prepare($sql);
        $statement->execute();
        if (count($fields) > 1) {
            $data = $statement->fetchAll(\PDO::FETCH_KEY_PAIR);
        } else {
            $data = $statement->fetchAll(\PDO::FETCH_COLUMN, 0);
        }
        return $data;
    }


    public function find($id)
    {
        $sql = "SELECT * FROM $this->table WHERE id=:id";
        $statement = $this->db->prepare($sql);
        $statement->bindValue(":id",$id);
        $statement->execute();
        $data = $statement->fetchObject(get_called_class());
        return $this->hasRelation($data);
    }


    public function first()
    {
        if (!empty($this->conditionString)) {
            $this->selectSql .= " WHERE $this->conditionString";
        }
        $statement = $this->db->prepare($this->selectSql);
        $this->bindValue($statement);
        $statement->execute();
        $data = $statement->fetchObject(get_called_class());
        return $this->hasRelation($data);
    }

    public function select(...$fields)
    {
        $placeholders = implode(',', $fields);
        $this->selectSql = "SELECT $placeholders FROM $this->table";
        return $this;
    }


    public function paginate($limit = 10)
    {
        $pageCount = $this->request->page ?? 0;
        $offset    = $pageCount > 0 ? (intval($pageCount) - 1) * $limit : 0;
        $limitSql  = " LIMIT $offset, $limit";

        if (!empty($this->conditionString)) {
            $sql = "SELECT * FROM $this->table WHERE $this->conditionString " . $limitSql;
            $aggregateSql = "SELECT COUNT(*) as aggregate FROM $this->table WHERE $this->conditionString";
        } else {
            $sql = "SELECT * FROM $this->table" . $limitSql;
            $aggregateSql = "SELECT COUNT(*) as aggregate FROM $this->table";
        }


        $statement = $this->db->prepare($sql);
        $this->bindValue($statement);
        $statement->execute();
        $data = $statement->fetchAll(\PDO::FETCH_CLASS, get_called_class());


        $data = $this->hasRelation($data);

        $statement = $this->db->prepare($aggregateSql);
        $statement->execute();
        $aggregate = $statement->fetchObject()->aggregate;

        $this->conditionString = '';
        $this->valueAndParameters = [];

        $paginate = new Paginate([
            "limit"       => $limit,
            "aggregate"   => $aggregate,
            "data"        => $data,
            "currentPage" => $this->request->page ?? 1,
            "path"        => APP_URL . $this->request->getPath(),
        ]);

        return $paginate;
    }



    public function create(array $data)
    {
        if (empty($data)) {
            throw new \Exception('record given empty');
        }
        $fields       = implode(',', array_keys($data));
        $placeholders = implode(',', array_map(fn ($attr) => ":$attr", array_keys($data)));
        $sql          = "INSERT INTO $this->table($fields) VALUES($placeholders)";
        $statement    = $this->db->prepare($sql);
        foreach ($data as $key => $value) {
            $statement->bindValue(":$key", $value);
        }
        $statement->execute();
        return $this->where('id', $this->db->lastInsertId())->first();
    }


    public function insert($data = [])
    {
        if (empty($data)) {
            throw new \Exception('record given empty');
        }
        $array_keys   = array_keys($data[0]);
        $fields       = implode(',', $array_keys);
        $arrayOfPlaceholder = [];
        foreach ($data as $row) {
            $arrayOfPlaceholder[] = implode(',', array_map(fn ($attr) => ":$attr", $array_keys));
        }
        $placeholders = "(" . implode("),(", $arrayOfPlaceholder) . ")";
        $sql          = "INSERT INTO $this->table($fields) VALUES $placeholders";
        $statement    = $this->db->prepare($sql);
        foreach ($data as $row) {
            foreach ($row as $key => $value) {
                $statement->bindValue(":$key", $value);
            }
        }
        $statement->execute();
        return true;
    }


    public function update($data = [])
    {
        if (empty($data)) {
            throw new \Exception('record given empty');
        }

        $placeholders = implode(', ', array_map(fn ($attr) => "$attr=:$attr", array_keys($data)));
        $sql          = "UPDATE $this->table SET $placeholders WHERE $this->conditionString ";
        $statement    = $this->db->prepare($sql);
        foreach ($data as $key => $value) {
            $statement->bindValue(":$key", $value);
        }
        $statement->execute();
        return true;
    }



    public function delete()
    {
        $statement = $this->db->prepare("DELETE FROM $this->table WHERE $this->conditionString");
        $statement->execute();
        return true;
    }


    public function with(...$relations)
    {

        foreach ($relations as $relation) {
            $this->relations[$relation] = $this->{$relation}();
        }

        return $this;
    }




    protected function when($status, $callback)
    {
        if ($status) {
            call_user_func($callback, ...[$this, $status]);
        }
        return $this;
    }
}
