<?php

namespace App\UI;

use P\HTMLTextAreaElement;

class CKEditor extends HTMLTextAreaElement
{
    public function getConfig()
    {
        $config = $this->getAttribute(":config");
        return json_decode($config, true);
    }

    public function setConfig(array $config)
    {
        return $this->setAttribute(":config", json_encode($config));
    }

    public function addConfig(string $name, string $value)
    {
        $config = $this->getConfig();
        $config[$name] = $value;
        return $this->setConfig($config);
    }
}
