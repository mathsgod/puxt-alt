<?php

namespace App;

use Google\Authenticator\GoogleAuthenticator;

class User extends Core\User
{
    /**
     * @var int
     */
    public $user_id;
    public $username;
    public $first_name;

    use ModelTrait {
        canUpdate as protected canUpdate2;
        canDelete as protected canDelete2;
    }

    public function __construct($id = null)
    {
        parent::__construct($id);
        if (!$id) {
            $this->join_date = date("Y-m-d");
            $this->language = "en";
        }
    }

    public function checkCode(string $code): bool
    {
        $g = new GoogleAuthenticator();
        return $g->checkCode($this->secret, $code);
    }

    public function setting(): array
    {
        return json_decode($this->setting, true) ?? [];
    }

    public function canRead(): bool
    {
        $user = self::$_app->user;
        if ($user->isAdmin()) { //admin can update all
            return true;
        }

        if ($user->user_id == $this->user_id) { //update itself
            return true;
        }

        if ($user->isPowerUser()) {
            if ($this->isAdmin()) {
                return false;
            }
        }
        if ($this->isGuest()) { //cannot update guest
            return false;
        }

        return $this->canUpdate2();
    }

    public function canUpdate(): bool
    {
        $user = self::$_app->user;
        if ($user->isAdmin()) { //admin can update all
            return true;
        }

        if ($user->user_id == $this->user_id) { //update itself
            return true;
        }

        if ($user->isPowerUser()) {
            if ($this->isAdmin()) {
                return false;
            }
        }
        if ($this->isGuest()) { //cannot update guest
            return false;
        }

        return $this->canUpdate2();
    }

    public function canDelete(): bool
    {
        $user = self::$_app->user;
        if ($this->isGuest()) { //cannot delete guest
            return false;
        }


        if ($user->user_id == $this->user_id) { //cannot delete myself
            return false;
        }

        if ($user->isAdmin()) { //admin
            return true;
        }

        if ($user->isPowerUser()) {
            if ($this->isAdmin()) {
                return false;
            }
        }

        return $this->canDelete2();
    }

    public function createUserLog(string $result = null)
    {
        $r["user_id"] = $this->user_id;
        $r["login_dt"] = date("Y-m-d H:i:s");
        $r["ip"] = $_SERVER['REMOTE_ADDR'];
        $r["result"] = $result;
        $r["user_agent"] = $_SERVER['HTTP_USER_AGENT'];
        UserLog::_table()->insert($r);
    }

    public function logout()
    {
        $o = UserLog::Query([
            "user_id" => $this->user_id
        ])->orderBy("userlog_id desc")->first();

        if ($o) {
            $o->logout_dt = date("Y-m-d H:i:s");
            $o->save();
        }
    }

    public function online()
    {
        if ($this->isUser()) {
            $this->update(["last_online" => date("Y-m-d H:i:s")]);
        }
    }

    public function offline()
    {
    }

    public function isOnline(): bool
    {
        $time = strtotime($this->last_online);

        if (time() - $time > 300) {
            return false;
        }
        return true;
    }

    public function sendPassword(App $app): string
    {
        $password = Util::GeneratePassword();
        $e_pwd = password_hash($password, PASSWORD_DEFAULT);

        $ret = $this->update(["password" => $e_pwd]);


        $content = $app->config["user"]["forget pwd email/content"];
        $content = str_replace("{username}", $this->username, $content);
        $content = str_replace("{password}", $password, $content);

        // Send Mail
        $mm = $app->createMail();
        $mm->Subject = $app->config["user"]["forget pwd email/subject"];
        $mm->msgHTML($content);
        $mm->setFrom("admin@" . $app->config["user"]["domain"]);
        $mm->addAddress($this->email);
        $mm->Send();
        return $password;
    }

    public function UserGroup()
    {
        return UserGroup::Query()->where("usergroup_id in (select usergroup_id from UserList where user_id=:user_id)", ["user_id" => $this->user_id]);
    }
}
