<?php

namespace tino\queryBuilder;

class QueryModel
{
    protected $select;
    protected $from;
    protected $where;
    protected $orderBy;
    protected $limit;
    protected $offset;
    protected $join;

    public function __construct()
    {
        $this->select = new Select();
        $this->from = new From();
        $this->join = new Join();
        $this->where = new Where();
        $this->orderBy = new OrderBy();
        $this->limit = new Limit();
        $this->offset = new Offset();
    }

    public function __toString()
    {
        $result = "SELECT $this->select";
        $result .= $this->from;
        $result .= $this->join;
        $result .= $this->where;
        $result .= $this->orderBy;
        $result .= $this->limit;
        $result .= $this->offset;
        $result .= $this->having;
    }
}
