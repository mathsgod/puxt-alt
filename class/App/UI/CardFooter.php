<?php

namespace App\UI;

use P\HTMLDivElement;
use App\Page;

class CardFooter extends HTMLDivElement
{
    public $page;
    public function __construct(Page $page)
    {
        parent::__construct();
        $this->page = $page;

        $this->classList->add("card-footer");
    }
}
