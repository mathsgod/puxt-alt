<?php

namespace App;

class Plugin
{
    public $name;
    public $path;
    public $setting;
    private static $plugins = [];
    public static function Load($name, App $app)
    {
        $p = new Plugin($name, $app);
        self::$plugins[] = $name;

        foreach ($p->setting["php"] as $php) {
            require_once($p->path . "/" . $php);
        }

        return $p;
    }

    public function __construct($name, App $app)
    {
        extract($app->pathInfo());

        $this->name = $name;
        $path = [];

        $path[] = [$composer_root . "/vendor/$name", "$composer_base/vendor/$name"];
        $path[] = [$composer_root . "/" . $name, "$composer_base/$name"];

        $path[] = [$cms_root . "/plugins/{$name}.*", "plugins", "version"];
        $path[] = [$cms_root . "/plugins/{$name}-*", "plugins", "version"];

        $path[] = [$system_root . "/plugins/{$name}", $system_base . "/plugins/$name"];
        $path[] = [$system_root . "/plugins/{$name}.*", $system_base . "/plugins", "version"];
        $path[] = [$system_root . "/plugins/{$name}-*", $system_base . "/plugins", "version"];
        $path[] = [$system_root . "/AdminLTE/plugins/{$name}", $system_base . "/AdminLTE/plugins/$name"];

        $config = $app->plugins;

        if ($config[$name]) {
            $this->setting = $config[$name];
        }

        if ($setting = parse_ini_file($cms_root . "/plugins.ini", true)[$name]) {
            $this->setting = $setting;
        }


        $language = $app->user->language;
        $this->setting["js"] = array_map(function ($js) use ($language) {
            return str_replace("{language}", $language, $js);
        }, $this->setting["js"]);


        $found = false;
        foreach ($path as $p) {
            $r = glob($p[0], GLOB_ONLYDIR);
            rsort($r);

            if ($f = $r[0]) {

                $this->path = $f;
                $this->base = $p[1];

                if ($p[2] == "version") {
                    $this->base .= "/" . basename($f);
                }

                $found = true;
                break;
            }
        }

        //check js file exist
        $this->setting["js"] = array_filter($this->setting["js"], function ($js) {
            return is_readable($this->path . "/" . $js);
        });

        if (!$found) {
            throw new \Exception($name . " not found");
        }
 
    }

    public function csss()
    {
        $csss = [];
        foreach ($this->setting["css"] as $css_f) {
            if (file_exists($this->path . "/" . $css_f)) {
                $csss[] = $this->base . "/" . $css_f;
            }
        }

        return $csss;
    }

    public function jss($language = null)
    {
        $jss = [];
        foreach ($this->setting["js"] as $js) {
            $f = str_replace("{language}", $language, $js);

            if (file_exists($this->path . "/" . $f)) {
                $jss[] = $this->base . "/" . $f;
            }
        }

        return $jss;
    }
}
