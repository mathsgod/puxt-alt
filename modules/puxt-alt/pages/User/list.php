{{table|raw}}
<?php

use App\User;

return [
    "get" => function (ALT $context) {
        $rt = $context->createRTable();
        $rt->setAttribute("remote", "User/list?_action=ds");
        $rt->addView();
        $rt->add("Username", "username");
        $rt->add("First name", "first_name");
        $rt->add("Last name", "last_name");
        $this->table = $rt;
    },
    "action" => [
        "ds" => function (ALT $context) {

            $rt = $context->createRTableResponse();
            $rt->source = User::Query();
            return $rt;
        }
    ]
];
