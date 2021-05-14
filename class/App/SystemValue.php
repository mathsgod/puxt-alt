<?php

namespace App;

class SystemValue extends Model
{
    public static function _(string $name, string $lang = 'en')
    {
        return self::Query(
            [
                "language" => $lang,
                "name" => $name
            ]
        )->first();
    }

    public function __toString()
    {
        return $this->value;
    }

    public function values()
    {
        // test json
        $v = $this->value;
        if (!preg_match(
            '/[^,:{}\\[\\]0-9.\\-+Eaeflnr-u \\n\\r\\t]/',
            preg_replace('/"(\\.|[^"\\\\])*"/', '', $v)
        )) {
            return json_decode($v, true);
        }

        $svl = explode(chr(10), $this->value);
        $vals = array();
        foreach ($svl as $sv) {
            $sv = trim($sv);
            if ($sv == "") continue;
            if (strstr($sv, "|")) {
                $l = explode("|", $sv);
                $vals[$l[0]] = trim($l[1]);
            } else {
                $vals[$sv] = $sv;
            }
        }

        return $vals;
    }
}
