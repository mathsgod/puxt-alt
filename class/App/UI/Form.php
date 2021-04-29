<?php

namespace App\UI;

use App\Page;
use P\HTMLFormElement;
use Vue\Scriptable;

class Form extends HTMLFormElement implements Scriptable
{

    private $submit_button;
    private $back_button;
    private $reset_button;
    protected $page;
    public $card;

    public function __construct(Page $page)
    {
        parent::__construct();

        //$this->setAttribute("is"]="alt-form"
        $this->page = $page;
        $this->method = "post";

        $this->card = new Card($page);
        $this->card->setAttribute("primary", true);
        $this->card->setAttribute("outline", true);

        $body = $this->card->body;

        $this->submit_button = new Button($page);
        $this->submit_button->classList->add("btn-success");
        $this->submit_button->setAttribute("type", "submit");
        $this->submit_button->setAttribute("icon", "fa fa-fw fa-check");
        $this->submit_button->setAttribute("is", "alt-button");
        $this->submit_button->label("Submit");

        $this->card->footer->append($this->submit_button);
        $this->card->footer->append(" ");

        $this->reset_button = new Button($page);
        $this->reset_button->classList->add("btn-info");
        $this->reset_button->classList->add("d-none");
        $this->reset_button->icon("fa fa-rotate-left")->label("Reset");
        $this->reset_button->setAttribute("type", "reset");
        $this->card->footer->append($this->reset_button);
        $this->card->footer->append(" ");

        $this->back_button = new Button($page);
        $this->back_button->classList->add("btn-warning text-white");
        $this->back_button->label("Back");
        $this->back_button->setAttribute("type", "button");
        if ($_GET["fancybox"]) {
            $this->back_button->setAttribute("data-fancybox-close", true);
        } else {
            $this->back_button->setAttribute("onClick", 'javascript:history.back(-1)');
        }

        $this->card->footer->append($this->back_button);
        if ($_GET["fancybox"]) {
            $this->action($page->uri());
        }

        $this->appendChild($this->card);

        $this->show_back = true;
        $this->show_reset = false;
    }

    public function __get($name)
    {
        switch ($name) {
            case "header":
                return $this->card->header;
                break;
        }
        return parent::__get($name);
    }

    public function __set($name, $value)
    {
        switch ($name) {
            case "show_back":
                if ($value) {
                    $this->back_button->classList->remove("hide");
                } else {
                    $this->back_button->classList->add("hide");
                }
                return;
                break;
            case "show_reset":
                if ($value) {
                    $this->reset_button->classList->remove("hide");
                } else {
                    $this->reset_button->classList->add("hide");
                }
                return;
                break;
        }

        parent::__set($name, $value);
    }

    public function action($action = "")
    {
        $this->setAttribute("action", $action);
        return $this;
    }

    public function addBody($body)
    {

        $this->card->body()->append((string) $body);
        return $this;
    }

    public function submitCheck($func)
    {
        $this->submit_check = $func;
        return $this;
    }

    public function card()
    {
        return p($this->card);
    }

    public function addHidden($name, $value)
    {
        $input = p("input")->appendTo($this);
        $input->attr("name", $name)->attr("type", "hidden")->val($value);
        return $input;
    }

    public function attr($name, $value)
    {
        return p($this)->attr($name, $value);
    }

    public function validate(array $arr): bool
    {
        $controls = [];
        foreach ($this->querySelectorAll("input") as $input) {
            $controls[$input->getAttribute("name")] = $input;
        }
        foreach ($this->querySelectorAll("select") as $select) {
            $controls[$select->getAttribute("name")] = $select;
        }
        foreach ($this->querySelectorAll("textarea") as $textarea) {
            $controls[$select->getAttribute("textarea")] = $textarea;
        }

        foreach ($arr as $name => $value) {
            if (!$controls[$name]) {
                return false;
            }
        }

        return true;
    }

    public function script()
    {
        return $this->card->script();
    }
}
