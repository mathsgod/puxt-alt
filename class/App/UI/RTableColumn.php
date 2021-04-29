<?php

namespace App\UI;

use P\HTMLElement;

class RTableColumn extends HTMLElement
{
    const EDIT_TYPE_TEXT = "text";
    const EDIT_TYPE_MULTISELECT = "multiselect";
    const EDIT_TYPE_SELECT = "select";
    const EDIT_TYPE_DATE = "date";


    public function __construct()
    {
        parent::__construct("r-table-column");
        $this->setAttribute("nowrap", true);
    }

    public function nowrap(bool $nowrap)
    {
        if ($nowrap) {
            $this->setAttribute("nowrap", true);
        } else {
            $this->removeAttribute("nowrap");
        }
        return $this;
    }

    public function editable(string $type = "text")
    {
        $this->setAttribute("editable", true);
        $this->setAttribute("edit-type", $type);
        return $this;
    }

    /**
     * @deprecated use sortable
     **/
    public function sort()
    {
        return $this->sortable();
    }

    /**
     * @deprecated use searchable
     **/
    public function search()
    {
        return $this->searchable();
    }

    public function ss()
    {
        $this->sortable();
        $this->searchable();
        return $this;
    }

    public function searchable(string $type = "text")
    {
        $this->setAttribute("searchable", true);
        $this->setAttribute("search-type", $type);
        return $this;
    }

    public function searchOption($options = null, string $display_member = null, string $value_member = null)
    {
        if ($value_member === null) {
            $value_member = $this->getAttribute("prop");
        }


        $data = [];
        foreach ($options as $k => $v) {
            if (is_object($v)) {
                $d = [
                    "label" => $display_member ? var_get($v, $display_member) : (string)$v,
                    "value" => var_get($v, $value_member)
                ];
                /*                 if ($this->searchOptValue) {
                    $d["group"] = \My\Func::_($this->searchOptValue)->call($v);
                }
 */
                $data[] = $d;
            } else {
                $data[] = ['label' => $v, 'value' => $k];
            }
        }
        $this->setAttribute(":search-option", json_encode($data, JSON_UNESCAPED_UNICODE));
        return $this;
    }

    public function sortable()
    {
        $this->setAttribute("sortable", true);
        return $this;
    }

    public function width(string $width)
    {
        $this->setAttribute("width", $width);
        $this->nowrap(false);
        return $this;
    }

    public function searchDate()
    {
        return $this->searchable("date");
    }
}
