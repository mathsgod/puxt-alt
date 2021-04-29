<?php

namespace ALT;

class Element  extends \P\HTMLElement
{
    public function setAttribute($name, $value)
    {
        return parent::setAttribute($name, is_array($value) ? json_encode($value) : $value);
    }
}
