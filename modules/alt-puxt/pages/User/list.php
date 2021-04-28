{{table}}

<?php

return [
    "get" => function ($context) {
        $this->table = $context->alt->createRTable();
    }
];
