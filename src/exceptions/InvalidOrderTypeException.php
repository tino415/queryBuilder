<?php
/**
 * Created by PhpStorm.
 * User: martin
 * Date: 16.8.2015
 * Time: 9:28
 */

namespace queryBuilder\exceptions;


class InvalidOrderTypeException extends \Exception
{

    public function __construct($orderType = null, $message = null, $code = 0, \Exception $previous = null)
    {
        if (is_null($message) && !is_null($orderType)) {
            $message = "Invalid order type $orderType\n";
        }

        parent::__construct($message, $code, $previous);
    }
}