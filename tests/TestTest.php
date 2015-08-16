<?php namespace tests;

use queryBuilder\Criteria;
use queryBuilder\Query;
use queryBuilder\QueryFactory;
use queryBuilder\Quote;

class BasicTest extends \PHPUnit_framework_TestCase
{
    public function testTest()
    {
        $pdo = new \PDO(
            Config::$config['db']['dsn'],
            Config::$config['db']['username'],
            Config::$config['db']['password']
        );

        $quote = new Quote($pdo);

        $factory = new QueryFactory($quote);

        $query = $factory->query()
            ->select('name', 'password')
            ->from('user', 'us')
            ->join('INNER', 'role', 'us.role_id', 'rl.id', 'rl')
            ->where($factory->criteria()
                ->binary('active', '=', 1)
                ->binary('rl.type', '=', 'admin')
                ->in('us.name', ['jozef', 'fero', 'jano', 'tino', 'martin', '32'])
            )
            ->orderBy(['accessLevel', 'name'], 'ASC');

        echo $query;
    }
}