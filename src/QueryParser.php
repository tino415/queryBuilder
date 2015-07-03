<?php

namespace queryBuilder;

class QueryParser
{
    public function __construct(\PDO $connection)
    {
        $this->connection = $connection;
    }

    public function parseSelect(Select $select)
    {
        $colls = $this->parseColls($select->colls);
        $result = "SELECT $colls\n";

        if (isSet($select->from)) {
            $from = $this->parseColl($select->from);
            $result .= "FROM $from\n";
        }

        foreach ($select->joins as $join) {
            list($type, $table, $condition) = $join;
            $table = $this->parseColl($table);
            $condition = $this->parseCondition($condition);
            $result .= "$type JOIN $table ON $condition\n";
        }

        if (isSet($select->where)) {
            $where = $this->parseCondition($select->where);
            $result .= "WHERE $where\n";
        }

        if (isSet($select->groupBy[0])) {
            $colls = $this->parseColls($select->groupBy);
            $result .= "GROUP BY $colls\n";
        }

        if (isSet($select->orderBy[0])) {
            $colls = $this->parseColls($select->orderBy);
            $result .= "ORDER BY $colls\n";
        }

        if (isSet($select->limit)) {
            if (isSet($select->offset)) {
                $result .= "LIMIT $select->offset, $select->limit\n";
            } else {
                $result .= "LIMIT $select->limit\n";
            }
        }

        if (isSet($select->having)) {
            $having = $this->parseCondition($select->having);
            $result .= "HAVING $having\n";
        }

        return $result;
    }

    private function parseCondition(array $condition)
    {
        return $this->parseLogic($condition);
    }

    private function parseColl($coll, $from = false, $to = false)
    {
        if ($coll instanceof Expression) return "($coll)";

        if ($from !== false) {
            if ($to !== false) {
                $coll = substr($coll, $from, $to);
            } else {
                $coll = substr($coll, $from);
            }
        } 

        $coll = (string) $coll;
        if (is_string($coll)) {
            $coll = explode('.', $coll);
            return '`' . implode('`.`', $coll) . '`';
        }
        //TODO Throw exception
    }

    private function parseColls($colls)
    {
        $parsed = [];
        foreach($colls as $coll) {
            $parsed[] = $this->parseColl($coll);
        }

        if(empty($parsed)) return '*';
        else return implode(', ', $parsed);
    }

    private function parseLogic(array $condition)
    {
        if(isset($condition[0]) && is_string($condition[0])) {
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
            $column = $this->parseColl($column, 0, -1);
            return  "$column NOT BETWEEN $value";
        } else {
            $column = $this->parseColl($column);
            return "$column BETWEEN $value";
        }
    }

    private function parseIn($column, $value)
    {
        $modifier = substr($column, -1);
        if (!$value instanceof Expresssion) {
            $value = $this->parseArray($value);
        }

        if ($modifier == '!') {
            $column = $this->parseColl(substr($column, 0, -1));
            return "$column NOT IN $value";
        } else {
            $column = $this->parseColl($column);
            return "$column IN $value";
        }

    }

    private function parseNumberEquals($column, $value)
    {
        $modifier = substr($column, -1);

        if ($modifier == '>' || $modifier == '<') {
            $column = substr($column, 0, -1);
            $column = $this->parseColl($column);
            return "$column $modifier= $value";
        } else {
            throw new UnknownModifierException("$modifier= is not query builder modifier");
        }
    }

    private function parseLike($column, $value)
    {
        $modifier = substr($column, -1);

        $escapedChunks = [];
        foreach ($value as $chunk) {
            $escapedChunks[] = substr($this->connection->quote($chunk), 1, -1);
        }

        $value = implode('%', $escapedChunks);

        if ($modifier == '!') {
            $column = $this->parseColl($column, 0, -1);
            return "$column NOT LIKE '$value'";
        } else {
            $column = $this->parseColl($column);
            return "$column LIKE '$value'";
        }
    }

    private function parseString($column, $value, $modifier)
    {
        $value = $this->connection->quote($value);

        if($modifier == '!') {
            $column = substr($column, 0, -1);
            return "`$column` <> $value";
        } else {
            return "`$column` = $value";
        }
    }

    private function isColumn($value)
    {
        return $value[0] == '@';
    }

    private function parseComparable($column, $parsedValue, $modifier)
    {
        if ($modifier == '>' || $modifier == '<') {
            $column = $this->parseColl($column, 0, -1);
            return "$column $modifier $parsedValue";
        } elseif ($modifier == '=') {
            $column = substr($column, 0, -1);
            return $this->parseNumberEquals($column, $parsedValue);
        } elseif ($modifier == '!') {
            $column = $this->parseColl($column, 0, -1);
            return "$column <> $parsedValue";
        } else {
            $column = $this->parseColl($column);
            return "$column = $parsedValue";
        }
    }

    private function parseExpression($column, $value)
    {
        $likeRegex = '/[^\\\\]*%/';

        $modifier = substr($column, -1);

        if (is_int($column)) return $this->parseLogic($value);

        if (is_null($value)) {
            if ($modifier == '!') {
                $column = $this->parseColl($column, 0, -1);
                return "$column IS NOT NULL";
            } else {
                $column = $this->parseColl($column);
                return "$column IS NULL";
            }
        }

        if ($this->isNumber($value)) {
            return $this->parseComparable($column, $value, $modifier);
        }

        if ($this->isColumn($value)) {
            $value = $this->parseColl($value, 1);
            return $this->parseComparable($column, $value, $modifier);
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

        if ($value instanceof Expression) {
            if ($modifier == '_') {
            }
        }

        $value = (string) $value;

        if ($modifier == '!') {
            $column = $this->parseColl($column, 0, -1);
            return "$column <> '$value'";
        } else {
            $column = $this->parseColl($column);
            return "$column = '$value'";
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
        return '(' . implode(', ', $items) . ')';
    }
}
