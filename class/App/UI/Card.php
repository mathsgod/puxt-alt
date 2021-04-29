<?php

namespace App\UI;

use App\Page;
use P\HTMLElement;
use Vue\Scriptable;

/**
 * @property CardHeader $header
 * @property CardBody $body
 * @property CardFooter $footer
 * @property bool $outline
 */
class Card extends HTMLElement
{
    const ATTRIBUTES = [
        "dataUrl" => ["name" => "data-url"],
        "dataUri" => ["name" => "data-uri"],
        "collapsible" => ["name" => ":collapsible", "type" => "json"],
        "collapsed" => ["name" => ":collapsed", "type" => "json"],
        "pinable" => ["name" => ":pinable", "type" => "json"],
        "draggable" => ["name" => ":draggable", "type" => "json"],
        "outline" => ["name" => "outline", "type" => "bool"]
    ] + parent::ATTRIBUTES;

    protected $page;
    public static $NUM = 0;
    public $app;

    public function __construct()
    {
        parent::__construct("card");

        $this->setAttribute("id", "_card_" . self::$NUM);

        self::$NUM++;
    }

    public function collapsible(bool $collapsible)
    {
        $this->header;
        $this->collapsible = $collapsible;
        return $this;
    }

    public function pinable(bool $pinable)
    {
        $this->pinable = $pinable;
        return $this;
    }

    public function __get($name)
    {
        if ($name == "header") {
            $this->header = new CardHeader($this->page);
            $this->prependChild($this->header);
            return $this->header;
        }

        if ($name == "body") {
            $this->body = new CardBody($this->page);
            $this->appendChild($this->body);
            return $this->body;
        }

        if ($name == "footer") {
            $this->footer = new CardFooter($this->page);
            $this->appendChild($this->footer);
            return $this->footer;
        }

        return parent::__get($name);
    }

    public function body()
    {
        return p($this->body);
    }

    public function header($title = null)
    {
        if ($title) {
            $this->header->title = $title;
        }
        return p($this->header);
    }

    public function addRForm($data = null)
    {
        $form = new \ALT\R\Form();
        $form->setPage($this->page);
        $form->setAttribute("method", "POST");
        $form->setAttribute("label-width", "auto");
        if ($data) {
            $form->setData($data);
        }
        $this->body->append($form);
        return $form;
    }

    public function addForm($data = null)
    {
        $form = new \ALT\R\Form();
        $form->setPage($this->page);
        $form->setAttribute("method", "POST");
        $form->setAttribute("label-width", "auto");
        if ($data) {
            $form->setData($data);
        }
        $this->body->append($form);
        return $form;
        /*     $form = new \ALT\Element\Form();
        $form->setPage($this->page);

        if ($data) {
            $form->setData($data);
        }
        $this->body->append($form);

        return $form; */
    }

    public function script()
    {

        $script = new \Vue\Script();
        $script->el = "#" . $this->getAttribute("id");

        foreach ($this->body->childNodes as $child) {
            if ($child instanceof Scriptable) {
                $script = $script->merge($child->script());
            }
        }
        return $script;
    }
}
