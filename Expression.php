<?php

namespace tino\queryBuilder;

class Expression
{
    public $content;

    public function __construct(array $content)
    {
        $this->content = $content;
    }

    public function __toString()
    {
        return implode(' ', $this->content);
    }
}
