<?php
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
        "href" => "dist/css/adminlte.min.css"
    ];


    $this->puxt->config["head"]["script"][] = ["src" => "plugins/jquery/jquery.min.js", "defer" => true];
    $this->puxt->config["head"]["script"][] = ["src" => "plugins/bootstrap/js/bootstrap.bundle.min.js", "defer" => true];
    $this->puxt->config["head"]["script"][] = ["src" => "dist/js/adminlte.min.js", "defer" => true];

    $this->puxt->config["body_attrs"][] = "hold-transition";
    $this->puxt->config["body_attrs"][] = "sidebar-mini";

    $this->puxt->hook('ready', function ($puxt) {

        $puxt->context->alt = new ALT();

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
