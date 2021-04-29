<?php

namespace App\UI;

use JsonSerializable;
use Closure;

class Column implements JsonSerializable
{
    public $name;
    public $title;
    public $data;
    public $orderable = false;
    public $searchable = false;
    public $searchType = "text";
    public $searchOptions = [];
    public $type = "text";
    public $alink = null;
    public $descriptor = [];
    public $width = null;
    public $minWidth = null;
    public $maxWidth = null;
    public $overflow = null;

    public $className = [];

    public $format = null;
    public $searchCallback = null;

    public $editable = false;
    public $editType = 'text';
    public $editData;
    public $wrap = false;
    public $noHide = false;

    public $align = null;

    public $searchOptGroups = null;
    public $searchOptValue = null;
    public $searchMethod;

    public $contents = [];

    public $order;



    public $cell_value_getter = null;
    public function setCellValue($getter)
    {
        $this->cell_value_getter = $getter;
    }

    public function getCellValue($obj)
    {
        if ($this->cell_value_getter) {
            return var_get($obj, $this->cell_value_getter);
        }
        return null;
    }

    public function addEdit()
    {
        $this->contents[] = function ($obj) {
            if (!$obj->canUpdate()) {
                return;
            }
            $a = html("a")->class("btn btn-xs btn-warning")->href($obj->uri("ae"));
            $a->i->class("fa fa-pencil-alt fa-fw");
            return $a;
        };
        return $this;
    }

    public function addView()
    {
        $this->contents[] = function ($obj) {
            if (!$obj->canRead()) {
                return;
            }
            $a = html("a")->class("btn btn-xs btn-info")->href($obj->uri("v"));
            $a->i->class("fa fa-search fa-fw");
            return $a;
        };
        return $this;
    }

    public function align($align)
    {
        $this->align = $align;
        return $this;
    }

    public function noHide()
    {
        $this->noHide = true;
        return $this;
    }

    public function minWidth(string $width)
    {
        $this->minWidth = $width;
        return;
    }

    public function width(string $width)
    {
        $this->width = $width;
        $this->minWidth = $width;
        $this->maxWidth = $width;
        $this->overflow = "hidden";
        return $this;
    }

    public function editable(string $type = "text", $data = null)
    {
        $this->editable = true;
        $this->editType = $type;
        $this->editData = $data;
        if (is_array($data)) {
            $this->editData = [];
            foreach ($data as $k => $v) {
                $this->editData[] = ["value" => $k, "label" => $v];
            }
        }
        return $this;
    }

    public function filter($filter)
    {
        $this->filter = $filter;
        $this->type = "html";
        return $this;
    }

    public function format($format)
    {
        $this->format = $format;
        $this->type = "html";
        return $this;
    }

    public function gf($descriptor)
    {
        $this->descriptor[] = $descriptor;
        return $this;
    }

    public function alink($alink = null)
    {
        $this->type = "html";
        $this->alink = $alink;
        return $this;
    }

    public function wrap()
    {
        $this->wrap = true;
        return $this;
    }

    public function ss()
    {
        $this->orderable = true;
        $this->searchable = true;
        $this->searchMethod = "like";
        return $this;
    }

    public function search()
    {
        $this->searchable = true;
        $this->searchMethod = "like";
        return $this;
    }

    public function sort($index = null)
    {
        return $this->order($index);
    }

    public function sortCallback($callback)
    {
        $this->sortCallback = $callback;
        return $this;
    }

    public function order($order)
    {
        if ($order) $this->order = $order;
        $this->orderable = true;
        return $this;
    }

    public function searchCallBack(callable $callback)
    {
        $this->searchable = true;
        $this->searchCallback = $callback;
        return $this;
    }

    public function searchSelect2($objects, $display_member = null, $value_member = null)
    {
        $this->searchable = true;
        $this->searchOptions = array($objects, $display_member, $value_member);
        $this->searchType = 'select2';
        $this->searchMethod = "equal";
        return $this;
    }

    public function searchDate()
    {
        $this->searchable = true;
        $this->searchType = 'date';
        $this->searchMethod = "date";
        return $this;
    }

