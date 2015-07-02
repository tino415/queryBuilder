<?php

namespace tino\queryBuilder;

class ConditionParser
{
    public function __construct(\PDO $connection)
    {
        $this->connection = $connection;
    }

    public function parser(array $condition)
    {
        return $this->parseLogic($condition);
    }

    private function parseLogic(array $condition)
    {
        if(isset($condition[0])) {
            $operator = strtoupper(array_shift($condition));
        } else {
            $operator = 'AND';
        }

        $items = [];
        foreach($condition as $column => $value) {
            $items[] = $this->parseExpression($column, $value);
        }
        return '(' . implode(" $operator ", $items) . ')';
    }

    private function isNumber($value)
    {
        return (is_int($value) || is_float($value) || is_bool($value));
    }

    private function parseBetween($column, $value)
    {
        $modifier = substr($column, -1);
        $value = $this->parseArray($value);

        if ($modifier == '!') {
            $column = substr($column, 0, -1);
            return  "$column NOT BETWEEN $value";
        } else {
            return "$column BETWEEN $value";
        }
    }

    private function parseIn($column, $value)
    {
        $modifier = substr($column, -1);
        $value = $this->parseArray($value);

        if ($modifier == '!') {
            $column = substr($column, 0, -1);
            return "$column NOT IN $value";
        } else {
            return "$column IN $value";
        }

    }

    private function parseNumberEquals($column, $value)
    {
        $modifier = substr($column, -1);

        if ($modifier == '>' || $modifier == '<') {
            $column = substr($column, 0, -1);
            return "$column $modifier= $value";
        } else {
            throw new UnknownModifierException("$modifier= is not query builder modifier");
        }
    }

    private function parseLike($column, $value, $modifier)
    {
        $modifier = substr($column, -1);


        $escapedChunks = [];
        foreach ($value as $chunk) {
            $escapedChunks[] = $this->connection->quote($chunk);
        }

        $value = implode('%', $escapedChunks);

        if ($modifier == '!') {
            $column = substr($column, 0, -1);
            return "$column NOT LIKE $value";
        } else {
            return "$column LIKE $value";
        }
    }

    private function parseString($column, $value, $modifier)
    {
        $value = $this->connection->quote($value);

        if($modifier == '!') {
            $column = substr($column, 0, -1);
            return "$column <> $value";
        } else {
            return "$column = $value";
        }
    }

    private function parseExpression($column, $value)
    {
        $likeRegex = '/[^\\\\]*%/';

        $modifier = substr($column, -1);

        if (is_int($column)) return $this->parseLogic($value);

        if (is_null($value)) {
            if ($modifier == '!') {
                $column = substr($column, 0, -1);
                return "$column IS NOT NULL";
            } else {
                return "$column IS NULL";
            }
        }

        if ($this->isNumber($value)) {
            if ($modifier == '>' || $modifier == '<') {
                $column = substr($column, 0, -1);
                return "$column $modifier $value";
            } elseif ($modifier == '=') {
                $column = substr($column, 0, -1);
                return $this->parseNumberEquals($column, $value);
            } elseif ($modifier == '!') {
                $column = substr($column, 0, -1);
                return "$column <> $value";
            } else {
                return "$column = $value";
            }
        }

        if (is_array($value)) {
            if ($modifier == '_') {
                return $this->parseBetween(substr($column, 0, -1), $value);
            } elseif ($modifier == '%') {
                return $this->parseLike(substr($column, 0, -1), $value);
            } else {
                return $this->parseIn($column, $value);
            }
        }

        $value = (string) $value;

        if ($modifier == '!') {
            $column = substr($column, 0, -1);
            return "$column <> $value";
        } else {
            return "$column = $value";
        }
    }

    private function parseValue($value)
    {
        if($this->isNumber($value)) return $value;
        elseif(is_array($value)) $this->parseArray($value);
        else return $this->connection->quote($value);
    }

    private function parseArray($value)
    {
        $items = [];
        foreach($value as $val) {
            $items[] = $this->parseValue($val);
        }
        return '(' . implode(', ', $items) . ')';
    }
}
