<?php
namespace core\Database\Traits;

trait ConditionString
{
    public function where($columnName, $operator = null, $value = null)
    {
        if (func_num_args() === 2) {
            $value    = $operator;
            $operator = "=";
        }
        $string = "$columnName $operator " . "'$value'";
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
        $string = "$columnName $operator " . "'$value'";
        $this->conditionString .= " OR " . $string;
        return $this;
    }


    public function whereIn(string $columnName, array $values)
    {
        $value = rtrim(implode(',', $values), ',');
        $this->conditionString = "$this->table.$columnName IN ($value)";
        return $this;
    }
}