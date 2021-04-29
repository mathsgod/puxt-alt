<?php

namespace App\UI;

use Exception;
use My\Func;
use P\HTMLElement;
use BS\InputSelect;
use P\InputCollection;
use Firebase\JWT\JWT;

class Col extends HTMLElement
{
    public $cell;
    public $label;
    public $c_tpl;
    public $page;

    public function cell()
    {
        return $this->cell;
    }

    public function __construct(string $tag, \App\Page $page)
    {
        parent::__construct($tag);
        $this->cell = p();
        $this->c_tpl = p();
        $this->page = $page;
    }

    public function gf($gf)
    {
        foreach ($this->cell as $cell) {
            p($cell)->attr("data-gf", $gf);

            if ($object = p($cell)->data("object")) {
                $field = p($cell)->attr("data-field");
                $gf_obj = Func::_($field)->call($object);
                p($cell)->text(Func::_($gf)->call($gf_obj));
            }
        }
        return $this;
    }

    public function tinymce(string $field)
    {

        foreach ($this->cell as $cell) {
            try {
                $textarea = p("textarea")->appendTo($cell);
                $textarea->attr("is", "tinymce");
                $textarea->attr('data-field', $field);
                $textarea->attr('name', $field);
                $textarea->addClass('form-control');

                if ($object = p($cell)->data("object")) {
                    $textarea->data("object", $object);

                    $textarea->attr("value",$this->getObjectValue($object, $field));

                    if ($this->callback) {
                        call_user_func($this->callback, $object, $textarea[0]);
                    }
                }
                $p[] = $textarea[0];
            } catch (Exception $e) {
                $cell->append("<p class='form-control-static'>" . $e->getMessage() . "</p>");
            }
        }


        if ($this->createTemplate) {
            $textarea = p("textarea");
            $textarea->addClass('form-control');
            $textarea->attr("name", $field);
            $textarea->attr("data-field", $field);

            $p[] = $textarea[0];
            $this->c_tpl[] = $textarea[0];
            $this->setAttribute("c-tpl", $this->c_tpl);
            $p->on("change", function () {
                $this->setAttribute("c-tpl",  $this->c_tpl);
            });
        }

        return $p;
    }

    /*public function iconpicker($field)
    {
        $p = new \BS\ButtonCollection;

        foreach ($this->cell as $cell) {
            $btn = new \BS\Button();
            $p->append($btn);
            $btn->attributes["data-iconset"] = "fontawesome";
            $btn->attributes["role"] = "iconpicker";
            $btn->attributes["data-rows"] = 10;
            $btn->attributes["data-cols"] = 10;
            $btn->attributes["name"] = $field;

            p($cell)->append($btn);

            if ($object = p($cell)->data("object")) {

                $btn->attributes["data-icon"] = \My\Func::_($field)->call($object);
            }
        }
        return $p;
    }*/

    public function ace($field, $mode)
    {
        $p = p();

        foreach ($this->cell as $cell) {
            $e = p("div");
            $e->css("height", "400px");
            $e->css("display", "none");

            $e->attr("is", "ace");
            $e->attr('data-field', $field);
            $e->attr('name', $field);

            if ($mode) {
                $e->attr("ace-mode", $mode);
            }

            if ($object = p($cell)->data("object")) {
                $e->data("object", $object);
                $e->text($this->getObjectValue($object, $field));

                if ($this->callback) {
                    call_user_func($this->callback, $object, $e[0]);
                }
            }
            $e->appendTo($cell);
        }


        if ($this->createTemplate) {
            $textarea = p("textarea");
            $textarea->attr("name", $field);
            $textarea->attr("data-field", $field);

            $p[] = $textarea[0];
            $this->c_tpl[] = $textarea[0];
        }

        return $p;
    }



    public function alink($uri)
    {
        foreach ($this->cell as $cell) {
            if ($object = p($cell)->data("object")) {
                $field = p($cell)->attr("data-name");
                $next_obj = \My\Func::_($field)->call($object);
                if (is_object($next_obj)) {
                    $object = $next_obj;
                }

                $href = $object->uri($uri);

                $a = p("el-link")->attr('href', $href)->attr("type","info");
                $a->append(p($cell)->contents());
                $a->appendTo(p($cell));
            }
        }
        return $this;
    }

