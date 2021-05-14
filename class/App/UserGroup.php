<?php

namespace App;

class UserGroup extends Core\UserGroup
{
    use ModelTrait {
        canUpdate as protected canUpdate2;
        canDelete as protected canDelete2;
    }

    public function User()
    {
        return $this->UserList()->map(function ($o) {
            return $o->User();
        });
    }

    private static $_CACHE;
    /**
     * @return \App\UserGroup
     */
    public static function _($name)
    {
        if (isset(self::$_CACHE[$name])) {
            return self::$_CACHE[$name];
        }

        self::$_CACHE[$name] = parent::_($name);
        
        return self::$_CACHE[$name];
    }



    public function __toString()
    {
        return $this->name;
    }

    public function canUpdate()
    {
        if ($this->usergroup_id <= 4) return false;
        return $this->canUpdate2();
    }

    public function canDelete()
    {
        if ($this->usergroup_id <= 4) return false;
        if ($this->UserList->count()) return false;
        return $this->canDelete2();
    }
}
