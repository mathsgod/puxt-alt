{{table|raw}}
<?php
return [
    "get" => function (ALT $context) {
        //if (!$_GET["data"]) {
        $rt = $context->createRTable();
        $rt->add("Name", "name");
        $this->table = $rt;
        //}else{
        //    return ["data"];
        //}
    },
    "action" => [
        "ds" => function () {
            return ["A"];
        }
    ]
];
