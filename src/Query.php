<?php namespace queryBuilder;

class Query
{
    /** @var  Quote */
    protected $quote;

    protected $select = [];
    protected $from = '';
    protected $joins = [];
    protected $where = [];
    protected $groupBy = [];
    protected $orderBy = [];
    protected $having = [];
    protected $limit = 0;
    protected $offset = 0;

    protected $set = [];

    public function __construct(Quote $quote)
    {
        $this->quote = $quote;
    }

    public function select($column, $alias = false)
    {
        $name = $this->quote->name($column);
        if ($alias) {
            $this->select[$this->quote->name($alias)] = $name;
        } else {
            $this->select[$name] = $name;
        }
        return $this;
    }

    public function buildSelect()
    {
        $select = $this->select;

        array_walk($select, function(&$item, $key) {
            $item = ($item == $key) ? $item : "$item AS $key";
        });

        return implode(', ', $select);
    }

    public function from($table, $alias = null)
    {
        $this->from = $this->quote->name($table);
        $this->from .= ($alias) ? ' AS ' . $this->quote->name($alias) : '';
        return $this;
    }

    public function join($type, $table, $referencer, $referred, $alias = null)
    {
        $join = $this->quote->joinType($type) . ' JOIN';
        $join .= ' ' . $this->quote->name($table);
        $join .= ($alias) ? ' AS ' . $this->quote->name($alias) : '';
        $join .= ' ON ' . $this->quote->name($referencer) . ' = ' . $this->quote->name($referred);

        $this->joins[] = $join;
        return $this;
    }

    public function where($criteria) {
        $this->where[] = $this->quote->criteria($criteria);
        return $this;
    }

    public function groupBy($column)
    {
        $this->groupBy[] = $this->quote->name($column);
        return $this;
    }

    public function orderBy($column, $type = 'ASC')
    {
        $this->orderBy[] = $this->quote->name($column) . " $type";
        return $this;
    }

    public function having($criteria)
    {
        $this->having[] = $this->quote->criteria($criteria);
        return $this;
    }

    public function limit($limit)
    {
        $this->limit = (integer) $limit;
        return $this;
    }

    public function set($column, $value)
    {
        $this->set[$this->quote->name($column)] = $this->quote->value($value);
        return $this;
    }

    public function buildUpdateSet()
    {
        $set = $this->set;
        array_walk($set, function(&$item, $key) {$item = "$key = $item";});
        return implode(', ', $set);
    }

    public function buildInsertColumns()
    {
        $columns = array_keys($this->set);
        return implode(', ', $columns);
    }

    public function offset($offset)
    {
        $this->offset = (integer) $offset;
        return $this;
    }

    public function __toString()
    {
        $query = 'SELECT ' . ((empty($this->select)) ? '*' : $this->buildSelect() . "\n");
        $query .= (empty($this->from)) ? '' : "FROM $this->from\n";
        $query .= (empty($this->joins)) ? '' : implode("\n", $this->joins) . "\n";
        $query .= (empty($this->where)) ? '' : 'WHERE ' . implode(' AND ', $this->where) . "\n";
        $query .= (empty($this->groupBy)) ? '' : 'GROUP BY ' . implode(', ', $this->groupBy) . "\n";
        $query .= (empty($this->orderBy)) ? '' : 'ORDER BY ' . implode(', ', $this->orderBy) . "\n";
        $query .= (empty($this->having)) ? '' : 'HAVING ' . implode(' AND ', $this->having) . "\n";
        $query .= (!$this->limit) ? '' : 'LIMIT ' . $this->limit . "\n";
        $query .= (!$this->offset) ? '' : 'OFFSET ' . $this->offset . "\n";

        return $query;
    }

    public function update()
    {

        $query = "UPDATE $this->from\n";
        $query .= "SET " . $this->buildUpdateSet() . "\n";
        $query .= (empty($this->where)) ? '' : 'WHERE ' . implode(' AND ', $this->where) . "\n";

        return $query;
    }

    public function insert()
    {
        $query = "INSERT INTO $this->from " . '(' . $this->buildInsertColumns() .")\n";
        $query .= "VALUES (" . implode(', ', $this->set) . ")\n";

        return $query;
    }

    public function delete()
    {
        $query = "DELETE FROM $this->from\n";
        $query .= (empty($this->where)) ? '' : 'WHERE ' . implode(' AND ', $this->where) . "\n";

        return $query;
    }
}