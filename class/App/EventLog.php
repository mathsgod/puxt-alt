<?php
// Created By: Raymond Chong
// Created Date: 19/3/2010
// Last Updated:
namespace App;

class EventLog extends Model
{
    const STATUS = ["Normal", "Restored"];

    public function getDifferent()
    {
        $source = $this->source;
        $target = $this->target;
        $a = array_diff_assoc($source, $target);
        $b = array_diff_assoc($target, $source);

        $diff = [];
        foreach ($a as $name => $value) {
            $diff[] = [
                "field" => $name,
                "from" => $b[$name],
                "to" => $value
            ];
        }

        return $diff;
    }

    public static function LogDelete($object)
    {
        $class = get_class($object);

        if ($object instanceof self) return;

        $r["id"] = $object->id();
        $r["user_id"] = self::$_app->user->user_id;
        $r["class"] = $class;
        $r["action"] = "Delete";
        $r["source"] = json_encode($object);
        if ($r["source"] === false) {
            $r["source"] = null;
        }
        $r["created_time"] = date("Y-m-d H:i:s");
        self::_table()->insert($r);
    }

    public static function Log($object, string $action)
    {
        $class = get_class($object);
        if ($object instanceof self) return;
        // check the module
        $rc = new \ReflectionClass($class);
        $short_name = $rc->getShortName();
        $m = self::$_app->module($short_name);
        if (!$m->log) return;

        $r["user_id"] = self::$_app->user->user_id;
        $r["class"] = $class;

        $r["id"] = $object->id();
        if ($action == "U") {
            $r["action"] = "Update";
            $c = "\\$class";
            $org = new $c($r["id"]);
            $r["source"] = json_encode($org);
            if ($r["source"] === false) {
                $r["source"] = null;
            }

            $target = json_encode($object);
            if ($target === false) {
                $r["target"] = null;
            } else {
                $r["target"] = $target;
            }
        } else {
            $r["action"] = "Insert";
            $target = json_encode($object);
            if ($target === false) {
                $r["target"] = null;
            } else {
                $r["target"] = $target;
            }
        }
        $r["created_time"] = date("Y-m-d H:i:s");
        self::_table()->insert($r);
    }

    public function restoreObject(): bool
    {
        $data = $this->source;

        $class = $this->class;

        $table = $class::_table();
        $table->insert($data);

        $this->status = 1;
        $this->save();
        return true;
    }
}
