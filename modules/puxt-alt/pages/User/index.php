<alt-tabs>
    <alt-tab label="All User" link="User/list" active></alt-tab>
    <alt-tab label="All User2" link="User/list_1"></alt-tab>
    <alt-tab label="All User3" link="User/list_2"></alt-tab>
</alt-tabs>

<?php

return [
    "get" => function (ALT $context) {
        $tab = $context->createTab();
        //   $tab->add("User", "list");
        $this->tab = $tab;
    },
];
