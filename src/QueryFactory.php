<?php
/**
 * Created by PhpStorm.
 * User: martin
 * Date: 16.8.2015
 * Time: 12:47
 */

namespace queryBuilder;


class QueryFactory
{
    private $quote;

    public function __construct(Quote $quote)
    {
        $this->quote = $quote;
    }

    public function query()
    {
        return new Query($this->quote);
    }

    public function criteria()
    {
        return new Criteria($this->quote);
    }
}