    public function width($width)
    {
        p($this)->css("width", "{$width}px");
        return $this;
    }

    public function attr($name, $value)
    {
        foreach ($this->cell as $cell) {
            if ($object = p($cell)->data("object")) {
                if ($value instanceof \Closure) {
                    $value = $value->call($object);
                }
            }
            p($cell)->attr($name, $value);
        }
        return $this;
    }

    public function format($callback, $params = null)
    {
        foreach ($this->cell as $cell) {
            if ($object = p($cell)->data("object")) {
                $content = p($cell)->html();
                if (is_array($callback)) {
                    $content = call_user_func($callback, $content, $params);
                } else {
                    $content = \My\Func::_($callback)->call($content);
                }

                p($cell)->html($content);
            }
        }
        return $this;
    }

    public function password($field)
    {
        $p = new \P\InputCollection;
        foreach ($this->cell as $cell) {
            $input = p("input")->appendTo($cell);
            //$input->attr("is", "bs-input");
            $input->addClass("form-control");
            $input->attr("type", "password");
            $input->attr("name", $field);
            //$input->attr("data-field", $field);

            if ($object = p($cell)->data("object")) {
                $input->data("object", $object);

                if ($this->callback) {
                    call_user_func($this->callback, $object, $input[0]);
                }
            }


            $p[] = $input[0];
        }

        if ($this->createTemplate) {
            $input = p("input");
            $input->addClass('form-control');
            $input->attr("name", $field);
            $input->attr("data-field", $field);

            $p[] = $input[0];

            $this->c_tpl[] = $input[0];
            $this->setAttribute("c-tpl", $this->c_tpl);
            $p->on("change", function () {
                $this->setAttribute("c-tpl",  $this->c_tpl);
            });
        }
        return $p;
    }

    public function inputNumber(string $field): InputCollection
    {
        $p = new InputCollection();
        foreach ($this->cell as $cell) {
            try {

                $input = p("input-number")->appendTo($cell);
                $input->attr("name", $field);

                if ($object = p($cell)->data("object")) {
                    $input->data("object", $object);
                    $input->attr("value", $this->getObjectValue($object, $field));

                    if ($this->callback) {
                        call_user_func($this->callback, $object, $input[0]);
                    }
                }

                $p[] = $input[0];
            } catch (Exception $e) {
                $cell->append("<p class='form-control-static'>" . $e->getMessage() . "</p>");
            }
        }

        if ($this->createTemplate) {
            $input = p("input-number");
            $input->attr("name", $field);
            $input->attr("value", $this->default[$field]);

            $p[] = $input[0];

            $this->c_tpl[] = $input[0];

            $this->setAttribute("c-tpl", $this->c_tpl);

            $p->on("change", function () {
                $this->setAttribute("c-tpl",  $this->c_tpl);
            });
        }

        return $p;
    }

    public function number($field = null): InputCollection
    {
        return $this->input($field)->type("number");
    }

    public function input(string $field): InputCollection
    {
        $p = new InputCollection;
        foreach ($this->cell as $cell) {
            try {

                $input = p("input")->appendTo($cell);
                //                $input->attr("is", "alt-input");
                $input->addClass("form-control");
                $input->attr("name", $field);
                $input->attr("data-field", $field);

                if ($object = p($cell)->data("object")) {
                    $input->data("object", $object);
                    $input->attr("value", $this->getObjectValue($object, $field));

                    if ($this->callback) {
                        call_user_func($this->callback, $object, $input[0]);
                    }
                }

                $p[] = $input[0];
            } catch (Exception $e) {
                $cell->append("<p class='form-control-static'>" . $e->getMessage() . "</p>");
            }
        }

        if ($this->createTemplate) {
            $input = p("input");
            $input->addClass("form-control");
            $input->attr("is", "alt-input");
            $input->attr("name", $field);
            $input->attr("data-field", $field);
            $input->attr("value", $this->default[$field]);

            $p[] = $input[0];

            $this->c_tpl[] = $input[0];

            $this->setAttribute("c-tpl", $this->c_tpl);

            $p->on("change", function () {
                $this->setAttribute("c-tpl",  $this->c_tpl);
            });
        }


        return $p;
    }

