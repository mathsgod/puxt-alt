<?php

namespace App;

class ACL extends Model
{
    const ACTION = ["FC", "C", "R", "U", "D"];
    const TYPE = ["Normal", "Regexp"];
    const SPECIAL_USER = [1 => "CREATOR OWNER", 2 => "CREATOR GROUP", 3 => "EVERYONE"];

    public function User()
    {
        return new User($this->user_id);
    }

    public function SpecialUser()
    {
        return self::SPECIAL_USER[$this->special_user];
    }

    public function path()
    {
        if ($this->path == "") {
            return $this->module;
        } else {
            return $this->module . "/" . $this->path;
        }
    }

    public function UserName()
    {
        if ($this->user_id) {
            return $this->User();
        } elseif ($this->usergroup_id) {
            return $this->UserGroup()->name;
        } else {
            return self::SPECIAL_USER[$this->special_user];
        }
    }

    public function Value()
    {
        if ($this->code == "") {
            return $this->value;
        }
        // API::output($this);
        $func = "_ACL_func_" . $this->acl_id;
        $eval = <<<EOT
function {$func}(){
    ?>{$this->code}<?php}
EOT;
        eval($eval);

        if ($func()) {
            return $this->value;
        }
    }
}
