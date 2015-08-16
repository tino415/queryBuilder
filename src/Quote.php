<?php namespace queryBuilder;

use queryBuilder\exceptions\InvalidOperatorException;
use queryBuilder\exceptions\InvalidJoinTypeException;
use queryBuilder\exceptions\InvalidOrderTypeException;

class Quote
{
    /** @var  \PDO */
    protected $pdo;

    /** @var string */
    protected $columnQuote = '`';

    /** @var array */
    protected $operators = [
        '=', '>=', '<=', '>', '<', '!=',
    ];

    /** @var array */
    protected $joinTypes = [
        'INNER', 'LEFT', 'RIGHT',
    ];

    /** @var array */
    protected $orderTypes = [
        'ASC', 'DESC',
    ];

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function name($column)
    {
        if ($column instanceof Literal) {
            return $column;
        }

        $parts = explode('.', $column);

        foreach($parts as $index => $name) {
            $parts[$index] = substr($this->pdo->quote($name), 1, -1);
        }

        return $this->columnQuote . implode("$this->columnQuote.$this->columnQuote", $parts) . $this->columnQuote;
    }

    public function operator($operator)
    {
        if (!$operator instanceof Literal && !in_array($operator, $this->operators)) {
            throw new InvalidOperatorException;
        }

        return $operator;
    }

    public function value($value)
    {
        if ($value instanceof Literal || is_int($value) || is_float($value)) {
            return $value;
        }
        return $this->pdo->quote($value);
    }

    public function likeValue($value)
    {
        if ($value instanceof Literal) {
            return $value;
        }

        foreach($value as $index => $part) {
            $value[$index] = $this->pdo->quote($part);
        }

        return implode('%', $value);
    }

    public function values($values) {
        if ( $values instanceof Literal) {
            return $values;
        }

        foreach($values as $index => $value) {
            $values[$index] = $this->value($value);
        }

        return '(' . implode(', ', $values) . ')';
    }

    public function columns($columns)
    {
        if ($columns instanceof Literal) {
            return $columns;
        }

        foreach($columns as $index => $column) {
            $columns[$index] = $this->name($column);
        }

        return implode(', ', $columns);
    }

    public function aliasedColumns($columns)
    {
        if ($columns instanceof Literal) {
            return $columns;
        }

        $escaped = [];
        foreach($columns as $index => $column) {
            if (is_int($index)) {
                $escaped[] = $this->name($column);
            } else {
                $escaped[] = $this->name($column) . ' AS ' . $this->name($index);
            }
        }

        return implode(', ' , $escaped);
    }

    public function joinType($type)
    {
        if ($type instanceof Literal) {
            return $type;
        }

        $type = strtoupper($type);
        if (!in_array($type, $this->joinTypes)) {
            throw new InvalidJoinTypeException;
        }

        return $type;
    }

    public function orderType($type)
    {
        if ($type instanceof Literal) {
            return $type;
        }

        $type = strtoupper($type);
        if (!in_array($type, $this->orderTypes)) {
            throw new InvalidOrderTypeException;
        }

        return $type;
    }

    public function criteria($criteria)
    {
        if ($criteria instanceof Literal) {
            return $criteria;
        }

        return $criteria;
    }
}