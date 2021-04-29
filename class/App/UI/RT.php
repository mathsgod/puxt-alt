<?php
namespace App\UI;

//class RT extends \RT
class RT extends \ALT\RT
{
    protected $_module;
    public function __construct($objects, $module, $request)
    {
        $this->_module = $module;
        if (is_string($module)) {
            $this->_module = \App\Module::_($module);
        }
        $this->bind($objects);
        parent::__construct($request);
    }

    public function add($label, $callback = null)
    {
        return parent::add($this->_module->translate($label), $callback);
    }
}
