<?php

namespace App;

use ALT\Element\Form;
use App\UI\RTable;
use Psr\Http\Message\ResponseInterface;
use Exception;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ServerRequestInterface;
use PHP\Psr7\JsonStream;
use PHP\Psr7\StringStream;
use ReflectionObject;
use Vue\Scriptable;

class Page extends \R\Page implements TranslatorInterface
{
    /**
     * @var \App\App
     */
    public $app;
    public $data = [];
    /**
     * @var \App\Alert
     */
    protected $alert;

    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->alert = $app->alert;
    }

    public function translate(string $name): string
    {
        if ($module = $this->module()) {
            return $module->translate($name);
        }
        return $this->app->translate($name);
    }


    public function object()
    {
        if ($this->_object) {
            return $this->_object;
        }

        $class = "\\" . $this->module()->class;

        $id = $this->id();
        if (class_exists($class, true)) {
            try {
                $this->_object = new $class($id);
            } catch (\Exception $e) {
                return null;
            }
        }

        return $this->_object;
    }

    public function id()
    {
        $path = $this->request->getURI()->getPath();
        foreach (explode("/", $path) as $q) {
            if (is_numeric($q)) {
                return $q;
            }
        }
    }

    public function module()
    {
        $route = $this->request->getAttribute("route");
        $ps = explode("/", $route->path);
        $ps = array_values(array_filter($ps, "strlen"));

        foreach ($this->app->modules() as $module) {
            if ($module->name == $ps[0]) {
                return $module;
                break;
            }
        }
    }

    public function path()
    {
        $route = $this->request->getAttribute("route");
        $path = $route->path;
        if ($path[0] == "/") {
            $path = substr($path, 1);
        }

        return $path;
    }

    protected function createTab($prefix = null): UI\Tab
    {
        $tab = new UI\Tab($this, $prefix);
        $tab->classList->add("card-primary");
        return $tab;
    }

    protected function createRTable(array $function)
    {
        $rtable = new RTable($this);
        $rtable->setTranslator($this);

        $path = (string) $function[0]->request->getURI()->getPath();
        $remote = (string) $path . "/" . $function[1] . "?" . $this->request->getUri()->getQuery();
        $rtable->setAttribute("remote", $remote);
        return $rtable;
    }

    protected function createRT2($objects): UI\RT2
    {
        $rt = new UI\RT2($this, $this->app->config);
        $path = (string) $objects[0]->request->getURI()->getPath();

        $rt->ajax["url"] = (string) $path . "/" . $objects[1] . "?" . $this->request->getUri()->getQuery();


        $rt->pageLength = 25;
        return $rt;
    }

    protected function createRForm($data = null)
    {
        if ($data === null) {
            $data = $this->object();
        }

        $form = new \ALT\R\Form();
        $form->setPage($this);
        $form->setAttribute("type", "primary");
        $form->setAttribute("outline", true);
        $form->setAttribute("method", "POST");
        $form->setAttribute("label-width", "auto");

        if ($data) {
            $form->setData($data);
        }
        return $form;
    }

    protected function createCard(string $type = "primary"): UI\Card
    {
        $card = new UI\Card($this);
        $card->outline = true;
        $card->setAttribute("type", $type);
        return $card;
    }

    protected function createElementCard()
    {
        $card = new \ALT\Element\Card();
        return $card;
    }

    protected function createElementForm()
    {
        $form = new Form();
        return $form;
    }

    protected function createForm($content, bool $multipart = false): UI\Form
    {
        $form = new UI\Form($this);
        p($form->card->body)->append($content);
        if ($multipart) {
            $form->setAttribute("enctype", "multipart/form-data");
        }
        return $form;
    }

    public function createV($object = null): UI\V
    {
        if (!$object) {
            $object = $this->object();
        }
        return new UI\V($object, $this);
    }

    public function createFormTable($data, string $data_key, string $data_name = "data")
    {
        $t = new \ALT\R\FormTable();
        $t->setAttribute("data-key", $data_key);

        $t->setAttribute("data-name", $data_name);

        if ($data) {
            $t->setAttribute(":data", json_encode($data));
        }
        return $t;
    }

    public function createT($objects): UI\T
    {
        return new UI\T($objects, $this);
    }

    public function createTable($objects): UI\Table
    {
        return new UI\Table($objects, $this);
    }

    public function delete()
    {
        $obj = $this->object();
        if ($obj->canDelete()) {
            $obj->delete();
        }

        if ($this->isAccept("application/json")) {
            return ["code" => 200];
        } else {
            $this->alert->success($this->module()->name . " deleted");
            $this->redirect();
        }
    }

    protected $plugins = [];
    public function addLib(string $library)
    {
        $name = $library;
        if ($this->plugins[$name]) {
            return $this->plugins[$name];
        }
        $p = new Plugin($name, $this->app);

        foreach ($p->setting["require"] as $require) {
            $this->addLib($require);
        }

        foreach ($p->setting["php"] as $php) {
            include_once($p->base . "/" . $php);
        }

        $this->plugins[$name] = $p;
        if ($name == "ckeditor") {
            $path = $this->app->config["user"]["roxy_fileman_path"];
            $path = str_replace("{username}", $this->app->user->username, $path);
            $_SESSION["roxy_fileman_path"] = $path;

            $pi = $this->app->pathinfo();
            $path = $pi["system_root"] . $path;
            mkdir($path);
        }


        return $p;
    }

    protected function isAccept(string $type)
    {
        $accepts = $this->request->getHeader("accept");
        foreach ($accepts as $accept) {
            $r = explode(";", $accept);
            if ($r[0] == $type) {
                return true;
            }
        }
        return false;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $this->request = $request;
        $route = $this->app->route;

        $path = $route->path;

        if (!$this->app->acl($path)) {
            return $this->app->accessDeny($request);
        }

        if ($request->getQueryParams()["_rtable"]) {
            $rt = new UI\RTableResponse();
            $request = $request->withQueryParams(["rt" => $rt]);
        }

        if ($request->getQueryParams()["_rt"]) {
            $rt = new UI\RTResponse();
            $request = $request->withQueryParams(["rt" => $rt]);
        }

        if ($request->getQueryParams()["_rt_request"]) {
            $rt = new UI\RTRequest($request);
            $request = $request->withQueryParams(["request" => $rt]);
        }

        if (($this->isAccept("text/html") || $this->isAccept("*/*")) && $request->getMethod() == "GET" && $request->getRequestTarget() == "get") {
            $file = $route->file;
            $pi = pathinfo($file);
            if (file_exists($template_file = $pi["dirname"] . "/" . $pi["filename"] . ".twig")) {
                $this->template = $this->app->twig($template_file);
            }
        }

        try {
            ob_start();
            $response = parent::__invoke($request, $response);
            $echo_content = ob_get_contents();
            ob_end_clean();
        } catch (Exception $e) {


            return $response
                ->withHeader("Content-Type", "application/json; charset=UTF-8")
                ->withBody(new JsonStream(["error" => [
                    "message" => $e->getMessage()
                ]]));
        }



        if ($request instanceof ServerRequestInterface) {
            if ($request->getQueryParams()["_rt"]) {
                return $response;
            }

            if ($request->getQueryParams()["_rt_request"]) {
                return $response;
            }
        }

        foreach ($request->getHeader("Accept") as $accept) {
            list($media,) = explode(",", $accept);
            switch ($media) {
                case "application/json":
                    return $response->withHeader("Content-Type", "application/json; charset=UTF-8");
                    break;
                case "*/*":
                case "text/html":
                    $content = "";

                    if ($this->template) {
                        $data = $this->data;
                        $data["app"] = $this->app;
                        $content .= (string) $response->getBody();
                        $content .= $echo_content;
                        $content .= $this->template->render($data);

                        $response = $response->withHeader("Content-Type", "text/html; charset=UTF-8");
                    } else {
                        $content .= $echo_content;
                        $content .= (string) $response->getBody();
                    }

                    if ($request->getMethod() == "get") {
                        if ($request instanceof ServerRequestInterface) {
                            $content .= $request->getAttribute("included_content");
                        }
                    }
                    return $response->withBody(new StringStream($content));
                    break;
                default:
                    return $response;
            }
        }

        return $response;
    }

    public function post()
    {
        $obj = $this->object();
        $id = $obj->id();

        if ($id) { //update
            if (!$obj->canUpdate()) {
                throw new Exception("access deny");
            }
        }

        $data = $this->request->getParsedBody();

        //gql
        if ($data["query"]) {
            $ro = new ReflectionObject($obj);
            $use_system_gql = $ro->getNamespaceName() == "App";
            $r = $this->app->executeQuery($data["query"], $use_system_gql);
            if (!$r["error"]) {
                $msg = $this->module()->name . " ";
                $msg .= $id ? "updated" : "created";
                $this->alert->success("Success", $msg);
            }

            return $r;
        }

        $obj->bind($data);

        if ($files = $this->request->getUploadedFiles()) {
            foreach ($files as $name => $file) {

                if (property_exists($obj, $name)) {
                    $obj->$name = (string) $file->getStream();
                }
                if (property_exists($obj, $name . "_name")) {
                    $obj->{$name . "_name"} = $file->getClientFilename();
                }

                if (property_exists($obj, $name . "_type")) {
                    $obj->{$name . "_type"} = $file->getClientMediaType();
                }

                if (property_exists($obj, $name . "_size")) {
                    $obj->{$name . "_size"} = $file->getSize();
                }
            }
        }


        if ($this->isAccept("application/json") || $this->request->getHeader("X-Requested-With")) {

            try {
                $obj->save();
            } catch (Exception $e) {
                return ["error" => ["message" => $e->getMessage()]];
            }

            $msg = $this->module()->name . " ";
            if (method_exists($obj, '__toString')) {
                $msg .= (string) $obj . " ";
            }
            $msg .= $id ? "updated" : "created";

            return ["data" => ["message" => $msg]];
        } else {
            $obj->save();

            $msg = $this->module()->name . " ";
            if (method_exists($obj, '__toString')) {
                $msg .= (string) $obj . " ";
            }
            $msg .= $id ? "updated" : "created";
            $this->alert->success("Success", $msg);
            $this->redirect();
        }
    }

    public function redirect(string $uri = null): ResponseInterface
    {
        if ($uri) {

            $location = $this->app->base_path . $uri;
            $this->response = $this->response->withHeader("Location", $location);
            return $this->response;
        }

        if ($_GET["_referer"]) {
            $this->response = $this->response->withHeader("Location", $_GET["_referer"]);
            return $this->response;
        }

        if ($referer = $this->request->getHeader("Referer")[0]) {
            if ($url = $_SESSION["app"]["referer"][$referer]) {
                $this->response = $this->response->withHeader("Location", $url);
                return $this->response;
            }
            $this->response = $this->response->withHeader("Location", $referer);
            return $this->response;
        }
    }

    public function createDataTable($objects): UI\DataTables
    {
        return new UI\DataTables($objects, $this);
    }
    public function write($element)
    {
        parent::write($element);

        if ($element instanceof Scriptable) {
            parent::write($element->script());
        }
    }
}
