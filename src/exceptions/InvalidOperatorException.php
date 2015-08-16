<?php
/**
 * Created by PhpStorm.
 * User: martin
 * Date: 16.8.2015
 * Time: 9:28
 */

namespace queryBuilder\exceptions;


class InvalidOperatorException extends \Exception
{

    public function __construct($operator = null, $message = null, $code = 0, \Exception $previous = null)
    {
        if (is_null($message) && !is_null($operator)) {
            $message = "Invalid operator $operator in sql criteria builder\n";
        }

        parent::__construct($message, $code, $previous);
    }
}