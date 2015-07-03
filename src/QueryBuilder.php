<?php

namespace queryBuilder;

class QueryBuilder
{
    private $connection;

    private $likePattern = '/[^\\\\]?%/';

    private $buildTable = [
        '=' => [
            'Number' => ['=', 'toString'],
            'Array' => ['IN', 'arrayOfValues'],
            'Column' => ['=', 'escapeColumn'],
            'Expression' => ['=', 'toString'],
            'String' => ['=', 'valueQuote'],
            'Null' => ['IS', 'returnNull'],
        ],

        '>=' => [
            'Number' => ['>=', 'toString'],
            'Column' => ['>=', 'escapeColumn'],
            'Expression' => ['>=', 'toString'],
        ],

        '<=' => [
            'Column' => ['<=', 'escapeColumn'],
            'Expression' => ['<=', 'toString'],
            'Number' => ['<=', 'toString'],
        ],

        '!' => [
            'Number' => ['<>', 'toString'],
            'Array' => ['NOT IN', 'arrayOfValues'],
            'Column' => ['<>', 'escapeColumn'],
            'Expression' => ['<>', 'toString'],
            'String' => ['<>', 'valueQuote'],
            'Null' => ['IS NOT', 'returnNull'],
        ],

        '%' => [
            'TwoArray' => ['LIKE', 'likeArrayFormat'],
            'Array' => ['LIKE', 'likeArrayFormat'],
            'Column' => ['LIKE', 'escapeColumn'],
            'Expression' => ['LIKE', 'toString'],
            'LikeString' => ['LIKE', 'likeStringFormat']
        ],

        '!%' => [
            'TwoArray' => ['NOT LIKE', 'likeArrayFormat'],
            'Array' => ['NOT LIKE', 'likeArrayFormat'],
            'Column' => ['NOT LIKE', 'escapeColumn'],
            'Expression' => ['NOT LIKE', 'toString'],
            'LikeString' => ['NOT LIKE', 'likeStringFormat'],
        ],

        '_' => [
            'TwoArray' => ['BETWEEN', 'arrayOfValues'],
        ],

        '!_' => [
            'TwoArray' => ['NOT BETWEEN', 'arrayOfValues'],
        ],
    ];

    public function __construct(\PDO $connection)
    {
        $this->connection = $connection;
    }

    private function isColumn($value)
    {
        return !is_array($value) && settype($value, 'string') && $value[0] == '@';
    }

    private function isString($value)
    {
        return !is_array($value) && settype($value, 'string');
    }

    private function isExpression($value)
    {
        return $value instanceof Expression;
    }

    private function isNumber($value)
    {
        return is_int($value) || is_float($value) || is_bool($value);
    }

    private function isArray($value)
    {
        return is_array($value);
    }

    private function isTwoArray($value)
    {
        return is_array($value) && count($value) == 2;
    }

    private function isNull($value)
    {
        return is_null($value);
    }

    private function isLikeString($value)
    {
        return settype($value, 'string') && preg_match($this->likePattern);
    }

    private function isOr($value)
    {
        return 
            is_array($value) && 
            isSet($value[0]) && 
            is_string($value[0]) && 
            $value[0] == 'OR';
    }

    private function toString($value)
    {
        return (string) $value;
    }


    private function columnQuote($column)
    {
        if ($column instanceof Expression) {
            return $column;
        }

        $column = explode('.', $column);
        return '`' . implode('`.`', $column) . '`';
    }

    private function escapeColumn($column)
    {
        $column = substr($column, 1);
        return $this->columnQuote($column);
    }

    private function columnsQuote(array $columns)
    {
        foreach($columns as $key => $column) {
            $columns[$key] = $this->columnQuote($column);
        }

        return $columns;
    }

    private function escapeColumnArray(array $columns)
    {
        return implode(', ', $this->columnsQuote($columns));
    }

    private function valueQuote($value, $quote = true)
    {
        return $this->connection->quote($value, $quote);
    }

    private function valuesQuote(array $values, $quote = true)
    {
        foreach ($values as $key => $value) {
            $values[$key] = $this->valueQuote($value, $quote);
        }

        return $values;
    }

    private function returnNull($value)
    {
        return null;
    }

