<?php

namespace ALT;

interface TranslatorInterface
{
    public function translate(string $name): string;
}