    public function file(string $field): InputCollection
    {
        $p = new InputCollection;
        foreach ($this->cell as $cell) {
            try {

                $object = p($cell)->data("object");
                $value = $this->getObjectValue($object, $field);
                if ($value) {

                    $div = p(
                        <<<HTML
<div class="input-group">
    <div class="input-group-btn">
    </div>
</div>                    
HTML
                    );
                    $button = p("<button type='button' class='btn btn-default' onclick='$(this).parent().next().attr(\"disabled\",false)'>reupload</button>");
                    $div->find(".input-group-btn")->append($button);


                    $input = p("input");
                    $input->attr("type", "file");
                    $input->addClass("form-control");
                    $input->attr("name", $field);
                    $input->attr("disabled", true);
                    $div->append($input);
                    $div->appendTo($cell);
                } else {
                    $input = p("input")->appendTo($cell);
                    $input->attr("type", "file");
                    $input->addClass("form-control");
                    $input->attr("name", $field);
                }

                $p[] = $input[0];
            } catch (Exception $e) {
                $cell->append("<p class='form-control-static'>" . $e->getMessage() . "</p>");
            }
        }
        return $p;
    }


    private static $FILEMAN_NUM = 0;
    public function fileman(string $field)
    {

        $app = $this->page->app;
        $p = $this->input($field);
        $p->attr("is", "fileman");
        $p->attr("url", $app->base_path . "/Fileman/?token=");

        $p->attr("id", "_fileman_" . self::$FILEMAN_NUM);
        self::$FILEMAN_NUM++;
        return $p;
    }


    public function roxyfileman($field)
    {
        $p = $this->input($field);
        $p->attr("is", "roxyfileman");
        return $p;
    }

    public function ckeditor($field)
    {
        $p = new \P\Query();
        $app = $this->page->app;
        $fileman_path = $app->base_path . "/Fileman/?token=";

        /*        $payload = [
            "iat" => time(),
            "exp" => time() + 3600,
            "api" => "http://192.168.88.108/hostlink-fileman/",
            "path" => "C:\Users\maths\Desktop\web\hostlink-fileman\uploads",
            "type" => "image",
            "url" => "http://192.168.88.108/hostlink-fileman/uploads",
        ];*/

        $pi = $this->page->app->pathinfo();
        $composer_base = $pi["composer_base"];
        $document_root = $pi["document_root"];


        $config = $this->page->app->config["hostlink-fileman"];

        $basepath = $config["basepath"];



        foreach ($this->cell as $cell) {
            try {
                $cke = new CKEditor();
                $textarea = p($cke)->appendTo($cell);

                $textarea->attr("is", "ckeditor");
                $textarea->attr('data-field', $field);
                $textarea->attr('name', $field);
                $textarea->addClass('form-control');
                $textarea->attr("basepath", $basepath);

                if ($object = p($cell)->data("object")) {
                    $textarea->data("object", $object);

                    //$textarea->text($this->getObjectValue($object,$field));
                    $textarea->attr("value", $this->getObjectValue($object, $field));

                    if ($this->callback) {
                        call_user_func($this->callback, $object, $textarea[0]);
                    }
                }
                $textarea->attr(":config", json_encode([
                    "filebrowserImageBrowseUrl" => $fileman_path . "&source=ckeditor&type=image",
                    "filebrowserBrowseUrl" => $fileman_path . "&source=ckeditor"
                ]));

                $p[] = $textarea[0];
            } catch (Exception $e) {
                $cell->append("<p class='form-control-static'>" . $e->getMessage() . "</p>");
            }
        }


        if ($this->createTemplate) {
            $textarea = p("ckeditor");
            $textarea->addClass('form-control');
            $textarea->attr("name", $field);
            $textarea->attr("data-field", $field);

            $p[] = $textarea[0];
            $this->c_tpl[] = $textarea[0];
            $this->setAttribute("c-tpl", $this->c_tpl);
            p($textarea[0])->on("change", function () {
                $this->setAttribute("c-tpl",  $this->c_tpl);
            });
        }

        return $p;
    }

