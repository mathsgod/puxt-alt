<?php

namespace App\UI;

class CheckBox extends \P\HTMLDivElement
{

    private static $_num = 0;
    public $input;
    public $label;

    public function __construct()
    {
        self::$_num++;
        parent::__construct();
        $this->classList->add("icheck-primary");

        $id = "_checkbox_" . self::$_num;
        $this->input = $this->ownerDocument->createElement("input");
        $this->input->setAttribute("type", "checkbox");
        $this->input->setAttribute("id", $id);
        $this->appendChild($this->input);

        $this->label = $this->ownerDocument->createElement("label");
        $this->label->setAttribute("for", $id);
        $this->appendChild($this->label);
    }
}
