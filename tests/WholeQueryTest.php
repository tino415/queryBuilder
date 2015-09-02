<?php namespace tests;

use queryBuilder\Query;
use queryBuilder\QueryFactory;
use queryBuilder\Quote;

class WholeQueryTest extends \PHPUnit_framework_TestCase
{
    private $pdo = false;

    public function getConnection()
    {
        if (!$this->pdo) {
            $this->pdo = new \PDO(
                Config::$config['db']['dsn'],
                Config::$config['db']['username'],
                Config::$config['db']['password']
            );
        }

        return $this->pdo;
    }

    public function testGenerating()
    {
        $quote = new Quote($this->getConnection());

        $factory = new QueryFactory($quote);

        $query = $factory->query()
            ->select('us.name')
            ->select('us.password')
            ->from('user', 'us')
            ->join('INNER', 'role', 'us.role_id', 'rl.id', 'rl')
            ->where($factory->criteria()
                ->compare('active', '=', 1)
                ->compare('rl.name', '=', 'admin')
                ->in('us.name', ['jozef', 'fero', 'jano', 'tino', 'martin', '32'])
            )
            ->orderBy('role_id', 'ASC');

        $this->assertEquals(
            "SELECT `us`.`name`, `us`.`password`\n" .
            "FROM `user` AS `us`\n" .
            "INNER JOIN `role` AS `rl` ON `us`.`role_id` = `rl`.`id`\n" .
            "WHERE `active` = 1 AND `rl`.`name` = 'admin' AND `us`.`name` IN ('jozef', 'fero', 'jano', 'tino', 'martin', '32')\n" .
            "ORDER BY `role_id` ASC\n",
            (string)$query
        );
    }

    public function testUpdate()
    {
        $quote = new Quote($this->getConnection());

        $factory = new QueryFactory($quote);

        $query = $factory->query()
            ->set('name', 'martin')
            ->set('password', '338')
            ->from('user')
            ->where($factory->criteria()
                ->compare('name', '=', 'tino')
            );

        $this->assertEquals(
            "UPDATE `user`\n" .
            "SET `name` = 'martin', `password` = '338'\n" .
            "WHERE `name` = 'tino'\n",
            $query->update()
        );
    }

    public function testInsert()
    {
        $quote = new Quote($this->getConnection());

        $factory = new QueryFactory($quote);

        $query = $factory->query()
            ->set('name', 'tino415')
            ->set('password', '1234')
            ->set('role_id', 1)
            ->from('user');

        $this->assertEquals(
            "INSERT INTO `user` (`name`, `password`, `role_id`)\n" .
            "VALUES ('tino415', '1234', 1)\n",
            $query->insert()
        );
    }
}