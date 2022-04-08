<?php

namespace khokonc\mvc\Database\Traits;

trait Relations
{

    private function pair_parent_child($parent, $foreign_key, $owner_key)
    {
        foreach ($parent as $parent) {
            if ($foreign_key == $parent->{$owner_key}) {
                return $parent;
            }
        }
        return null;
    }

    private function pair_child_parent($child, $foreign_key, $owner_key)
    {
        foreach ($child as $parent) {
            if ($owner_key == $child->{$foreign_key}) {
                return $parent;
            }
        }
        return null;
    }

    private function hasRelation($data)
    {
        if (empty($this->relations)) {
            return $data;
        }
        foreach ($this->relations as $relation => $property) {
            if ($property['name'] == 'belongsTo') :
                $data = $this->ifBelongsTo($data, $relation, $property);
            elseif ($property['name'] == 'belongsToMany') :
                $data = $this->ifBelongsToMany($data, $relation, $property);
            elseif ($property['name'] == 'hasMany') :
                $data = $this->ifHasMany($data, $relation, $property);
            endif;
        }
        return $data;
    }


    private function ifBelongsTo($data, $relation, $property)
    {
        $parentClass = $property['modelname'];
        $parentKey   = $property['owner_key'];
        $foreign_key  = $property['foreign_key'];

        $ids       = $this->pluck('id');
        $parents   = (new $parentClass())->whereIn($parentKey, $ids)->get();

        if (is_array($data)) :
            foreach ($data as $key => $model) {
                $data[$key]->{$relation} = $this->pair_parent_child($parents, $model->{$foreign_key}, $parentKey);
            }
        else :
            $data->{$relation} = (new $parentClass())->where($parentKey, $data->id)->first();
        endif;

        return $data;
    }


    private function ifBelongsToMany($data, $relation, $property)
    {
        $parentClass = $property['modelname'];
        $parentKey   = $property['owner_key'];
        $foreign_key  = $property['foreign_key'];

        $ids       = $this->pluck('id');
        $parents    = (new $parentClass())->whereIn($parentKey, $ids)->get();

        if (is_array($data)) :
            foreach ($data as $key => $model) {
                $pair = $this->pair_parent_child($parents, $model->{$foreign_key}, $parentKey);
                if(is_null($pair)){
                    $data[$key]->{$relation} = [];
                }else{
                    $data[$key]->{$relation}[] = $pair;
                }
            }
        else :
            $data->{$relation} = (new $parentClass())->where($parentKey, $data->id)->get();
        endif;

        return $data;
    }

    private function ifHasMany($data, $relation, $property)
    {
        $childClass  = $property['modelname'];
        $parentKey   = $property['local_key'];
        $foreign_key  = $property['foreign_key'];

        $ids    = $this->pluck('id');
        $childs = (new $childClass())->whereIn($parentKey, $ids)->get();

        if (is_array($data)) :
            foreach ($data as $key => $model) {
                $pair = $this->pair_parent_child($childs, $model->{$parentKey}, $foreign_key);
                if (!is_null($pair)) {
                    $data[$key]->{$relation}[] = $pair;
                } else {
                    $data[$key]->{$relation} = [];
                }
            }
        else :
            $data->{$relation} = (new $childClass())->where($parentKey, $data->id)->get();
        endif;
        return $data;
    }



    public function belongsTo($parentClass, $foreign_key, $owner_key)
    {
        return [
            'name' => 'belongsTo',
            "modelname" => $parentClass,
            "foreign_key" => $foreign_key,
            "owner_key" => $owner_key,
        ];
    }

    public function hasMany($childClass, $foreign_key, $local_key)
    {
        return [
            'name' => 'hasMany',
            "modelname" => $childClass,
            "foreign_key" => $foreign_key,
            "local_key" => $local_key,
        ];
    }
}
