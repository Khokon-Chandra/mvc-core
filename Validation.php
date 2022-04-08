<?php

namespace core;

use core\Database\Database;

class Validation
{

    protected const REQUIRED = 'required';
    protected const EMAIL = 'email';
    protected const MAX = 'max';
    protected const MIN = 'min';
    protected const MATCH = 'match';
    protected const UNIQUE = 'unique';
    protected const INCORRECT = 'incorrect';
    protected const EXISTS = 'exists';
    protected const PASSWORD = 'password';
    protected const IMAGE = 'image';
    public array $errors = [];
    protected array $rules = [];
    protected array $attributes = [];


    private function beautifyRuleName()
    {
        foreach ($this->rules as $attribute => $rulename) {
            $rulename = explode('|', $rulename);
            foreach ($rulename as $index => $rule) {
                $wilcard = explode(':', $rule);
                if (count($wilcard) === 2) {
                    $rule = [$wilcard[0] => $wilcard[1]];
                }
                $rulename[$index] = $rule;
            }
            $this->rules[$attribute] = $rulename;
        }
    }



    public function exicuteValidation()
    {
        $this->beautifyRuleName();

        foreach ($this->rules as $attribute => $rule) {
            $value = $this->attributes[$attribute];
            foreach ($rule as $item) {
                $rulename = is_array($item) ? array_key_first($item) : $item;
                if (self::REQUIRED === $rulename && empty($value)) {
                    $this->addError($attribute, self::REQUIRED, ['attr' => $attribute]);
                }

                if (self::MIN === $rulename && !empty($value) && strlen($value) < reset($item)) {
                    $this->addError($attribute, self::MIN, $item);
                }

                if (self::MAX === $rulename && strlen($value) > reset($item)) {
                    $this->addError($attribute, self::MAX, $item);
                }

                if (self::UNIQUE === $rulename && !empty($value)) {
                    $object = $this->getOne(reset($item), $attribute, $value);
                    if (!empty($object)) {
                        $this->addError($attribute, self::UNIQUE, ['field' => $attribute]);
                    }
                }

                if (self::EXISTS === $rulename && !empty($value)) {
                    $object = $this->getOne(reset($item), $attribute, $value);
                    if (empty($object)) {
                        $this->addError($attribute, self::EXISTS, ['field' => $attribute]);
                    }
                }
            }
        }
        return empty($this->errors);
    }



    protected function addError($attribute, $rule, $params = [])
    {
        $messages = $this->errorMessages()[$rule] ?? "";
        foreach ($params as $key => $value) {

            $messages = str_replace("{{$key}}", $value, $messages);
        }
        $this->errors[$attribute] = $messages;
    }



    private function errorMessages()
    {
        return [
            self::REQUIRED  => "{attr} is required",
            self::EMAIL     => "This field must be valid email address",
            self::MIN       => "Minimum length of this field {min}",
            self::MAX       => "Maximum length of this field {max}",
            self::MATCH     => "This field must be the same as {match}",
            self::UNIQUE    => "Record with this {field} already exists",
            self::INCORRECT => "Incorrect {field}",
            self::EXISTS    => "Record doesn't exists with this {field}",
            self::PASSWORD  => "Invalid record"
        ];
    }

    private function getOne($tableName, $attribute, $value)
    {
        $db        = new Database();
        $sql       = "SELECT $attribute FROM $tableName WHERE $attribute = :attr";
        $statement = $db->prepare($sql);
        $statement->bindValue(":attr", $value);
        $statement->execute();
        return $statement->fetchObject();
    }
}
