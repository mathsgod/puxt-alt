<?php

namespace App;

class AuthLock extends Model
{
    public static function IsLock(): bool
    {
        $ip = $_SERVER["REMOTE_ADDR"];
        if ($ip) {
            $w[] = ["ip=?", $ip];
        }
        $w[] = "value>=3";
        $w[] = "date_add(`time`,Interval 180 second) > now()";
        return self::Count($w);
    }

    public static function Add()
    {
        $ip = $_SERVER["REMOTE_ADDR"];
        if (!$ip) {
            return;
        }
        $a = AuthLock::Query([
            "ip" => $ip
        ])->first();
        if ($a) {
            $a->value++;
        } else {
            $a = new AuthLock();
            $a->ip = $ip;
            $a->value = 0;
        }
        $a->time = date("Y-m-d H:i:s");

        //no checking
        $a->save();
    }

    public static function Clear()
    {
        $ip = $_SERVER["REMOTE_ADDR"];
        if (!$ip) {
            return;
        }
        if ($a = self::First(["ip" => $ip])) {
            $a->delete();
        }
    }
}
