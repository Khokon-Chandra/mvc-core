<?php

namespace core;

trait ModelTrait
{

    public static function where($columnName, $operator = null, $value = null)
    {
        parent::where($columnName,$operator,$value);
    }

    public static function all()
    {
        return "hello";
        // return parent::all();
    }

    public static function get()
    {
       return parent::get();
    }

    public static function first()
    {
       return parent::first();
    }

    public static function select(...$fields)
    {
        parent::select(...$fields);
    }



    public static function create(array $data)
    {
       return parent::create($data);
    }


    public static function insert($data = [])
    {
       return parent::insert($data);
    }


    public static function update($data = [])
    {
        return parent::update($data);
    }



    public static function delete()
    {
       return parent::delete();
    }

  

    
}