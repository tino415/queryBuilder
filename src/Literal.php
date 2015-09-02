<?php
/**
 * Created by PhpStorm.
 * User: martin
 * Date: 3.9.2015
 * Time: 1:26
 */

namespace queryBuilder;


class Literal
{
    private $value;

    public function __construct($value)
    {
        $this->value = $value;
    }

    public function __toString()
    {
        return (string) $this->value;
    }
}