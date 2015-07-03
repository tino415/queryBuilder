<?php

namespace queryBuilder;

class Select extends Expression
{
    private $colls;
    private $from;
    private $where;
    private $connection;
    private $joins = [];
    private $groupBy = [];
    private $orderBy = [];
    private $limit;
    private $offset;
    private $having;

    public function addSelectedColls(array $colls)
    {
        $this->colls = array_merge($this->colls, $colls);
        return $this;
    }

    public function addSelectedColl($coll)
    {
        $this->colls[] = $coll;
        return $this;
    }

    public function from($from, $alias = false)
    {
        if (!$alias) {
            $this->from = $from;
        } else {
            $this->from = [$from, $alias];
        }

        return $this;
    }

    public function innerJoin($table, $condition)
    {
        $this->joins[] = ['INNER', $table, $condition];
        return $this;
    }

    public function leftJoin()
    {
        $this->joins[] = ['LEFT', $table, $condition];
        return $this;
    }

    public function rightJoin()
    {
        $this->joins[] = ['RIGHT', $table, $condition];
        return $this;
    }

    public function where($where)
    {
        if (!isset($this->where)) {
            $this->where = $where;
        } else {
            $this->where[] = $where;
        }
        return $this;
    }

    public function andWhere($where)
    {
        if (!isset($this->where)) {
            $this->where = $where;
        } elseif (isset($this->where[0]) && $this->where[0] == 'OR') {
            $this->where = [$this->where, $where];
        } else {
            $this->where[] = $where;
        }
        return $this;
    }

    public function orWhere($where)
    {
        if (!isset($this->where)) {
            $this->where = $where;
        } elseif (isset($this->where[0]) && $this->where[0] == 'OR') {
            $this->where[] = $where;
        } else {
            $this->where = ['OR', $this->where, $where];
        }
        return $this;
    }

    public function qroupBy($colls)
    {
        $this->groupBy = $colls;
        return $this;
    }

    public function addCollToGroupBy($coll)
    {
        $this->groupBy[] = $coll;
        return $this;
    }

    public function addCollsToGroupBy(array $colls)
    {
        $this->groupBy = array_merge($this->groupBy, $colls);
        return $this;
    }

    public function orderBy($colls)
    {
        $this->orderBy = $colls;
        return $this;
    }

    public function addCollToOrderBy($coll)
    {
        $this->orderBy[] = $coll;
        return $this;
    }

    public function addCollsToOrderBy(array $colls)
    {
        $this->orderBy = array_merge($this->orderBy, $colls);
        return $this;
    }

    public function limit($limit)
    {
        $this->limit = $limit;
        return $this;
    }

    public function offset($offset)
    {
        $this->offset = $offset;
        return $this;
    }

    public function having($having)
    {
        if (!isset($this->having)) {
            $this->having = $having;
        } else {
            $this->having[] = $having;
        }
        return $this;
    }

    public function andHaving($having)
    {
        if (!isset($this->having)) {
            $this->having = $having;
        } elseif (isset($this->having[0]) && $this->having[0] == 'OR') {
            $this->having = [$this->having, $having];
        } else {
            $this->having[] = $having;
        }
        return $this;
    }

    public function orHaving($having)
    {
        if (!isset($this->having)) {
            $this->having = $having;
        } elseif (isset($this->having[0]) && $this->having[0] == 'OR') {
            $this->having[] = $having;
        } else {
            $this->having = ['OR', $this->having, $having];
        }
        return $this;
    }

    public function __construct(array $colls = [], Connection $connection)
    {
        $this->colls = $colls;
        $this->connection = $connection;
    }

    public function __toString()
    {
        return $this->connection->queryParser->parseSelect($this);
    }

    public function __get($name)
    {
        return $this->{$name};
    }

    public function __isSet($name)
    {
        return isSet($this->{$name});
    }
}
