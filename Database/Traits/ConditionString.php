<?php
namespace khokonc\mvc\Database\Traits;

trait ConditionString
{

    private function bindValue($statement)
    {
        foreach ($this->valueAndParameters as $key => $value){
            $statement->bindValue($key,$value);
        }
    }

    public function where($columnName, $operator = null, $value = null)
    {
        if (func_num_args() === 2) {
            $value    = $operator;
            $operator = "=";
        }

        $string = "$columnName $operator:$columnName";
        $this->valueAndParameters[":$columnName"] = $value;

        if (!empty($this->conditionString)) {
            $this->conditionString .= " AND " . $string;
        } else {
            $this->conditionString = $string;
        }
        return $this;
    }

    public function orWhere($columnName, $operator = null, $value = null)
    {
        if (func_num_args() === 2) {
            $value    = $operator;
            $operator = "=";
        }

        if (empty($this->conditionString)) {
            throw new \Exception('Invalid orWhere() clouser', 500);
        }
        $string = "$columnName $operator:$columnName";
        $this->valueAndParameters[":$columnName"] = $value;
        $this->conditionString .= " OR " . $string;
        return $this;
    }

    public function whereBetween($columnName, array $values)
    {
        if(count($values) !=2){
            throw new \Exception('Invalid where clouser value');
        }
        $string = " BETWEEN :$columnName1 AND :$columnName2";
        $this->valueAndParameters[":$columnName1"] = $valueS[0];
        $this->valueAndParameters[":$columnName2"] = $valueS[1];

        if (!empty($this->conditionString)) {
            $this->conditionString .= " AND " . $string;
        } else {
            $this->conditionString = $string;
        }
        return $this;
    }

    public function whereIn(string $columnName, array $values)
    {
        $array  = array_map(fn($value)=>":$columnName$value",$values);
        $string = rtrim(implode(',',$array),',');
        foreach ($values as $value){
            $this->valueAndParameters[":$columnName$value"] = $value;
        }
        $this->conditionString = "$this->table.$columnName IN ($string)";
        return $this;
    }
}