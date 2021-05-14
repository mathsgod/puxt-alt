<?php

namespace App;

class Alert
{
    public function info(string $message, string $description=null)
    {
        $_SESSION["app"]["message"][] = [
            "type" => "info",
            "message" => $message,
            "description" => $description,
            "icon" => "icon fas fa-info"
        ];
    }

    public function success(string $message, string $description=null)
    {
        $_SESSION["app"]["message"][] = [
            "type" => "success",
            "message" => $message,
            "description" => $description,
            "icon" => "icon fas fa-check"
        ];
    }

    public function danger(string $message, string $description=null)
    {
        $_SESSION["app"]["message"][] = [
            "type" => "danger",
            "message" => $message,
            "description" => $description,
            "icon" => "icon fas fa-ban"
        ];
    }

    public function warning(string $message, string $description=null)
    {
        $_SESSION["app"]["message"][] = [
            "type" => "warning",
            "message" => $message,
            "description" => $description,
            "icon" => "icon fas fa-exclamation-triangle"
        ];
    }
}
