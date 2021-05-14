<?php

namespace App;

trait ModelTrait
{
    public static $_app;

    /**
     * @return User|null
     */
    public function createdBy()
    {
        if ($this->created_by) {
            return new User($this->created_by);
        }
    }

    /**
     * @return User|null
     */
    public function updatedBy()
    {
        if ($this->updated_by) {
            return new User($this->updated_by);
        }
    }

    private function _acl_allow($action = []): bool
    {

        $rc = new \ReflectionClass(get_class($this));
        $class = $rc->getShortName();

        //--- deny ---
        if (array_intersect($action, self::$_app->acl["action"]["deny"][$class] ?? [])) {
            return false;
        }

        //creator owner
        if (array_intersect($action, self::$_app->acl["special_user"][1]["deny"][$class] ?? [])) {
            if (self::$_app->user_id == $this->created_by) {
                return false;
            }
        }

        //creator group
        if (array_intersect($action, self::$_app->acl["special_user"][2]["deny"][$class] ?? [])) {
            if (array_intersect(self::$_app->usergroup_id, $this->creator_group ?? [])) {
                return false;
            }
        }

        //everyone
        if (array_intersect($action,  self::$_app->acl["special_user"][3]["deny"][$class] ?? [])) {
            return false;
        }

        if (array_intersect($action, self::$_app->acl["action"]["allow"][$class] ?? [])) {
            return true;
        }

        //creator owner
        if (array_intersect($action, self::$_app->acl["special_user"][1]["allow"][$class] ?? [])) {
            if (self::$_app->user_id == $this->created_by) {
                return true;
            }
        }

        //creator group
        if (array_intersect($action, self::$_app->acl["special_user"][1]["allow"][$class] ?? [])) {
            if (array_intersect(self::$_app->usergroup_id, $this->creator_group ?? [])) {
                return true;
            }
        }

        //everyone
        if (array_intersect($action,  self::$_app->acl["special_user"][3]["allow"][$class] ?? [])) {
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function canReadBy(User $user)
    {
        if ($user->isAdmin()) {
            return true;
        }

        return  $this->_acl_allow(["FC", "R"]);
    }

    /**
     * @return bool
     */
    public function canUpdateBy(User $user)
    {
        if ($user->isAdmin()) {
            return true;
        }

        return  $this->_acl_allow(["FC", "U"]);
    }

    /**
     * @return bool
     */
    public function canDeleteBy(User $user)
    {

        if ($user->isAdmin()) {
            return true;
        }
        return  $this->_acl_allow(["FC", "D"]);
    }

    public function id()
    {
        $key = $this->_key();
        return $this->$key;
    }


    /**
     * @return string
     */
    public function uri($a = null)
    {
        $reflect = new \ReflectionClass($this);
        $uri = $reflect->getShortName();
        if ($this->id()) {
            $uri .= "/" . $this->id();
        }
        if (isset($a)) {
            $uri .= "/" . $a;
        }
        return $uri;
    }

    public function __call($function, $args)
    {
        $class = get_class($this);

        //check const
        $c = new \ReflectionClass($class);
        if ($const = $c->getConstants()) {

            $decamlize = function ($string) {
                return strtolower(preg_replace(['/([a-z\d])([A-Z])/', '/([^_])([A-Z][a-z])/'], '$1_$2', $string));
            };
            $field = $decamlize($function);

            if (array_key_exists(strtoupper($field), $const)) {
                return $const[strtoupper($field)][$this->$field];
            }

            if (array_key_exists($function, $const)) {
                return $const[$function][$this->$field];
            }
        }
        return parent::__call($function, $args);
    }


    public function save()
    {
        $key = $this->_key();

        if (!$this->$key) { //insert
            $action = "C";
            if (property_exists($this, "created_by")) {
                $this->created_by = self::$_app->user_id;
            }

            if (property_exists($this, "creator_group")) {
                $this->creator_group = self::$_app->usergroup_id;
            }
        } else {
            $action = "U";
            if (property_exists($this, "updated_by")) {
                $this->updated_by = self::$_app->user_id;
            }
        }

        if ($action == "C") {
            $result = parent::save();
            EventLog::Log($this, $action);
        } else {
            EventLog::Log($this, $action);
            $result = parent::save();
        }
        return $result;
    }

    public function delete()
    {
        EventLog::LogDelete($this);
        return parent::delete();
    }
}
