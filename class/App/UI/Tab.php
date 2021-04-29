<?php

namespace App\UI;

use App\Page;

class Tab extends Card
{
    public $navs;
    public $content;
    public static $_TabID = 0;
    public static $_MyTab = 0;
    protected $page;

    public function __construct()
    {
        parent::__construct();
        return;
        $this->classList->add("card-outline card-outline-tabs border-top-0");


        $this->navs = $this->ownerDocument->createElement("ul");
        $this->navs->classList->add("nav");
        $this->navs->classList->add("nav-tabs");

        $this->header->append($this->navs);
        $this->header->classList->add("p-0 border-bottom-0");


        $this->content =  $this->ownerDocument->createElement("div");
        $this->content->classList[] = "tab-content";
        $this->body->append($this->content);
        $this->body->classList->add("p-1 m-0");

        self::$_MyTab++;
        //$module = $page->module();
        $this->setAttribute("prefix", $prefix);

        /* if ($module) {
            $this->setAttribute("data-cookie", $page->path() . "/$prefix" . self::$_MyTab);
        } */

        $this->classList[] = "my_tab";
    }

    public function collapsible()
    {
        return;
        $li = p("li")->addClass("pull-right");
        $a = p("a")->attr("href", "#")->html('<i class="fa fa-minus"></i>')->attr('data-widget', "collapse")->appendTo($li);
        p($this->navs)->append($li);
    }

    public function pinable()
    {
        return;
        $li = p("li")->addClass("pull-right");
        $a = p("a")->attr("href", "#")->html('<i class="fa fa-thumbtack"></i>')->appendTo($li);
        $a->attr("@click.prevent", '$emit("toggle-pin")');
        p($this->navs)->append($li);
        $this->attributes[":pinable"] = "true";
    }

    public function add(string $label, string $uri, $t = null)
    {
        //$module = $this->page->module();
        //$label = $this->page->translate($label);
        self::$_TabID++;
        $tab_id = self::$_TabID;

        if ($id = $this->page->id()) {
            $href = $module->name . "/" . $id . "/" . $uri;
        } else {
            $href = dirname($this->page->path()) . "/" . $uri;
        }

        if (isset($t)) {
            $url = parse_url($href);
            parse_str($url["query"], $query);
            $query["t"] = $t;
            $href = $url["path"] . "?" . http_build_query($query);
        }


        $url = dirname($this->page->path()) . "/" . $uri;

        if (!$this->page->app->acl($url)) {
            return;
        }
        $ti = new TabItem();

        //$li = p("li");

        $a = p("a")->attr("href", $href)->text($label)->appendTo($ti->li);
        $a->addClass("nav-link");


        $prefix = $this->getAttribute("prefix");
        $id = "tab-{$prefix}{$tab_id}";
        $a->attr("data-target", "#$id");
        $a->attr("data-toggle", "tabajax");

        p($this->navs)->append($ti->li);

        $div = p("div");
        $div->addClass("tab-pane")->attr("id", $id);
        p($this->content)->append($div);
        return $ti;
    }

    public function addLocal($label, $content)
    {
        $label = $this->page->translate($label);
        self::$_TabID++;
        $tab_id = self::$_TabID;

        $i = new TabItem();

        $a = p("a")->attr("href", "#tab-{$tab_id}")->text($label)->appendTo($i->li);
        $a->addClass("nav-link");
        $prefix = $this->getAttribute("prefix");
        $id = "tab-{$prefix}{$tab_id}";
        $a->attr("data-target", "#$id");
        $a->attr("data-toggle", "tab");
        p($this->navs)->append($i->li);

        p($i->div)->attr("id", $id)->append($content);
        p($this->content)->append($i->div);
        return $i;
    }
}
