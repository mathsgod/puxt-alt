<?php

namespace App\UI;

use \Box\Spout\Writer\WriterFactory;
use \Box\Spout\Common\Type;

use JsonSerializable;
use Exception;
use Vue\Scriptable;

class Row
{
    public $style = null;
    public $class = null;

    public function style($callback)
    {
        $this->style = $callback;
        return $this;
    }

    public function addClass($callback)
    {
        $this->class = $callback;
        return $this;
    }

    public function getData($obj)
    {
        $r = [];
        if ($this->style) {
            $r["style"] = call_user_func($this->style, $obj);
        }
        if ($this->class) {
            $r["class"] = call_user_func($this->class, $obj);
        }
        return $r;
    }
}

class RTResponse implements JsonSerializable
{
    public $fields = [];
    public $source = null;

    public $_columns = [];
    public $page = 1;
    public $length;
    public $key;
    public $row;

    public $columns = [];

    public function __construct()
    {
        $this->draw = $_GET["draw"];
        $this->request["columns"] = $_GET["columns"];
        $this->order = $_GET["order"];
        $this->page = intval($_GET["page"]);
        $this->length = intval($_GET["length"]);
        $this->search = $_GET["search"];
        $this->row = new Row();

        foreach ($this->request["columns"] as $column) {
            if ($column["name"] == "__view__") {
                $this->addView();
            }

            if ($column["name"] == "__edit__") {
                $this->addEdit();
            }

            if ($column["name"] == "__del__") {
                $this->addDel();
            }
        }
    }

    public function key($key)
    {
        $this->key = $key;
        return $this;
    }

    public function where()
    {
        $where = [];
        return $where;
    }

    public function addEdit()
    {
        $that = $this;
        $c = new Column();
        $c->title = "";
        $c->type = "html";
        $c->data = "__edit__";
        $c->name = "__edit__";
        $c->className[] = "text-center";
        $c->width = "1px";
        $c->descriptor[] = function ($obj) use ($that) {
            if (is_array($obj)) {
                if ($obj["canUpdate"]) {
                    $uri = $that->model . "/" . $obj[$that->key] . "/ae";
                    $a = html("a")->class("btn btn-xs btn-warning text-white")->href($uri);
                    $a->i->class("fa fa-pencil-alt fa-fw");
                    return $a;
                }
                return;
            }

            if (!$obj->canUpdate()) {
                return;
            }
            $a = html("a")->class("btn btn-xs btn-warning text-white")->href($obj->uri("ae"));
            $a->i->class("fa fa-pencil-alt fa-fw");
            return $a;
        };
        $this->_columns["__edit__"] = $c;
        return $c;
    }

    public function addView()
    {
        $that = $this;
        $c = new Column();
        $c->title = "";
        $c->type = "html";
        $c->data = "__view__";
        $c->name = "__view__";
        $c->className[] = "text-center";
        $c->width = "1px";
        $c->descriptor[] = function ($obj) use ($that) {

            if (is_array($obj)) {
                if ($obj["canView"]) {
                    $uri = $that->model . "/" . $obj[$that->key] . "/v";
                    $a = html("a")->class("btn btn-xs btn-info")->href($uri);
                    $a->i->class("fa fa-search fa-fw");
                    return $a;
                }
                return;
            }
            if (!$obj->canRead()) {
                return;
            }
            $a = html("a")->class("btn btn-xs btn-info")->href($obj->uri("v"));
            $a->i->class("fa fa-search fa-fw");
            return $a;
        };
        $this->_columns["__view__"] = $c;
        return $c;
    }


    public function addDel()
    {

        $c = new Column();
        $c->title = "";
        $c->type = "delete";
        $c->data = "__del__";
        $c->name = "__del__";
        $c->width = "1px";
        $c->className[] = "text-center";
        $that = $this;
        $c->descriptor[] = function ($obj) use ($that) {
            if (is_array($obj)) {
                if ($obj["canView"]) {
                    return $that->model . "/" . $obj[$that->key];
                }
                return;
            }

            if (!$obj->canDelete()) {
                return;
            }
            return $obj->uri();
        };
        $this->_columns["__del__"] = $c;
        return $c;
    }

    public function addSubRow($name, $func, $key)
    {
        $path = $func[0]->path();

        $url = $path . "/$func[1]";
        $c = new Column();
        $c->title = "";
        $c->type = "sub-row";
        $c->name = $name;
        $c->url = $url;
        $c->key = $key;
        $this->_columns[$name] = $c;
        return $c;
    }

    public function add($name, $getter)
    {
        $c = new Column();
        $c->name = $name;
        $c->descriptor[] = $getter;

        $this->_columns[$name] = $c;

        return $c;
    }


