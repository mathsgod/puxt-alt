<?php
namespace App\UI;

class Element  extends \P\HTMLElement
{
    public function setAttribute(string $name, $value=null)
    {
        return parent::setAttribute($name, is_array($value) ? json_encode($value) : $value);
    }
}