    public function textarea($field)
    {
        $p = p();

        foreach ($this->cell as $cell) {
            try {
                $textarea = p("textarea")->appendTo($cell);
                $textarea->attr('data-field', $field);
                $textarea->attr('name', $field);
                $textarea->addClass('form-control');

                if ($object = p($cell)->data("object")) {
                    $textarea->data("object", $object);

                    $textarea->text($this->getObjectValue($object, $field));

                    if ($this->callback) {
                        call_user_func($this->callback, $object, $textarea[0]);
                    }
                }
                $p[] = $textarea[0];
            } catch (Exception $e) {
                $cell->append("<p class='form-control-static'>" . $e->getMessage() . "</p>");
            }
        }


        if ($this->createTemplate) {
            $textarea = p("textarea");
            $textarea->addClass('form-control');
            $textarea->attr("name", $field);
            $textarea->attr("data-field", $field);

            $p[] = $textarea[0];
            $this->c_tpl[] = $textarea[0];
            $this->setAttribute("c-tpl", $this->c_tpl);
            $p->on("change", function () {
                $this->setAttribute("c-tpl",  $this->c_tpl);
            });
        }

        return $p;
    }

    public function select($field, $data_source = null, $display_member = null, $value_member = null)
    {
        $p = new \P\SelectCollection();

        foreach ($this->cell as $cell) {
            $select = p("select")->appendTo($cell);
            $select->addClass("form-control");
            $select->attr("data-field", $field);
            $select->attr("name", $field);

            if ($object = p($cell)->data("object")) {
                $select->data("object", $object);
                $select[0]->setAttribute("data-value", json_encode($this->getObjectValue($object, $field)));
                if ($this->callback) {
                    call_user_func($this->callback, $object, $select[0]);
                }
            }

            $p[] = $select[0];
        }

        if ($this->createTemplate) {
            $select = p("select");
            $select->addClass("form-control");
            $select->attr("data-field", $field);
            $select->attr("name", $field);

            $p[] = $select[0];
            $this->c_tpl[] = $select[0];
            $this->setAttribute("c-tpl", $this->c_tpl);
            $p->on("change", function () {
                $this->setAttribute("c-tpl",  $this->c_tpl);
            });
        }

        if ($data_source) {
            $p->ds($data_source, $display_member, $value_member);
        }

        return $p;
    }

    public function Xeditable($index, $type = "text")
    {
        $p = p();
        foreach ($this->cell as $cell) {
            if ($type == "text") {
                $a = new \Xeditable\Text();
                $a->setAttribute("index", $index);
            } elseif ($type == "textarea") {
                $a = new \Xeditable\Textarea();
                $a->setAttribute("index", $index);
            } elseif ($type == "date") {
                $a = new \Xeditable\Date();
                $a->setAttribute("index", $index);
            } elseif ($type == "select") {
                $a = new \Xeditable\Select();
            } elseif ($type == "datetime") {
                $a = new \Xeditable\DateTime();
                $a->setAttribute("index", $index);
            } else {
                throw new \Exception("Xeditable type $type not found");
            }
            $cell->append($a);
            //$a->appendTo($cell);

            if ($object = p($cell)->data("object")) {
                if ($type != "select") {
                    p($a)->text(is_object($object) ? $object->$index : $object[$index]);
                }

                $a->setAttribute("data-pk", $object->id());
                $a->setAttribute("data-url", $object->uri() . "?xeditable");
            }

            $a->setAttribute("name", $index);
            $a->setAttribute("data-name", $index);

            $p[] = $a;
        }

        return $p;
    }

    public function ws($value = "pre")
    {
        p($this)->css("white-space", $value);
        return $this;
    }

    public function a($field = null)
    {
        $p = new \P\AnchorCollection;
        foreach ($this->cell as $cell) {
            $a = p("a")->appendTo($cell);
            $a->attr("data-field", $field);

            if ($object = p($cell)->data("object")) {
                $a->data("object", $object);
                $a->text(Func::_($field)->call($object));
            }
            $p[] = $a[0];

            if ($this->callback) {
                call_user_func($this->callback, $object, $a[0]);
            }
        }
        return $p;
    }