    public function searchEq()
    {
        $this->searchable = true;
        $this->searchType = 'equal';
        $this->searchMethod = "equal";
        return $this;
    }

    public function searchSingle($objects, $display_member = null, $value_member = null)
    {
        $this->searchable = true;
        $this->searchOptions = array($objects, $display_member, $value_member);
        $this->searchType = 'multiselect';
        $this->searchMethod = "equal";
        return $this;
    }

    public function searchMultiple($objects, $display_member = null, $value_member = null)
    {
        $this->searchable = true;
        $this->searchOptions = array($objects, $display_member, $value_member);
        $this->searchType = 'multiselect';
        $this->searchMultiple = true;
        $this->searchMethod = "multiple";
        return $this;
    }

    public function searchOption($objects, $display_member = null, $value_member = null)
    {
        $this->searchable = true;
        $this->searchOptions = array($objects, $display_member, $value_member);
        $this->searchType = 'select';
        $this->searchMethod = "equal";
        return $this;
    }

    public function searchOptGroup($groups, $value)
    {
        $this->searchOptGroups = $groups;
        $this->searchOptValue = $value;
        return $this;
    }

    public function _searchOption()
    {
        $data = [];
        $display_member = $this->searchOptions[1];
        $value_member = $this->searchOptions[2];
        if (!$value_member) {
            $value_member = $this->data;
        }

        foreach ($this->searchOptions[0] as $k => $v) {
            if (is_object($v)) {
                $d = [
                    "label" => $display_member ? \My\Func::_($display_member)->call($v) : (string)$v,
                    "value" => \My\Func::_($value_member)->call($v)
                ];
                if ($this->searchOptValue) {
                    $d["group"] = \My\Func::_($this->searchOptValue)->call($v);
                }

                $data[] = $d;
            } else {
                $data[] = ['label' => $v, 'value' => $k];
            }
        }
        return $data;
    }



    public function getData($object, $k)
    {
        if ($this->contents) {
            $contents = array_map(function ($callable) use ($object) {
                return (string)call_user_func($callable, $object);
            }, $this->contents);

            return $contents;
        }


        $result = $object;
        $last_obj = $object;
        foreach ($this->descriptor as $descriptor) {
            $result = \My\Func::_($descriptor)->call($result);
            if (is_object($result)) {
                $last_obj = $result;
            }
        }

        if ($this->format) {
            if (is_string($this->format) && function_exists($this->format)) {
                $func = $this->format;
                $result = $func($result);
            } else {
                $result = \My\Func::_($this->format)->call($result);
            }
        }

        if ($this->alink && $last_obj) {

            $a = html("el-link")->href($last_obj->uri($this->alink))->type("info");
            $a->text($result);
            $result = "<vue>$a</vue>";
        }
        return $result;
    }

    public function jsonSerialize()
    {
        $data = [];
        $data["name"] = $this->name;
        $data["title"] = $this->title;
        $data["data"] = $this->data;
        $data["type"] = $this->type;
        $data["orderable"] = $this->orderable;
        $data["searchable"] = $this->searchable;
        $data["searchType"] = $this->searchType;
        $data["searchOption"] = $this->_searchOption();
        $data["searchOptGroup"] = $this->searchOptGroups;
        $data["searchMultiple"] = $this->searchMultiple;
        $data["searchMethod"] = $this->searchMethod;
        $data["editable"] = $this->editable;
        $data["editType"] = $this->editType;
        $data["editData"] = $this->editData;
        $data["wrap"] = $this->wrap;
        $data["noHide"] = $this->noHide;
        if ($this->align) {
            $data["cellStyle"]["text-align"] = $this->align;
        }
        if ($this->width) $data["width"] = $this->width;
        if ($this->minWidth) $data["minWidth"] = $this->minWidth;
        if ($this->maxWidth) $data["maxWidth"] = $this->maxWidth;
        if ($this->overflow) $data["overflow"] = $this->overflow;
        if ($this->className) $data["className"] = implode(" ", $this->className);
        return $data;
    }
}
