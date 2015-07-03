<?php

namespace tino\queryBuilder;

class SQLCase extends Expression
{

    public $attribute;
    public $cases;

    public function __construct($attribute, array $cases)
    {
        $this->attribute = $attribute;
        $this->cases = $cases;
    }

    public function __toString()
    {
        $result = "CASE $this->attribute\n";
        foreach($this->cases as $when => $then) {
            $result .= "\tWHEN $when THEN $then\n";
        }
        $result .= "END\n";
        return $result;
    }
}
