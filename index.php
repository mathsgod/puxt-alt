<?php

error_reporting(E_ALL & ~E_NOTICE);
require_once("vendor/autoload.php");
$app = new PUXT\App(__DIR__);
$app->run();
