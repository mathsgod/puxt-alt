<?php

namespace App\UI;

use \My\Func;
use App\Page;

class V extends Card
{
    public $columns = [];
    public $tableClass = [];
    public $tables = [];
    public $row = 0;

    public $container;

    public function __construct($object, Page $page)
    {
        parent::__construct($page);
        $this->classList->add("alt-v");
        $this->object = $object;
        $this->tableClass = "table-sm";
        $this->body->classList->add('p-0');
        $this->container = $this->body();

        $this->addTable();
    }

    public function setColumnRatio($ratio)
    {
        $this->column_ratio = $ratio;
        return $this;
    }

    public function header($title = null)
    {
        $this->title = $title;
        return $this;
    }

    public function addBreak()
    {
        $this->row++;

        $row_div = p("div")->addClass("col-xs-12")->appendTo($this->body());
        $wrap_div = p("div")->addClass("col-xs-12")->appendTo($row_div);
        $this->container = $wrap_div;

        $this->addTable();
        return $this;
    }

    public function addSplit()
    {
        $tables = $this->tables[$this->row];
        $c = count($tables);
        if ($c == 1 && count($this->tables) == 1) {
            $div = p("div")->addClass("row")->appendTo($this->container);
            $tables[0]->appendTo($div);
            $wrap_div = p("div")->addClass("col-xs-12");
            $tables[0]->wrap($wrap_div);
        }

        $row_div = $tables[0]->parent()->parent();

        $this->container = p("div")->addClass("col-xs-12")->appendTo($row_div);
        $this->addTable();

        $c = count($this->tables[$this->row]);
        $col_class = floor(12 / $c);

        foreach ($row_div->children("div") as $div) {
            p($div)->removeClass();
            p($div)->addClass("col-md-" . $col_class);
        }
        return $this->table;
    }

    public function addNext()
    {
        $this->table = p("table")->addClass("table")->appendTo($this->container);
        $this->table->addClass($this->tableClass);
        $this->table->append(p("tbody"));
    }

    public function addTable(): \P\Query
    {

        $this->table = p("table")->addClass("table")->appendTo($this->container);
        $this->table->addClass($this->tableClass);
        $this->table->append(p("tbody"));
        $this->tables[$this->row][] = $this->table;
        return $this->table;
    }

    public function addHr()
    {

        $tbody = $this->table->find('tbody');
        $tr = p("tr")->appendTo($tbody);
        $tr->append("<td colspan='2'><hr style='margin:0px'/></td>");
        return $this;
    }

    public function add($label, $getter = null)
    {
        $label = ($this->page) ? $this->page->translate($label) : $label;
        $tbody = $this->table->find("tbody");

        $tr = new Col("tr", $this->page);
        $tr->classList->add("d-flex");

        p($tr)->appendTo($tbody);
        $th = p("th")->addClass("bg-info text-nowrap overflow-hidden")->append($label);
        $th->appendTo($tr);

        $th->addClass("col-3 col-md-2");

        $cell = p("td");
        $cell->addClass("col");
        $cell->data("object", $this->object);
        $tr->cell[] = $cell[0];
        if ($getter instanceof \Closure) {
            $cell->html($getter($this->object));
        } elseif ($getter) {
            $cell->attr("data-name", $getter);
            $cell->text(Func::_($getter)->call($this->object));
        }

        $cell->appendTo($tr);

        return $tr;
    }

    public function __get($name)
    {
        if ($name == "header") {
            $this->setAttribute("info", true);
            $this->setAttribute("outline", true);
        }
        return parent::__get($name);
    }
}
