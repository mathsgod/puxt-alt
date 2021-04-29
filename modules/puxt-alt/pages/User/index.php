{{tab|raw}}

<alt-tabs>
    <alt-tab label="All User" src="User/list"></alt-tab>
    <alt-tab label="All User2" src="User/list_1"></alt-tab>
    <alt-tab label="All User3" src="User/list_2"></alt-tab>
</alt-tabs>

<?php

return [
    "get" => function (ALT $context) {
        $tab = $context->createTab();
        //   $tab->add("User", "list");
        $this->tab = $tab;
    },
];
