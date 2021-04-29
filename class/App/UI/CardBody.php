<?php

namespace App\UI;

use P\HTMLElement;
use App\Page;

class CardBody extends HTMLElement
{
    public $page;
    public function __construct(Page $page)
    {
        parent::__construct("card-body");
        $this->page = $page;
    }
}