    public function email($field)
    {
        $p = new \P\InputCollection;
        foreach ($this->cell as $cell) {
            try {
                $input = p("input")->appendTo($cell);
                $input->attr("type", "email");
                $input->addClass("form-control");
                //                $input->attr("is", "alt-email");
                $input->attr("name", $field);
                $input->attr("data-field", $field);

                if ($object = p($cell)->data("object")) {
                    $input->data("object", $object);
                    $input->attr("value", $this->getObjectValue($object, $field));

                    if ($this->callback) {
                        call_user_func($this->callback, $object, $input[0]);
                    }
                }

                $p[] = $input[0];
            } catch (Exception $e) {
                $cell->append("<p class='form-control-static'>" . $e->getMessage() . "</p>");
            }
        }

        if ($this->createTemplate) {
            $input = p("input");
            $input->attr("is", "alt-email");
            $input->attr("name", $field);
            $input->attr("data-field", $field);
            $input->attr("value", $this->default[$field]);

            $p[] = $input[0];

            $this->c_tpl[] = $input[0];
            $this->setAttribute("c-tpl", $this->c_tpl);
            $p->on("change", function () {
                $this->setAttribute("c-tpl",  $this->c_tpl);
            });
        }
        return $p;
    }

    public function button($field = null): \P\Query
    {

        $as = new \P\Query();
        foreach ($this->cell as $cell) {
            $btn = new \P\HTMLButtonElement();
            $cell->append($btn);
            $btn->classList->add("btn");
            $btn->classList->add("btn-primary");
            $btn->classList->add("btn-xs");

            if ($object = p($cell)->data("object")) {
                p($btn)->data("object", $object);
            }

            if ($field) {
                p($btn)->attr("data-value", $this->getObjectValue($object, $field));
            }

            $as[] = $btn;
        }
        return $as;
    }

    public function tokenField($field, array $options = [])
    {

        $p = new \P\Query();
        foreach ($this->cell as $cell) {

            $cell->classList->add("select2-info");
            $input = p("input")->appendTo($cell);
            $input->attr("type", "hidden");
            $input->attr("name", $field);

            $e = p("select2")->appendTo($cell);
            $e->addClass("form-control");
            $e->attr("name", $field . "[]");
            $e->attr("data-tags", "true");
            $e->attr("multiple", true);


            $data = [];
            $value = [];
            if ($object = p($cell)->data("object")) {

                $value = $this->getObjectValue($object, $field);
                if (is_string($value)) {
                    $value = explode(",", $value);
                }

                $value = array_filter($value, function ($v) {
                    return $v != "";
                });

                foreach ($value as $v) {
                    $data[] = [
                        "id" => $v,
                        "text" => $v,
                    ];
                }

                $e->attr(":value", json_encode($value));
            }

            foreach ($options as $v) {
                if (!in_array($v, $value)) {
                    $data[] = [
                        "id" => $v,
                        "text" => $v
                    ];
                }
            }

            $e->attr("data-data", json_encode($data));
            $p[] = $e;
        }

        if ($this->createTemplate) {
            $e = p("select2");
            $e->addClass("form-control");
            $e->attr("name", $field);
            $e->attr("data-tags", "true");
            $e->attr("multiple", true);

            $p[] = $e[0];
            $this->c_tpl[] = $e[0];
            $this->setAttribute("c-tpl", $this->c_tpl);

            $data = [];
            foreach ($options as $v) {
                if (!in_array($v, $value)) {
                    $data[] = [
                        "id" => $v,
                        "text" => $v
                    ];
                }
            }
            $e->attr("data-data", json_encode($data));



            $p->on("change", function () {
                $this->setAttribute("c-tpl",  $this->c_tpl);
            });
        }

        //  $p->options($options);
        return $p;
    }

    private function getObjectValue($object, string $field)
    {
        $f = explode("[", $field, 2);
        $f = $f[0];

        $value = is_object($object) ? $object->$f : $object[$f];
        preg_match_all('#\[(.*?)\]#', $field, $match);
        foreach ($match[1] as $m) {
            $value = $value[$m];
        }
        return $value;
    }

