<?php

namespace tino\queryBuilder;

class Connection extends \PDO
{
    use ConfigurationTrait;

    private $conditionParser = 'ConditionParser';

    private $dsn;

    private $username;

    private $password;

    public function __construct($options)
    {
        $this->loadOtions($options);
        parent::__construct(
            $this->dsn,
            $this->username,
            $this->password
        );
    }

    public function select($colls)
    {
        return new Select($colls);
    }

    public function __get($name)
    {
        switch($name) {
        case 'conditionParser':
            return $this->instnace($this->conditionParser);
        }
    }
}
