{{tabs}}
<?php

return [
    "get" => function ($context) {
        $this->tabs = $context->alt->createTab();
    }
];
