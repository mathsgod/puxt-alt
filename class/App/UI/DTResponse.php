<?php
namespace App\UI;

use JsonSerializable;
use Exception;

class DTResponse implements JsonSerializable
{

    public $fields = [];
    public $source = null;

    public $_columns = [];

    public function __construct($source)
    {
        $this->source = $source;
        $this->draw = $_GET["draw"];
        $this->request["columns"] = $_GET["columns"];
        $this->order = $_GET["order"];
        $this->start = $_GET["start"];
        $this->length = $_GET["length"];
        $this->search = $_GET["search"];

        foreach ($_GET["_columns"] as $i => $c) {
            $this->request["columns"][$i] = array_merge($this->request["columns"][$i], $c);
        }
    }

    public function where()
    {
        $where = [];
        return $where;
    }

    public function addEdit()
    {
        $c = new Column();
        $c->title = "";
        $c->type = "edit";
        $c->data = "__edit__";
        $c->name = "__edit__";
        $c->className[]="text-center";
        $c->width="1px";
        $c->descriptor[] = function ($obj) {
            if (!$obj->canUpdate()) {
                return;
            }
            $a = html("a")->class("btn btn-xs btn-warning")->href($obj->uri("ae"));
            $a->i->class("fa fa-pencil-alt fa-fw");
            return $a;
        };
        $this->_columns["__edit__"] = $c;
        return $c;
    }

    public function addView()
    {
        $c = new Column();
        $c->title = "";
        $c->type = "view";
        $c->data = "__view__";
        $c->name = "__view__";
        $c->className[]="text-center";
        $c->width="1px";
        $c->descriptor[] = function ($obj) {
            if (!$obj->canRead()) {
                return;
            }
            $a = html("a")->class("btn btn-xs btn-info")->href($obj->uri("v"));
            $a->i->class("fa fa-search fa-fw");
            return $a;
        };
        $this->_columns["__view__"] = $c;
        return $c;
    }


    public function addDel()
    {
        $c = new Column();
        $c->title = "";
        $c->type = "delete";
        $c->data = "__del__";
        $c->name = "__del__";
        $c->width="1px";
        $c->className[]="text-center";
        $c->descriptor[] = function ($obj) {
            if (!$obj->canDelete()) {
                return;
            }
            return $a=html("a")->class("btn btn-xs btn-danger")->href($obj->uri("del"))->html("<i class='fa fa-fw fa-times'></i>");
        };
        $this->_columns["__del__"] = $c;
        return $c;
    }

    public function add($name, $getter)
    {
        $c = new Column();
        $c->name = $name;
        $c->descriptor[] = $getter;

        $this->_columns[$name] = $c;

        return $c;
    }

    public function data()
    {
        foreach($this->fields as $c){
            $this->add($c,$c);
        }

        $source = $this->filteredSource();
        $source->limit($this->start . "," . $this->length);

        $data = [];
        foreach ($source as $obj) {
            $d = [];
            foreach ($this->request["columns"] as $k => $c) {
                try {
                    if (array_key_exists($c["name"], $this->_columns)) {
                        $col = $this->_columns[$c["name"]];
                        $d[$c["data"]] = (string)$col->getData($obj, $k);
                    } else {
                        $d[$c["data"]] = null;
                    }
                } catch (Exception $e) {
                    $d[$c["data"]] = $e->getMessage();
                }
            }
            $data[] = $d;
        }

        return $data;
    }

    public function recordsTotal()
    {
        return $this->source->count();
    }

    public function filteredSource(){
        $source = clone $this->source;

        foreach ($this->order as $o) {
            $c = $this->request["columns"][$o["column"]];
            if ($c["orderable"] == "false") continue;
            $source->orderBy($c["data"] . " " . $o["dir"]);
        }

        foreach ($this->request["columns"] as $k => $c) {
            if ($c["searchable"] == "false") continue;
            $column=$this->_columns[$c["name"]];
            
            if ($value = $c["search"]["value"]) {
                if($column->searchCallback){
                    $w=[];
                    $w[] = call_user_func($column->searchCallback, $value);
                    $source->where($w);
                }elseif ($c["searchType"] == "text") {
                    $w = [];
                    $w[] = [$c["data"] . " like ?", "%$value%"];
                    $source->where($w);
                } elseif ($c["searchType"] == "select" || $c["searchType"] == "select2") {
                    $w = [];
                    $w[] = [$c["data"] . " = ?", $value];
                    $source->where($w);
                } elseif ($c["searchType"] == "date"){
                    $value=json_decode($value,true);
                    $field=$c["data"];
                    $w=[];
                    $w[]=["date(`$field`) between ? and ?",[$value["from"],$value["to"]]];
                    $source->where($w);
                }
            }
        }

        $source->limit($this->start . "," . $this->length);
        return $source;
    }

    public function recordsFiltered()
    {
        $source = $this->filteredSource();
        return $source->count();
    }

    public function jsonSerialize()
    {
        return [
            "draw" => $this->draw,
            "data" => $this->data(),
            "recordsTotal" => $this->recordsTotal(),
            "recordsFiltered" => $this->recordsFiltered()
        ];
    }
}