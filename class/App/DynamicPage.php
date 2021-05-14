<?php

namespace App;

//Created by Raymond Chong
//Date: 2020-06-26
class DynamicPage extends Model
{
    public static function _(string $name)
    {
        return self::Query(["name" => $name])->first();
    }
    /*
    public function content()
    {
        $loader = new \Twig\Loader\FilesystemLoader(self::$_app->document_root);
        $twig = new \Twig\Environment($loader, ["debug" => true]);
        $twig->addExtension(new Twig\Dynamic\Extension());
        Twig\Dynamic\Extension::SetData($this->data);

        $template = $twig->load("/pages/" . $this->path);

        return $template->render([]);
    }*/
}
