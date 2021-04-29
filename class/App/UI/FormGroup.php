<?php
namespace App\UI;

use P\HTMLDivElement;


class FormGroup extends HTMLDivElement
{
    public function __construct()
    {
        parent::__construct();
        //        $this->attributes["is"]="bs-form-group";
        $this->classList[] = "form-group";
    }
}

