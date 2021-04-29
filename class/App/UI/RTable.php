<?php


namespace App\UI;

use App\Translatable;
use App\TranslatorInterface;
use P\HTMLElement;


/**
 * @property bool $selectable 
 * @property \P\Element $header
 * @property \App\TranslatorInterface $translator
 */
class RTable extends HTMLElement implements Translatable
{
    public function __construct()
    {
        parent::__construct("r-table");

        $this->dropdown = new Element("template");
        $this->dropdown->setAttribute("slot", "dropdown");
        $this->append($this->dropdown);

        $this->header = new Element("template");
        $this->header->setAttribute("slot", "header");
        $this->append($this->header);
    }

    public function setTranslator(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function __set($name, $value)
    {

        switch ($name) {
            case "selectable":
                $this->setAttribute("selectable", true);
                return;
                break;
        }
        parent::__set($name, $value);
    }

    public function __get($name)
    {
        switch ($name) {
            case "selectable":
                return $this->getAttribute("selectable");
        }

        return parent::__get($name);
    }

    public function addSubrow(string $prop)
    {
        $col = $this->add("", $prop);
        $col->setAttribute("width", "55px");
        return $col;
    }

    public function addView()
    {
        $col = new RTableColumn();
        $col->setAttribute("width", "36px");
        $col->setAttribute("prop", "__view__");

        $this->append($col);

        return $col;
    }


    public function addEdit()
    {
        $col = new RTableColumn();
        $col->setAttribute("prop", "__edit__");
        $col->setAttribute("width", "36px");
        $this->append($col);
        return $col;
    }

    public function addDel()
    {
        $col = new RTableColumn();
        $col->setAttribute("width", "36px");
        $col->setAttribute("prop", "__del__");

        $this->append($col);
        return $col;
    }

    public function setCellUrl(string $url)
    {
        $this->setAttribute("cell-url", $url);
        return $this;
    }

    public function setKey(string $key)
    {
        $this->setAttribute("key", $key);
        return $this;
    }



    public function add(string $label, string $prop)
    {
        $col = new RTableColumn();
        $col->setAttribute("label", $this->translator->translate($label));
        $col->setAttribute("prop", $prop);

        $this->append($col);

        return $col;
    }

    /**
     * @param array|string $url
     */
    public function addDropdown(string $label, $url, array $param = [])
    {

        if (is_array($url)) {
            $url = (string) $url[0]->path() . "/" . $url[1] . "?" . http_build_query($param);
        }

        $item = new Element("r-table-dropdown-item");
        $item->setAttribute("label", $label);
        $item->setAttribute("url", $url);


        $this->dropdown->append($item);
    }

    public function order(string $name, string $dir = null)
    {
        $this->setAttribute("default-sorting", $name);
        $this->setAttribute("default-sorting-order", $dir);
        return $this;
    }
}
