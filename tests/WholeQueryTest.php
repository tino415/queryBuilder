<?php namespace tests;

use queryBuilder\QueryFactory;
use queryBuilder\Quote;

class WholeQueryTest extends \PHPUnit_framework_TestCase
{
    public function testGenerating()
    {
        $pdo = new \PDO(
            Config::$config['db']['dsn'],
            Config::$config['db']['username'],
            Config::$config['db']['password']
        );

        $quote = new Quote($pdo);

        $factory = new QueryFactory($quote);

        $query = $factory->query()
            ->select('us.name', 'us.password')
            ->from('user', 'us')
            ->join('INNER', 'role', 'us.role_id', 'rl.id', 'rl')
            ->where($factory->criteria()
                ->compare('active', '=', 1)
                ->compare('rl.name', '=', 'admin')
                ->in('us.name', ['jozef', 'fero', 'jano', 'tino', 'martin', '32'])
            )
            ->orderBy(['role_id', 'name'], 'ASC');

        $this->assertEquals(
            "SELECT `us`.`name`, `us`.`password`\n" .
            "FROM `user` AS `us`\n" .
            "INNER JOIN `role` AS `rl` ON `us`.`role_id` = `rl`.`id`\n" .
            "WHERE `active` = 1 AND `rl`.`name` = 'admin' AND `us`.`name` IN ('jozef', 'fero', 'jano', 'tino', 'martin', '32')\n" .
            "ORDER BY `role_id`, `name` ASC\n",
            (string)$query
        );
    }
}