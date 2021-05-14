<?php

namespace App;

class Composer
{
    public $app;
    public function __construct(App $app)
    {
        $this->app = $app;
    }

    public function installed(): array
    {
        return $this->lockConfig()["packages"] ?? [];
    }

    public function package(string $name)
    {
        foreach ($this->installed() as $package) {
            if ($package["name"] == $name) {
                return $package;
            }
        }
        return null;
    }


    public function hasPackage($package): bool
    {
        foreach ($this->installed() as $p) {
            if ($p["name"] == $package) {
                return true;
            }
        }
        return false;
    }

    public function path()
    {
        $p = $this->app->pathInfo();
        return $p["composer_root"];
    }

    public function lockConfig()
    {
        if (file_exists($file = $this->path() . "/composer.lock")) {
            return json_decode(file_get_contents($file), true);
        }
        return [];
    }
}
