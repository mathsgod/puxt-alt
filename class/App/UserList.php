<?php

namespace App;

class UserList extends Core\UserList
{
    use ModelTrait;

    public function canDelete()
    {
        if ($this->usergroup_id == 1) {
            if (!self::$_app->user->is("Administrators")) {
                return false;
            }
        }
        if ($this->user_id == 1) {
            return false;
        }
        if ($this->user_id == 3) {
            return false;
        }
        return parent::canDelete();
    }
}
