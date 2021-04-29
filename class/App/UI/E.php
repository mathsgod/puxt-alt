<?php

namespace App\UI;

use P\HTMLDivElement;

class E extends HTMLDivElement
{
    public $object;
    public $row = [];
    public $column_ratio = [2, 10];
    public $content;
    public $contents = [];
    public $page = null;

    public function __construct($object, \App\Page $page)
    {
        parent::__construct();
        $this->page = $page;
        $this->object = $object;
        $this->content = p("div")->addClass("col-md-12")->appendTo($this);
        $this->contents[] = $this->content;
    }

    public function setColumnRatio($ratio)
    {
        $this->column_ratio = $ratio;
        return $this;
    }

    public function addSplit()
    {
        $this->classList->add("row col-md-12");
        
        $this->content = p("div")->addClass("col-md-12")->appendTo($this);
        $this->contents[] = $this->content;

        //resize
        $col = floor(12 / count($this->contents));
        foreach ($this->contents as $content) {
            $content->removeClass();
            $content->addClass("col-md-{$col}");
        }
    }

    public function addBreak()
    {
        $div = p("div")->addClass("row col-md-12");
        foreach ($this->contents as $content) {
            $div->append($content);
        }
        $div->appendTo($this);

        $this->contents = [];
        $this->content = p("div")->addClass("col-md-12")->appendTo($this);
        $this->contents[] = $this->content;
    }

    public function addHr()
    {
        $hr = p("hr")->appendTo($this->content);
        return $hr;
    }

    public function addHeader($type)
    {
        $h = new C($type);
        p($h)->appendTo($this->content);
        return $h;
    }

    public function addParagraph()
    {
        $form_group = new C("div");
        $form_group->classList->add('form-group');
        p($form_group)->appendTo($this->content);
        return $form_group;
    }


    public function add(string $label, $getter = null): Col
    {
        $form_group = new FormGroup();
        $form_group->classList->add("row");
        $label = p("label")->addClass("col-sm-2 col-form-label")->text($this->page->translate($label));
        p($form_group)->append($label);

        $c2 = new Col("div", $this->page);
        $c2->classList->add('col-md-10');

        $cell = p("div");
        $cell->appendTo($c2);
        $cell->data("object", $this->object);

        $c2->cell[] = $cell[0];

        if ($getter) {
            $static = p("p");
            $static->addClass("col-form-label");
            if ($getter instanceof \Closure) {
                $static->html($getter($this->object));
            } else {
                $result = \My\Func::_($getter)->call($this->object);

                $static->text($result);
                if (is_object($result)) {
                    $cell->data("object", $result);
                }
            }

            $static->appendTo($cell);
        }

        $c2->callback = function ($object, $node) {
            if ($node->tagName == "a") {
                p($node)->wrap(p("p")->addClass("col-form-label"));
            }
        };

        $form_group->appendChild($c2);

        $this->content->append($form_group);
        $this->content->append("\n");



        return $c2;
    }
}
