<?php


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
        return "tab";
    }

    public function createRTable()
    {
        return "rTable";
    }
}