    public function data()
    {
        foreach ($this->fields as $c) {
            $this->add($c, $c);
        }

        $source = $this->filteredSource();

        if ($this->page) {
            $source->limit($this->length);
            $source->offset($this->length * ($this->page - 1));
        }



        $data = [];
        foreach ($source as $obj) {
            $d = [];

            $d["__row__"] = $this->row->getData($obj);

            if (is_array($obj)) {
                $object_vars = $obj;
            } else {
                $object_vars = get_object_vars($obj);
            }

            foreach ($this->request["columns"] as $k => $c) {
                try {
                    if (array_key_exists($c["name"], $this->_columns)) {
                        $col = $this->_columns[$c["name"]];

                        if ($col->type == "sub-row") {
                            $d[$c["name"]] = ["url" => $col->url, "params" => [$col->key =>  $object_vars[$col->key]]];
                        } elseif ($col->type == "delete") {
                            if ($content = (string) $col->getData($obj, $k)) {
                                $d[$c["name"]] = ["type" => $col->type, "content" => (string) $content];
                            } else {
                                $d[$c["name"]] = null;
                            }
                        } elseif ($col->type == "text") {
                            $d[$c["name"]] = [
                                "type" => $col->type,
                                "content" => (string) $col->getData($obj, $k),
                                "value" => $col->getCellValue($obj)
                            ];
                        } elseif ($col->type == "html") {

                            $content = $col->getData($obj, $k);
                            if ($content instanceof Scriptable) {
                                $d[$c["name"]] = ["type" => "vue", "content" => (string) $content];
                            } else {
                                $d[$c["name"]] = ["type" => "html", "content" => (string) $content];
                            }
                        } else {
                            $v = $col->getData($obj, $k);

                            if (is_array($v)) {
                                $d[$c["name"]] = $v;
                            } else {
                                $d[$c["name"]] = (string) $v;
                            }
                        }
                    } elseif (array_key_exists($c["name"], $object_vars)) {
                        $d[$c["name"]] = $object_vars[$c["name"]];
                    } else {
                        $d[$c["name"]] = null;
                    }
                } catch (Exception $e) {
                    $d[$c["name"]] = $e->getMessage();
                }
            }

            if ($this->key) {
                $key = $this->key;
                $d["_key"] = $obj->$key;
            }

            $data[] = $d;
        }

        return $data;
    }

    public function recordsTotal()
    {
        return $this->source->count();
    }

    public function search()
    {
        $search = [];
        foreach ($this->request["columns"] as $k => $c) {
            $search[$c["name"]] = $c["search"]["value"];
        }
        return $search;
    }

    public function filteredSource()
    {
        $source = clone $this->source;

        foreach ($this->order as $o) {
            $column = $this->_columns[$o["name"]];
            if ($column->order) {
                $source->orderBy($column->order . " " . $o["dir"]);
            } elseif ($column->sortCallback) {
                $source->orderBy(call_user_func($column->sortCallback) . " " . $o["dir"]);
            } else {
                $source->orderBy([$o["name"] => $o["dir"]]);
            }
        }

        foreach ($this->request["columns"] as $k => $c) {
            $column = $this->_columns[$c["name"]];
            $value = $c["search"]["value"];

            if ($value !== null && $value !== "") {

                if ($column->searchCallback) {
                    $c = call_user_func($column->searchCallback, $value);
                    $source->where($c[0], $c[1]);
                    continue;
                }

                if ($c["searchMethod"] == "multiple") {
                    $field = $c["name"];
                    $s = [];
                    $p = [];
                    foreach ($value as $k) {
                        $s[] = "?{$field}_{$k}";
                        $p["{$field}_{$k}"] = $k;
                    }

                    $source->where("$field in (" . implode(",", $s) . ")", $p);
                    continue;
                } elseif ($c["searchMethod"] == "like") {
                    $name = ":" . $c["name"];
                    $source->where($c["name"] . " like $name", [$name => "%$value%"]);
                } elseif ($c["searchMethod"] == "equal") {
                    $source->filter([$c["name"] => $value]);
                } elseif ($c["searchMethod"] == "date") {

                    $from = $value[0];
                    $to = $value[1];
                    if ($from == $to) {
                        $field = $c["name"];
                        $name = ":" . $field;
                        $source->where("date(`$field`) = $name", [$name => $from]);
                    } else {
                        $field = $c["name"];
                        $field_from = ":" . $field . "_from";
                        $field_to = ":" . $field . "_to";
                        $source->where("date(`$field`) between $field_from and $field_to", [
                            $field_from => $from,
                            $field_to => $to
                        ]);
                    }
                }
            }
        }

        return $source;
    }

    public function recordsFiltered()
    {
        $source = $this->filteredSource();
        return $source->count();
    }

    public function jsonSerialize()
    {
        //parse columns
        foreach ($this->columns as $name => $c) {
            if (is_string($c)) {
                $this->add($name, $c);
            } elseif (is_array($c)) {

                $col = $this->add($name, $c["content"]);
                if ($c["format"]) {
                    $col->format($c["format"]);
                }
                if ($c["alink"]) {
                    $col->alink($c["alink"]);
                }
            }
        }


        if ($_GET["type"]) {
            $this->exportFile($_GET["type"]);
            exit();
            return null;
        }

        return [
            "draw" => $this->draw,
            "data" => $this->data(),
            "total" => $this->recordsFiltered()
        ];
    }

    public function exportFile($type)
    {

        switch ($type) {
            case "xlsx":
                $t = Type::XLSX;
                break;
            case "csv":
                $t = Type::CSV;
                break;
        }
        $writer = WriterFactory::create($t);
        $writer->openToFile("php://output");

        $data = $this->data();

        foreach ($this->request["columns"] as $k => $c) {

            $col = $this->_columns[$c["data"]];

            if ($col->type != "text") continue;

            $row[] = $c["data"];
            $cols[] = $c["data"];
        }


        $writer->addRow($row);


        foreach ($data as $d) {
            $ds = [];
            foreach ($cols as $c) {
                if (is_array($d[$c])) {
                    $ds[$c] = $d[$c]["content"];
                } else {
                    $ds[$c] = $d[$c];
                }
            }
            $writer->addRow($ds);
        }

        $writer->close();
    }
}
