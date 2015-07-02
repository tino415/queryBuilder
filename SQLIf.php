<?php

namespace tino\queryBuilder;

class SQLIf extends Expression
{
    public $condition;
    public $result;

    public function __construct($condition, $result)
    {
        $this->condition = $condition;
        $this->result = $result;
    }

    public function __toString()
    {
        return "IF $this->condition THEN $this->result";
    }
}
