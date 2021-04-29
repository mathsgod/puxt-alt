<?php

namespace App\UI;

class A extends \P\HTMLAnchorElement
{
    public function __construct()
    {
        parent::__construct();
        $this->classList->add("btn");
    }

    public function addClass(string $class): self
    {
        $this->classList->add($class);
        return $this;
    }
    public function icon(string $icon): self
    {
        $this->prepend(p("<i class='$icon'></i>")[0]);
        return $this;
    }
}
