<?php

namespace App;

interface TranslatorInterface
{
    public function translate(string $name): string;
}
