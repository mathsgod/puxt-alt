<?php

namespace App\UI;

use P\HTMLDivElement;
use App\Page;

class CardHeader extends HTMLDivElement
{
    protected $page;
    public $tools;

    public function __construct(Page $page)
    {
        parent::__construct();
        $this->page = $page;
        $this->classList->add("card-header");
        $this->setAttribute("is","card-header");

        $this->tools = new CardTools($page);
        $this->appendChild($this->tools);
    }

    public function addButton($label, $uri)
    {
        $button = new Button("default", "btn-xs", $uri);
        $button->classList[] = "btn-xs";
        $button->text($label);
        $this->appendChild($button);
        return $button;
    }

    public function __set($name, $value)
    {
        if ($name == "title") {
            $template = p("<h5 class='card-title'></h5>");
            $template->text($value);
            $this->prepend($template[0]);
            return;
        }
        parent::__set($name, $value);
    }

    public function __debugInfo()
    {
        return ["tools" => "a"];
    }
}