    private function parseValue($value, $acceptedTypes)
    {
        foreach ($acceptedValues as $checker => $parser) {
            if ($this->{'is' . $checker}($value)) {
                return $this->{$parser}($value);
            }
        }
    }

    private function arrayOfValues(array $values)
    {
        $items = [];

        foreach($values as $value) {
            if ($this->isExpression($value)) {
                $items[] = $this->toString($value);
            } elseif($this->isNumber($value)) {
                $items[] = $this->toString($value);
            } elseif($this->isColumn($value)) {
                $items[] = $this->columnQuote($value);
            } elseif($this->isString($value)) {
                $items[] = $this->valueQuote($value);
            } else {
                //TODO throw exception
            }
        }

        return '(' . implode(', ', $items) . ')';
    }

    private function likeArrayFormat(array $values)
    {
        foreach($values as $key => $value) {
            $values[$key] = substr($this->valueQuote($value), 1, -1);
        }
        return "'" . implode('%', $values) . "'";
    }

    private function likeStringFormat($value)
    {
        $parts = preg_split($this->likePattern, $value);
        return $this->likeArrayFormat($parts);
    }

    private function getOperator($column)
    {
        preg_match('/(>=|<=|!_|!%|!|%|_|>|<)$/', $column, $matches);
        if (isSet($matches[1])) {
            $operator = $matches[1];
            $column = substr($column, 0, -1 * strlen($matches[1]));
        } else {
            $operator = '=';
        }

        return [$operator, $column];
    }

    private function logicArray(array $conditions)
    {
        $operator = $this->isOr($conditions) ? 'OR' : 'AND';
        if(isSet($conditions[0]) && is_string($conditions[0])) {
            unset($conditions[0]);
        }
        $conditions = $this->buildArrayOfConditions($conditions);
        return '(' . implode(" $operator ", $conditions) . ')';
    }

    private function buildArrayOfConditions(array $conditions)
    {
        $parsedConditions = [];
        foreach($conditions as $column => $condition) {
            if (is_int($column)) {
                $parsedConditions[] = $this->logicArray($condition);
            } else {
                $parsedConditions[] = $this->buildCondition($column, $condition);
            }
        }

        return $parsedConditions;
    }

    private function buildCondition($column, $value)
    {
        list($operation, $column) = $this->getOperator($column);
        list($value, $operator) = $this->buildValue($operation, $value);
        $column = $this->columnQuote($column);
        return "$column $operator $value";
    }

    private function buildValue($operation, $value)
    {
        foreach($this->buildTable[$operation] as $checker => $parsing) {
            if ($this->{'is' . $checker}($value)) {
                return [$this->{$parsing[1]}($value), $parsing[0]];
            }
        }
        //TODO throw error
    }

    public function parseSelect(Select $select) {
        $columns = $this->escapeColumnArray($select->colls);

        if(empty($columns)) $columns = '*';
        $result = "SELECT $columns\n";

        if (isSet($select->from)) {

            if (is_array($select->from)) {
                $from = $select->from[0];
                $alias = $this->columnQuote($select->from[1]);
            } else {
                $from = $select->from;
            }

            if ($from instanceof Select) {
                $from = "($from)";
            } elseif (is_string($from)) {
                $from = $this->columnQuote($from);
            } elseif (!$from instanceof Expression) {
                //TODO throw exception
            }

            if (isSet($alias)) {
                $result .= "FROM $from AS $alias\n";
            } else {
                $result .= "FROM $from\n";
            }
        }

        foreach ($select->joins as $join) {
            list($type, $table, $condition) = $join;
            $table = $this->columnQuote($table);
            $condition = $this->logicArray($condition);
            $result .= "$type JOIN $table ON $condition\n";
        }

        if (isSet($select->where)) {
            $where = $this->logicArray($select->where);
            $result .= "WHERE $where\n";
        }

        if (isSet($select->groupBy[0])) {
            $colls = $this->escapeColumnArray($select->groupBy);
            $result .= "GROUP BY $colls\n";
        }

        if (isSet($select->orderBy[0])) {
            $colls = $this->escapeColumnArray($select->orderBy);
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
            $having = $this->logicArray($select->having);
            $result .= "HAVING $having\n";
        }

        return $result;
    }
}
