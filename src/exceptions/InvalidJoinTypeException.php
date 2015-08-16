<?php
/**
 * Created by PhpStorm.
 * User: martin
 * Date: 16.8.2015
 * Time: 11:46
 */

namespace queryBuilder\exceptions;


class InvalidJoinTypeException extends \Exception {
    public function __construct($joinType = null, $message = null, $code = 0, \Exception $previous = null)
    {
        if (is_null($message) && !is_null($joinType)) {
            $message = "Invalid join type $joinType\n";
        }

        parent::__construct($message, $code, $previous);
    }
}