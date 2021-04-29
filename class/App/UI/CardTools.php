<?php

namespace App\UI;

use ALT\Dropdown;
use P\HTMLDivElement;
use App\Page;
use P\HTMLButtonElement;
use P\HTMLSpanElement;

class CardTools extends  HTMLDivElement
{
    protected $page;
    public function __construct(Page $page)
    {
        parent::__construct();
        $this->page = $page;
        $this->classList->add("card-tools");
    }

    public function addButton(string $text): HTMLButtonElement
    {
        $btn = $this->ownerDocument->createElement("button", $text);
        $btn->setAttribute("type", "button");
        $btn->classList->add("btn btn-tool");
        $this->appendChild($btn);
        return $btn;
    }

    public function addBadge(string $text): HTMLSpanElement
    {
        $span = $this->ownerDocument->createElement("span", $text);
        $span->classList->add("badge badge-primary");
        $this->appendChild($span);
        return $span;
    }

    public function addDropdown(string $label)
    {

        $bg = new Dropdown($label);
        $bg->button->classList->add('btn-tool');
        $bg->button->classList->remove("btn-default");

        p($bg->button)->text($label);

        $this->append($bg);
        return $bg;
    }
}
