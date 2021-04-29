<?php

use App\Model;

return function ($options) {

    $this->puxt->config["head"]["base"] = ["href" => "/"];

    $this->puxt->config["head"]["link"][] = [
        "rel" => "stylesheet",
        "href" => "https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback"
    ];

    $this->puxt->config["head"]["link"][] = [
        "rel" => "stylesheet",
        "href" => "plugins/fontawesome-free/css/all.min.css"
    ];
    $this->puxt->config["head"]["link"][] = [
        "rel" => "stylesheet",
        "href" => "alt/css/adminlte.min.css"
    ];

    $this->puxt->config["head"]["link"][] = [
        "rel" => "stylesheet",
        "href" => "css/element-ui/index.css"
    ];

    $this->puxt->config["head"]["link"][] = [
        "rel" => "stylesheet",
        "href" => "css/main.css"
    ];


    $this->puxt->config["head"]["script"][] = ["src" => "plugins/jquery/jquery.min.js", "defer" => true];
    $this->puxt->config["head"]["script"][] = ["src" => "plugins/bootstrap/js/bootstrap.bundle.min.js", "defer" => true];
    $this->puxt->config["head"]["script"][] = ["src" => "alt/js/adminlte.min.js", "defer" => true];

    $this->puxt->config["head"]["script"][] = ["src" => "plugins/vue/vue.js"];
    $this->puxt->config["head"]["script"][] = ["src" => "dist/puxt-alt.umd.js"];




    $this->puxt->config["body_attrs"][] = "hold-transition";
    $this->puxt->config["body_attrs"][] = "sidebar-mini";

    $this->puxt->hook('ready', function ($puxt) {


        $alt = $puxt->context = new ALT($puxt->context);
        $alt->db = $puxt->context->db;
        \App\Core\Model::$_db = $alt->db;


        $this->addLayout("modules/puxt-alt/layouts/default", "default");

        $files[] = __DIR__ . "/pages/" . $puxt->context->route->path . "index.php";
        $files[] = __DIR__ . "/pages/" . $puxt->context->route->path . "/index.php";
        $files[] = __DIR__ . "/pages/" . $puxt->context->route->path . ".php";

        foreach ($files as $file) {
            if (file_exists($file)) {
                $this->puxt->config["dir"]["pages"] = "modules/puxt-alt/pages";
                break;
            }
        }
    });
};
