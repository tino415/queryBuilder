<?php

namespace tests;

use queryBuilder\Connection;

class ConnectionTest extends \PHPUnit_Framework_TestCase
{
    
    private $connection;

    public function setUp() {
        $this->connection = new Connection(
            Config::$config['db']['dsn'],
            Config::$config['db']['username'],
            Config::$config['db']['password'],
            'queryBuilder\QueryBuilder'
        );
    }

    public function testWhereEquals()
    {
        $this->assertEquals(
            "SELECT `name`, `password`\nFROM `user`\n".
            "WHERE (`name` = 'tomáš')\n",
            (string) $this->connection->select(['name', 'password'])
                ->from('user')
                ->where(['name' => 'tomáš'])
        );

        $this->assertEquals(
            "SELECT `id`, `lang`\nFROM `locale`\n".
            "WHERE (`id` = 3)\n",
            (string) $this->connection->select(['id', 'lang'])
                ->from('locale')
                ->where(['id' => 3])
        );
    }

    public function testWhereIn()
    {
        $this->assertEquals(
            "SELECT `name`, `password`\nFROM `user`\n".
            "WHERE (`name` IN ('tomáš', 'miro', 'jano'))\n",
            (string) $this->connection->select(['name', 'password'])
                ->from('user')
                ->where(['name' => ['tomáš', 'miro', 'jano']])
        );

        $this->assertEquals(
            "SELECT `name`, `password`\nFROM `user`\n".
            "WHERE (`name` NOT IN ('tomáš', 'miro', 'jano'))\n",
            (string) $this->connection->select(['name', 'password'])
                ->from('user')
                ->where(['name!' => ['tomáš', 'miro', 'jano']])
        );
    }

    public function testWhereLike()
    {
        $this->assertEquals(
            "SELECT `name`, `password`\nFROM `user`\n".
            "WHERE (`name` LIKE '%tomáš%miro%jano')\n",
            (string) $this->connection->select(['name', 'password'])
                ->from('user')
                ->where(['name%' => ['', 'tomáš', 'miro', 'jano']])
        );

        $this->assertEquals(
            "SELECT `name`, `password`\nFROM `user`\n".
            "WHERE (`name` NOT LIKE '%tomáš%miro%jano')\n",
            (string) $this->connection->select(['name', 'password'])
                ->from('user')
                ->where(['name!%' => ['', 'tomáš', 'miro', 'jano']])
        );
    }

    public function testWhereBetween()
    {
        $this->assertEquals(
            "SELECT `id`, `lang`\nFROM `locale`\n".
            "WHERE (`id` BETWEEN (1, 5))\n",
            (string) $this->connection->select(['id', 'lang'])
                ->from('locale')
                ->where(['id_' => [1, 5]])
        );

        $this->assertEquals(
            "SELECT `id`, `lang`\nFROM `locale`\n".
            "WHERE (`id` NOT BETWEEN (1, 5))\n",
            (string) $this->connection->select(['id', 'lang'])
                ->from('locale')
                ->where(['id!_' => [1, 5]])
        );
    }

    public function testWhereAndOr()
    {
        $this->assertEquals(
            "SELECT `id`, `lang`\nFROM `locale`\n".
            "WHERE (`id` BETWEEN (1, 5) AND `lang` = 'en')\n",
            (string) $this->connection->select(['id', 'lang'])
                ->from('locale')
                ->where(['id_' => [1, 5], 'lang' => 'en'])
        );

        $this->assertEquals(
            "SELECT `id`, `lang`\nFROM `locale`\n".
            "WHERE ((`id` BETWEEN (1, 5) AND `lang` = 'en') OR `lang` = 'cz')\n",
            (string) $this->connection->select(['id', 'lang'])
                ->from('locale')
                ->where(['OR', ['id_' => [1, 5], 'lang' => 'en'], 'lang' => 'cz'])
        );

        $this->assertEquals(
            "SELECT `id`, `lang`\nFROM `locale`\n".
            "WHERE ((`id` BETWEEN (1, 5) OR `lang` = 'en') AND (`lang` = 'cz'))\n",
            (string) $this->connection->select(['id', 'lang'])
                ->from('locale')
                ->where(['OR', 'id_' => [1, 5], 'lang' => 'en'])
                ->andWhere(['lang' => 'cz'])
        );

        $this->assertEquals(
            "SELECT `id`, `lang`\nFROM `locale`\n" .
            "WHERE (((`id` BETWEEN (1, 5) OR `lang` = 'en') " .
            "AND (`lang` = 'cz')) OR (`id` = 6))\n",
            (string) $this->connection->select(['id', 'lang'])
                ->from('locale')
                ->where(['OR', 'id_' => [1, 5], 'lang' => 'en'])
                ->andWhere(['lang' => 'cz'])
                ->orWhere(['id' => 6])
        );
    }

    public function testJoins()
    {
        $this->assertEquals(
            "SELECT `id`, `lang`\n" .
            "FROM `locale`\n" .
            "INNER JOIN `translation` ON (`translation`.`lang` = `locale`.`lang`)\n" .
            "WHERE (`id` BETWEEN (1, 5) OR `lang` = 'en')\n",
            (string) $this->connection->select(['id', 'lang'])
                ->from('locale')
                ->innerJoin('translation', ['translation.lang' => '@locale.lang'])
                ->where(['OR', 'id_' => [1, 5], 'lang' => 'en'])
        );
    }

    public function tearDown()
    {
        $this->connection = null;
    }
}
