<?php

namespace App\UI;

use Traversable;

class T extends Card
{
    public $objects;
    public $table;

    public function __construct($objects, \App\Page $page)
    {
        parent::__construct($page);
        $this->objects = $objects;

        $this->setAttribute("primary", true);
        $this->setAttribute("outline", true);

        $this->body->classList->add("p-0");
        $this->body->classList->add('table-responsive');
        $this->classList->add("box-primary");
        $this->table = new Table($objects, $page);
        $this->table->classList->add("table-sm");
        $this->table->classList->add("table-hover");
        $this->body->append($this->table);
    }

    public function addCheckbox($index, $callback)
    {
        return $this->table->addCheckbox($index, $callback);
    }

    public function add(string $label, $getter = null): Col
    {
        return $this->table->add($this->page->translate($label), $getter);
    }

    public function addChildRow(string $label, $getter = null)
    {
        return $this->table->addChildRow($this->page->translate($label), $getter);
    }

    public function addView(): Col
    {
        return $this->table->addView();
    }

    public function addEdit(): \P\AnchorCollection
    {
        return $this->table->addEdit();
    }

    public function addDel(): Col
    {
        return $this->table->addDel();
    }

    public function setCreate(string $uri)
    {
        return $this->header->addButton(null, $uri)->icon("fa fa-fw fa-plus")->addClass("btn-primary");
    }

    public function formCreate($options, $default = null)
    {
        $this->table->setAttribute("form-create", true);

        if (is_string($options)) {
            $opt = [
                "name" => $options,
                "default" => $default
            ];
        } else {
            $opt = $options;
        }

        $this->table->setAttribute("form-name", $opt["name"]);
        $this->table->default = $opt["default"];

        return $this;
    }

    public function subHTML($label, $callback, $index): Col
    {
        $url = $callback[0]->path() . "/" . $callback[1];
        return $this->table->add($label, function ($o) use ($url, $index) {
            if (is_object($o)) {
                if ($index) {
                    $url .= "?" . http_build_query([$index => $o->$index]);
                } else {
                    $url .= "?" . http_build_query(["id" => $o->ID()]);
                }
            } else {
                $url .= "?" . http_build_query([$index => $o[$index]]);
            }

            return "<button class='btn btn-xs btn-primary table-childrow-btn table-childrow-close' data-url='$url' data-target=''><i class='fa fa-chevron-up'></i></button>";
        });
    }

    public function header(string $title)
    {
        $this->card->header->title = $title;
    }
}
