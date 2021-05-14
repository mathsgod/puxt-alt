<?php

use ALT\RTable;
use App\App;
use App\UI\RTableResponse;
use App\User;

class ALT extends PUXT\Context
{
    public $module;
    public $user; //current logined user
    public function __construct(PUXT\Context $context)
    {
        foreach ($context as $k => $v) {
            $this->$k = $v;
        }

        $path = explode("/", $this->route->path, 2);

        if ($path[0]) {
            $this->module = $this->getModule($path[0]);
        }
        \App\Core\Model::$_db = $context->db;
        $this->user = new User(1);
    }

    public function getModule(string $name)
    {
        return ["name" => $name];
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

    public function createRTableResponse()
    {
        return new RTableResponse($this, $this->query);
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
