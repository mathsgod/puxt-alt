<?php

namespace App\UI;

use App\Page;
use ALT\C2;
use My\Func;
use P\HTMLTableElement;

class Table extends HTMLTableElement
{
    protected $page;
    protected $objects;
    protected $columns = [];
    public $key;

    public function __construct($objects, Page $page)
    {
        parent::__construct();
        $this->objects = $objects;
        $this->page = $page;
        $this->setAttribute("is", "alt-table");
        $this->classList->add("table");
        $this->classList->add("table-sm");

        $this->createTBody();
    }

    public function add($label = null, $getter = null): Col
    {
        $column = new Col("th", $this->page);
        $column->default = $this->default;

        $this->columns[] = $column;

        if ($this->hasAttribute("form-create")) {

            $column->createTemplate = true;
        }

        $thead = $this->createTHead();
        $th = p($column)->appendTo($thead);

        //$th->text($this->page->translate($label));
        $th->text($label);
        $column->cell = new \P\Query;
        $i = 0;

        $tbody = $this->tBodies[0];

        foreach ($this->objects as $k => $obj) {
            if ($tbody->rows->length <= $i) {

                $row = $tbody->insertRow();
                if ($obj instanceof \App\Model) {
                    $row->setAttribute("data-index", $obj->id());
                } else {
                    if ($this->key) {
                        if (is_array($obj)) {
                            $row->setAttribute("data-index", $obj[$this->key]);
                        } else {
                            $row->setAttribute("data-index", $obj->{$this->key});
                        }
                    } else {
                        $row->setAttribute("data-index", $k);
                    }
                }
            } else {
                $row = $tbody->rows[$i];
            }
            $i++;

            $cell = $row->insertCell();

            p($cell)->data("object", $obj);

            $column->cell[] = $cell;

            if ($getter) {
                if ($getter instanceof \Closure) {
                    p($cell)->html(call_user_func_array($getter, [$obj, $k]));
                } else {
                    $cell->setAttribute("data-name", $getter);
                    p($cell)->text(Func::_($getter)->call($obj));
                }
            }
        }

        $form_name = p($this)->attr("form-name");

        $column->callback = function ($object, $node) use ($form_name) {
            $field = $node->getAttribute("data-field");


            $tr = p($node)->closest("tr");

            $id = $tr->attr("data-index");

            $fn = "_u";
            if ($form_name)
                $fn = $form_name . "[u]";

            if ($node->hasAttribute("multiple")) {
                $node->setAttribute("name", "{$fn}[$id][$field][]");
            } else {
                $node->setAttribute("name", "{$fn}[$id][$field]");
            }
        };


        return $column;
    }

    public function row()
    {
        return p($this->rows);
    }

    public function addView()
    {
        $column = $this->add();
        $column->width(20);

        foreach ($column->cell() as $c) {
            $obj = p($c)->data("object");
            if ($obj instanceof \App\Core\Model) {
                if ($obj->canRead()) {
                    $btn = html("a")->class("btn btn-xs btn-info")->href($obj->uri('v'))->html("<i class='fa fa-fw fa-search'></i>");
                    p($c)->html($btn);
                }
            }
        }

        return $column;
    }

    public function addDel()
    {
        $column = $this->add();
        $column->width(20);

        foreach ($column->cell() as $c) {
            $obj = p($c)->data("object");
            if ($obj instanceof \App\Model) {
                if ($obj->canDelete()) {
                    $btn = html("a")->class("btn btn-xs btn-danger confirm")->href($obj->uri('index/delete'))->html("<i class='fa fa-fw fa-times'></i>");
                    p($c)->html($btn);
                }
            }
        }

        return $column;
    }


    public function addEdit()
    {
        $column = $this->add();
        $column->width(20);

        $p = new \P\AnchorCollection();
        foreach ($column->cell() as $c) {
            $obj = p($c)->data("object");
            if ($obj instanceof \App\Model) {
                if ($obj->canRead()) {
                    $btn = p("a")->attr([
                        "class" => "btn btn-xs btn-warning",
                        "href" => $obj->uri('ae')
                    ])->html("<i class='fa fa-fw fa-pencil-alt'></i>");
                    $btn->appendTo($c);
                    $p[] = $btn[0];
                }
            }
        }

        return $p;
    }

    public function addCheckBox($index, $getter)
    {
        $column = $this->add(null, function ($obj) use ($index, $getter) {
            $input = p("input");
            $input->attr("type", "checkbox");
            $input->attr("is", "iCheck");
            if ($index) {
                $input->attr("index", $index);
                if (is_array($obj)) {
                    $input->val($obj[$index]);
                } else {
                    $input->val($obj->$index);
                }
                $input->attr("name", "{$index}[]");

                if ($getter) {
                    if ($getter($obj)) {
                        $input->attr("checked", true);
                    }
                }
            }
            return $input;
        })->width(20);


        /*$column->html("<input type='checkbox' class='iCheck' onClick='
var checked=$(this).is(\":checked\");
var index=$(this).closest(\"th\").index();
var td=$(this).closest(\"table\").find(\"tbody tr\").find(\"td:nth(\"+index+\")\");
if(checked){
	td.find(\".iCheck\").iCheck(\"check\");
}else{
	td.find(\".iCheck\").iCheck(\"uncheck\");
}

'/>");*/
        return $column;
    }


    public function addChildRow($label, $getter)
    {
        $label = $this->page ? $this->page->translate($label) : $label;
        return parent::addChildRow($label, $getter);
    }

    public function __toString()
    {

        $html = parent::__toString();
        $o = p($html);
        $o->find("td")->each(function ($i, $o) {
            if (p($o)->find("input,textarea,select")->count()) {
                p($o)->wrapInner("<div class='form-group m-0'></div>");
            }
        });
        return (string) $o;
    }
}