    public function inputSelect($field, $options = [])
    {
        $p = new \ALT\InputSelectCollection();

        foreach ($this->cell as $cell) {

            $is = new \ALT\InputSelect();
            $input = p($is)->find("input");
            $input->attr("data-field", $field);
            $input->attr("name", $field);
            $cell->append($is);

            if ($object = p($cell)->data("object")) {

                $input->val($this->getObjectValue($object, $field));

                if ($this->callback) {
                    call_user_func($this->callback, $object, $is);
                }
            }

            foreach ($options as $v) {
                $is->addItem($v);
            }

            $p[] = $is;
        }

        if ($this->createTemplate) {
            $is = new \ALT\InputSelect();

            foreach ($options as $v) {
                $is->addItem($v);
            }

            p($is)->find("input")->attr("data-field", $field)->attr("name", $field);
            $p[] = $is;
            $this->c_tpl[] = $is;
            $this->setAttribute("c-tpl", $this->c_tpl);
            $p->on("change", function () {
                $this->setAttribute("c-tpl",  $this->c_tpl);
            });
        }
        return $p;
    }

    public function checkboxes(string $field, array $ds)
    {
        //<div class="icheck-primary"><input type="checkbox" id="_checkbox_1" name="a" value="1"><label for="_checkbox_1"></label></div>

        $p = p();
        foreach ($this->cell as $cell) {

            $hidden = p("input")->appendTo($cell);
            $hidden->attr("type", "hidden");
            $hidden->attr("name", $field);


            $object = p($cell)->data("object");
            if ($object) {
                $object = $this->getObjectValue($object, $field);

                if (is_string($object)) {
                    $object = explode(",", $object);
                }
            }


            $div = p("div");
            $div->addClass("row");
            $div->appendTo($cell);


            foreach ($ds as $k => $v) {
                $id = uniqid("_$field", true);
                $cb_div = p("div")->appendTo($div);
                $cb_div->addClass("col-sm-6 col-md-4 col-lg-2");

                $cb = p("div")->appendTo($cb_div);
                $cb->addClass("icheck-primary");

                $cb_input = p("input")->appendTo($cb);
                $cb_input->attr("id", $id);
                $cb_input->attr("type", "checkbox");
                $cb_input->attr("value", $k);
                $cb_input->attr("name", $field . "[]");

                if ($object) {
                    if (in_array($k, $object)) {
                        $cb_input->attr("checked", true);
                    }
                }


                $label = p("label");
                $label->attr("for", $id);
                $label->appendTo($cb);
                $label->text($v);


                $cb_div->appendTo($div);
            }
        }
        return $p;
    }

    public function checkbox($field): \P\Query
    {
        $p = p();
        foreach ($this->cell as $cell) {

            $input = p("input")->appendTo($cell);
            $input->attr("type", "hidden");
            $input->attr("data-field", $field);
            $input->attr("name", $field);
            $input->val(0);
            call_user_func($this->callback, null, $input[0]);


            $cb = new CheckBox();
            p($cell)->append($cb);


            $cb->input->setAttribute("name", $field);
            $cb->input->value = 1;


            if ($object = p($cell)->data("object")) {
                $value = $this->getObjectValue($object, $field);
                if ($value) {
                    $cb->input->setAttribute("checked", true);
                }

                if ($this->callback) {
                    call_user_func($this->callback, $object, $cb->input);
                }
            }

            $p[] = $cb;
        }

        if ($this->createTemplate) {
            $cb = new CheckBox();
            $input = $cb->input;
            $input->input->setAttribute("name", $field);
            $cb->input->value = 1;

            $this->c_tpl[] = $cb;
            $this->setAttribute("c-tpl", $this->c_tpl);
            $p->on("change", function () {
                $this->setAttribute("c-tpl",  $this->c_tpl);
            });
        }

        return $p;
    }

    public function colorpicker($index)
    {
        return $this->input($index)->addClass("cp");
    }

    public function datePicker(string $field): InputCollection
    {
        $p = new InputCollection;
        foreach ($this->cell as $cell) {
            try {
                $div = p("date-picker")->appendTo($cell);
                $div->attr("name", $field);

                if ($object = p($cell)->data("object")) {
                    $div->data("object", $object);
                    $div->attr("value", $this->getObjectValue($object, $field));

                    if ($this->callback) {
                        call_user_func($this->callback, $object, $div[0]);
                    }
                }
                $p[] = $div[0];
            } catch (Exception $e) {
                $cell->append("<p class='form-control-static'>" . $e->getMessage() . "</p>");
            }
        }

        if ($this->createTemplate) {

            $div = p("date-picker");
            $div->attr("name", $field);
            $p[] = $div[0];
            $this->c_tpl[] = $div[0];
            $this->setAttribute("c-tpl", $this->c_tpl);
            $p->on("change", function () {
                $this->setAttribute("c-tpl",  $this->c_tpl);
            });
        }
        return $p;
    }

