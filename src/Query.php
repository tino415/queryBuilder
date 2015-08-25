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

    public function select($columns)
    {
        if (func_num_args() > 1 || !is_array($columns)) {
            $columns = func_get_args();
        }

        $this->select[] = $this->quote->aliasedColumns($columns);
        return $this;
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

    public function groupBy($columns)
    {
        if (func_num_args() > 1 || !is_array($columns)) {
            $columns = func_get_args();
        }

        $this->groupBy[] = $this->quote->columns($columns);
        return $this;
    }

    public function orderBy($column, $type = 'ASC')
    {
        $this->orderBy[] = ((is_array($column)) ? $this->quote->columns($column) : $this->quote->name($column)) . ' ' . $this->quote->orderType($type);
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
        $this->set[] = $this->quote->name($column) . ' = ' . $this->quote->value($value);
        return $this;
    }

    public function offset($offset)
    {
        $this->offset = (integer) $offset;
        return $this;
    }

    public function __toString()
    {
        $query = 'SELECT ' . ((empty($this->select)) ? '*' : implode(', ', $this->select) . "\n");
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
}