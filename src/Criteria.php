<?php
/**
 * Created by PhpStorm.
 * User: martin
 * Date: 16.8.2015
 * Time: 9:17
 */

namespace queryBuilder;


class Criteria
{
    /** @var  \queryBuilder\Quote */
    protected $quote;

    /** @var array */
    protected $criteria = [];

    protected $operators = [
        '=', '<=', '>=', '>', '<',
    ];

    public function __construct(Quote $quote)
    {
        $this->quote = $quote;
    }

    public function binary($column, $operator, $value)
    {
        $column = $this->quote->name($column);
        $operator = $this->quote->operator($operator);
        $value = $this->quote->value($value);

        $this->criteria[] = "$column $operator $value";
        return $this;
    }

    public function like($column, $value)
    {
        $column = $this->quote->name($column);
        $value = $this->quote->likeValue($value);

        $this->criteria[] = "$column LIKE $value";
        return $this;
    }

    public function notLike($column, $value)
    {
        $column = $this->quote->name($column);
        $value = $this->quote->likeValue($value);

        $this->criteria[] = "$column NOT LIKE $value";
        return $this;
    }

    public function between($column, $from, $to)
    {
        $column = $this->quote->name($column);
        $from = $this->quote->value($from);
        $to = $this->quote->value($to);

        $this->criteria[] = "$column BETWEEN $from AND $to";
        return $this;
    }

    public function notBetween($column, $from, $to)
    {
        $column = $this->quote->name($column);
        $from = $this->quote->value($from);
        $to = $this->quote->value($to);

        $this->criteria[] = "$column NOT BETWEEN $from AND $to";
        return $this;
    }

    public function in($column, $values)
    {
        $column = $this->quote->name($column);
        $values = $this->quote->values($values);

        $this->criteria[] = "$column IN $values";
        return $this;
    }

    public function notIn($column, $values)
    {
        $column = $this->quote->name($column);
        $values = $this->quote->values($values);

        $this->criteria[] = "$column NOT IN $values";
        return $this;
    }

    public function __toString()
    {
        return implode(' AND ', $this->criteria);
    }
}