    public function date(string $field): InputCollection
    {
        $p = new InputCollection;
        foreach ($this->cell as $cell) {
            try {
                $div = p("input")->appendTo($cell);
                $div->addClass("form-control");
                $div->attr("is", "date");
                $div->attr("type", "text");
                $div->attr("name", $field);
                $div->attr("autocomplete", "off");

                if ($object = p($cell)->data("object")) {
                    $div->data("object", $object);
                    $div->attr("value", $this->getObjectValue($object, $field));

                    if ($this->callback) {
                        call_user_func($this->callback, $object, $div[0]);
                    }
                }
                $p[] = $div[0];
            } catch (Exception $e) {
                $cell->append("<p class='form-control-static'>" . $e->getMessage() . "</p>");
            }
        }

        if ($this->createTemplate) {

            $div = p("input")->attr("is", "alt-date");
            $div->attr("name", $field);
            $div->attr("data-field", $field);
            $p[] = $div[0];
            $this->c_tpl[] = $div[0];
            $this->setAttribute("c-tpl", $this->c_tpl);
            $p->on("change", function () {
                $this->setAttribute("c-tpl",  $this->c_tpl);
            });
        }
        return $p;
    }

    public function time(string $field)
    {
        $p = $this->datetime($field);
        $p->attr("format", "HH:mm");
        return $p;
    }

    public function datetime(string $field)
    {
        $p = new \P\InputCollection;
        foreach ($this->cell as $cell) {

            $div = p("input")->appendTo($cell);
            $div->attr("is", "alt-datetime");
            $div->attr("type", "input");
            $div->addClass("form-control");
            $div->attr("name", $field);
            $div->attr("data-field", $field);
            if ($object = p($cell)->data("object")) {
                $div->data("object", $object);
                $div->attr("value", $this->getObjectValue($object, $field));

                if ($this->callback) {
                    call_user_func($this->callback, $object, $div[0]);
                }
            }
            $p[] = $div[0];
        }

        if ($this->createTemplate) {

            $div = p("input")->attr("is", "alt-datetime");
            $div->attr("icon", "far fa-clock-alt");
            $div->attr("name", $field);
            $div->attr("data-field", $field);
            $p[] = $div[0];
            $this->c_tpl[] = $div[0];
            $this->setAttribute("c-tpl", $this->c_tpl);
            $p->on("change", function () {
                $this->setAttribute("c-tpl",  $this->c_tpl);
            });
        }
        return $p;
    }

    public function multiSelect(string $field): \P\SelectCollection
    {
        $p = new \P\SelectCollection();

        foreach ($this->cell as $cell) {
            $select = p("select")->appendTo($cell);
            $select->addClass("selectpicker");
            $select->attr("data-live-search", "true");
            $select->attr("data-field", $field);
            $select->attr("data-actions-box", "true");
            $select->attr("name", $field . "[]");
            $select->attr("multiple", true);
            //$select->attr("data-width","fit");

            if ($object = p($cell)->data("object")) {
                $select->data("object", $object);
                $value = $this->getObjectValue($object, $field);
                if (is_string($value)) {
                    $value = explode(",", $value);
                }

                $select->attr("data-value", json_encode($value, JSON_UNESCAPED_UNICODE));
                if ($this->callback) {
                    call_user_func($this->callback, $object, $select[0]);
                }
            }

            $p[] = $select[0];
        }

        if ($this->createTemplate) {
            $select = p("select");
            $select->addClass("form-control");
            $select->attr("data-live-search", "true");
            $select->attr("data-field", $field);
            $select->attr("data-actions-box", "true");
            $select->attr("name", $field . "[]");
            $select->attr("multiple", true);


            $p[] = $select[0];
            $this->c_tpl[] = $select[0];
            $this->setAttribute("c-tpl", $this->c_tpl);
            $p->on("change", function () {
                $this->setAttribute("c-tpl",  $this->c_tpl);
            });
        }

        return $p;
    }

