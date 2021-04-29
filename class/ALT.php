<?php

use ALT\RTable;

class ALT extends PUXT\Context
{
    public function __construct(PUXT\Context $context)
    {
        foreach ($context as $k => $v) {
            $this->$k = $v;
        }
    }

    public function createTab()
    {
        return new \App\UI\Tab();
    }

    public function createRTable()
    {
        $rt = new RTable();
        return $rt;
    }

    public function isLogined()
    {
        return true;
    }

    public function object()
    {
        //$this->params->id
        //        $this->route
    }
}
