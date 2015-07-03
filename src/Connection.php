<?php

namespace queryBuilder;

class Connection extends \PDO
{
    private $queryParser;

    public function __construct(
        $dsn, 
        $username, 
        $password, 
        $conditionParser = "\queryBuilder\QueryParser"
    ) {
        parent::__construct(
            $dsn,
            $username,
            $password
        );
        $this->queryParser = new $conditionParser($this);
        $this->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    }

    public function select($colls = [])
    {
        return new Select($colls, $this);
    }

    public function __get($name)
    {
        switch($name) {
        case 'queryParser':
            return $this->queryParser;
        }
    }
}