    public function multiSelectPicker(string $field)
    {
        return $this->multiSelect($field);
    }

    public function selectPicker(string $field)
    {
        $p = new \P\SelectCollection();

        foreach ($this->cell as $cell) {
            $select = p("select")->appendTo($cell);
            $select->addClass("form-control selectpicker");
            $select->attr("data-live-search", "true");
            $select->attr("data-field", $field);
            $select->attr("name", $field);

            if ($object = p($cell)->data("object")) {
                $select->data("object", $object);
                $select->attr("data-value", $this->getObjectValue($object, $field));
                if ($this->callback) {
                    call_user_func($this->callback, $object, $select[0]);
                }
            }

            $p[] = $select[0];
        }

        if ($this->createTemplate) {
            $select = p("select");
            $select->addClass("form-control selectpicker");
            $select->attr("data-field", $field);
            $select->attr("name", $field);

            $p[] = $select[0];
            $this->c_tpl[] = $select[0];
            $this->setAttribute("c-tpl", $this->c_tpl);
            $p->on("change", function () {
                $this->setAttribute("c-tpl",  $this->c_tpl);
            });
        }

        return $p;
    }

    public function multiSelect2(string $field)
    {
        foreach ($this->cell as $cell) {
            $input = p("input")->appendTo($cell);
            $input->attr("type", "hidden");
            $input->attr("name", $field);

            if ($object = p($cell)->data("object")) {
                if ($this->callback) {

                    call_user_func($this->callback, $object, $input[0]);
                }
            }
        }

        $select = $this->select2($field);
        $select->attr("multiple", true);
        $select->attr("name", $field . "[]");

        $a = json_decode($select->attr(":value"), true);
        if (is_string($a)) {
            $select->attr(":value", json_encode(explode(",", $a)));
        }
        return $select;
    }

    public function select2(string $field)
    {
        $p = new \P\SelectCollection();

        foreach ($this->cell as $cell) {
            $cell->classList->add("select2-info");
            $select = p("select")->appendTo($cell);
            $select->addClass("form-control");
            $select->attr("is", "select2");
            $select->attr("data-field", $field);
            $select->attr("name", $field);

            //$select->attr(":option", json_encode(["theme" => "bootstrap4"]));

            if ($object = p($cell)->data("object")) {
                $select->data("object", $object);
                try {
                    $data_value = $this->getObjectValue($object, $field);
                    $select->attr(":value", json_encode($data_value));
                } catch (\Exception $e) {
                }

                if ($this->callback) {
                    call_user_func($this->callback, $object, $select[0]);
                }
            }

            $p[] = $select[0];
        }

        if ($this->createTemplate) {
            $select = p("select");
            $select->addClass("select2 form-control");
            $select->attr("data-field", $field);
            $select->attr("name", $field);
            $this->c_tpl[] = $select[0];
            $this->setAttribute("c-tpl", $this->c_tpl);
            $p->on("change", function () {
                $this->setAttribute("c-tpl",  $this->c_tpl);
            });


            $p[] = $select[0];
        }
        return $p;
    }

    public function helpBlock(string $text)
    {
        $p = p();
        foreach ($this->cell as $cell) {
            $block = p("span")->appendTo($cell);
            $block->addClass("form-text text-muted");
            $block->html($text);
            $p[] = $block[0];
        }

        if ($this->createTemplate) {
            $block = p("span");
            $block->addClass("form-text text-muted");
            $block->html($text);
            $this->c_tpl[] = $block[0];
            $this->setAttribute("c-tpl", $this->c_tpl);
            $p->on("change", function () {
                $this->setAttribute("c-tpl",  $this->c_tpl);
            });
        }

        return $p;
    }

    public function img(string $field)
    {
        $p = p();
        foreach ($this->cell as $cell) {
            $img = p("img")->appendTo($cell);
            $img->attr("data-field", $field);
            if ($object = p($cell)->data("object")) {
                $img->attr("src", $this->getObjectValue($object, $field));
            }
            $p[] = $img[0];
        }
        return $p;
    }
}
