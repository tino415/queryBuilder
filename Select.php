<?php

namespace tino\queryBuilder;

class Select extends Expression
{
    public $colls;
    public $from;

    public function parseColl($coll) 
    {
        if(is_string($coll)) return "`$coll`";
        elseif($coll instanceof Expression) return "($coll)";
    }

    public function parseColls()
    {
        $colls = [];
        foreach($this->colls as $coll) {
            $colls[] = $this->parseColl($coll);
        }

        if(empty($colls)) return '*';
        elseif(count($colls) === 1) return $colls[0];
        else return implode(', ', $colls);
    }

    public function __construct(array $colls = [])
    {
        $this->colls = $colls;
    }

    public function __toString()
    {
        $colls = $this->parseColls();
        $from = $this->parseColl($this->form);
        return "SELECT $colls FROM $from";
    }
}
