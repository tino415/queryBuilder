<?php

namespace tests;

use queryBuilder\Connection;
use queryBuilder\Expression;

class SelectTest extends \PHPUnit_Framework_TestCase
{
    public function setUp() {
        $this->connection = new Connection(
            Config::$config['db']['dsn'],
            Config::$config['db']['username'],
            Config::$config['db']['password'],
            'queryBuilder\QueryBuilder'
        );
    }

    public function testSelectNoColumn()
    {
        echo "Select no column\n";
        $this->assertEquals(
            "SELECT *\n",
            (string) $this->connection->select()
        );
    }

    public function testSelectColumn()
    {

        echo "Simple select one column\n";
        $this->assertEquals(
            "SELECT `name`\n",
            (string) $this->connection->select(['name'])
        );

        echo "Simple select two columns\n";
        $this->assertEquals(
            "SELECT `name`, `password`\n",
            (string) $this->connection->select(['name', 'password'])
        );
    }

    public function testSelectPathedColumns()
    {
        echo "Select one pathed column\n";
        $this->assertEquals(
            "SELECT `user`.`name`\n",
            (string) $this->connection->select(['user.name'])
        );

        echo "Select two pathed columns\n";
        $this->assertEquals(
            "SELECT `user`.`name`, `user`.`password`\n",
            (string) $this->connection->select(['user.name', 'user.password'])
        );
    }

    public function testSelectExpression()
    {
        echo "Select one simple expression\n";
        $this->assertEquals(
            "SELECT 1+1\n",
            (string) $this->connection->select([new Expression('1+1')])
        );

        echo "Select multiple expressions\n";
        $this->assertEquals(
            "SELECT 1+1, LOG(43) + `b`\n",
            (string) $this->connection->select([
                new Expression('1+1'),
                new Expression('LOG(43) + `b`'),
            ])
        );

        echo "Select mixed expressions and columns\n";
        $this->assertEquals(
            "SELECT `name`, `size` / 100 * `productivity`\n",
            (string) $this->connection->select([
                'name',
                new Expression('`size` / 100 * `productivity`')
            ])
        );
    }

    public function tearDown()
    {
        $this->connection = null;
    }
}
