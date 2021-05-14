<?php

namespace App;
use ALT\NavItem;

class Module 
{
    public static $_app;

    public $group;
    public $icon = "far fa-circle nav-icon";
    public $class;
    public $menu = [];
    public $sequence = PHP_INT_MAX;

    public $show_index = true;
    public $show_list = false;
    public $show_create = false;
    public $show_update = true;
    public $show_delete = true;

    public $log = true;

    public $hide = false;
    public $translate = [];

    public function __construct(string $name, array $config = [])
    {
        $this->name = $name;
        $this->class = $name;

        foreach ($config as $k => $v) {
            $this->$k = $v;
        }
    }

    public function loadConfig(array $config = [])
    {
        foreach ($config as $k => $v) {
            $this->$k = $v;
        }
    }

    public function __toString()
    {
        return $this->class;
    }

    public function getAction()
    {
        $app = self::$_app;

        $page = $app->config["system"]["page"];
        if (!$page) {
            $page = "pages";
        }

        $pi = $app->pathInfo();

        $name = $this->name;
        if (file_exists($file = $pi["cms_root"] . "/pages/" . $name)) {
            foreach (glob($file . "/*.php") as $p) {
                $pi = pathinfo($p);
                $action[] = $pi;
            }
        }
        if (file_exists($file = $pi["system_root"] . "/pages/" . $name)) {
            foreach (glob($file . "/*.php") as $p) {
                $pi = pathinfo($p);
                $action[] = $pi;
            }
        }

        return $action;
    }

    public function getMenuLink(string $path): array
    {
        if ($this->hide) {
            return [];
        }
        $links = [];


        if ($this->show_list || $this->show_index) {
            $link = new NavItem();
            $link->label = $this->translate("List");
            $link->link = $this->name;
            $link->icon = "fa fa-fw fa-list";
            $link->active = ($path == $this->name);
            $link->keyword = "";
            $links[] = $link;
        }

        if ($this->show_create) {
            if (self::$_app->allowAction("C", $this->class)) {
                $link = new NavItem();
                $link->label = $this->translate("Add");
                $link->link = $this->name . "/ae";
                $link->icon =  "fa fa-fw fa-plus";
                $link->active = ($path == $this->name . "/ae");
                $link->keyword = "";
                $links[] = $link;
            }
        }


        foreach ($this->menu as $k => $v) {

            if (is_array($v)) {
                $link = new NavItem();
                $link->label = $this->translate($k);
                $link->link = $v["link"];
                $link->icon = $v["icon"];
                $link->active = ($path == $v["link"]);
                $link->target = $v["target"];
                $link->keyword = $this->translate($k);
                $link->badge = $v["badge"];
                $links[] = $link;
            } else {

                $link = new NavItem();
                $link->label = $this->translate($k);
                $link->link = $v;
                $link->icon = "fa fa-fw fa-link";
                $link->active = ($path == $v);
                $link->keyword = $this->translate($k);
                $links[] = $link;
            }
        }

        return $links;
    }

    public function translate(string $text): string
    {
        $lang = self::$_app->user->language;
        if ($this->translate[$lang][$text]) {
            return $this->translate[$lang][$text];
        }
        return self::$_app->translate($text);
    }

    public function keyword()
    {
        return $this->name . " " . $this->translate($this->name);
    }
}
