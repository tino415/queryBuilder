<?php

namespace tests;

use queryBuilder\Connection;
use queryBuilder\Expression;

class FromTest extends \PHPUnit_Framework_TestCase
{
    public function setUp() {
        $this->connection = new Connection(
            Config::$config['db']['dsn'],
            Config::$config['db']['username'],
            Config::$config['db']['password'],
            'queryBuilder\QueryBuilder'
        );
    }

    public function testFromTable()
    {
        echo "Testing simple From table\n";
        $this->assertEquals(
            "SELECT *\nFROM `user`\n",
            (string) $this->connection->select()
                ->from('user')
        );

        echo "From table with namespace\n";
        $this->assertEquals(
            "SELECT *\nFROM `authentication`.`user`\n",
            (string) $this->connection->select()
                ->from('authentication.user')
        );
    }

    public function testFromSelect()
    {
        echo "Testing select from select\n";

        $userSelect = $this->connection->select()->from('user');
        $selectFromSelect = $this->connection->select()->from($userSelect, 'user');

        $this->assertEquals(
            "SELECT *\nFROM (SELECT *\nFROM `user`\n) AS `user`\n",
            (string) $selectFromSelect
        );
    }

    public function testFromExpression()
    {
        echo "Select from SQL Expression\n",
        $this->assertEquals(
            "SELECT *\nFROM 1+3\n",
            (string) $this->connection->select()->from(new Expression("1+3"))
        );

        echo "Select from SQL Expression with alias\n";
        $this->assertEquals(
            "SELECT *\nFROM LOG(2) + `a` AS `expression`\n",
            (string) $this->connection->select()
            ->from(new Expression('LOG(2) + `a`'), 'expression')
        );
    }

    public function tearDown()
    {
        $this->connection = null;
    }
}
