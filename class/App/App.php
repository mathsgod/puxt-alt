<?php

namespace App;

use App\GQL\Server;
use Exception;
use Composer\Autoload\ClassLoader;
use Firebase\JWT\JWT;
use PHP\Psr7\JsonStream;
use PHP\Psr7\Response;
use PHP\Psr7\StringStream;
use Psr\Log\LoggerInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Yaml\Yaml;

class App extends \R\App implements TranslatorInterface
{
    public $config = [];
    public $user;
    public $user_id;
    public $usergroup_id = [];
    public $locale = "zh-hk";
    public $plugins = [];
    public $setting = [];
    public $system_value = [];
    public $router;
    public $composer;

    /** Root path of alt3 */
    public $system_root;

    public function __construct(string $root = null, ClassLoader $loader = null, LoggerInterface  $logger = null)
    {
        //check config file
        if (!file_exists($root . "/config.ini") && !file_exists($root . "/config.yml")) {
            throw new Exception("config.ini or config.yml not found");
        }

        $this->system_root = dirname(__DIR__, 2);


        spl_autoload_register(function ($class) use ($root) {
            $class_path = str_replace("\\", DIRECTORY_SEPARATOR, $class);
            $file = realpath($root . "/pages/$class_path/$class.class.php");
            if (is_readable($file)) {
                require_once($file);
            }
        });

        if (file_exists($config_file = $root . "/config.yml")) {
            $this->config = array_merge([], Yaml::parseFile($config_file));
        }

        $this->composer = new Composer($this);
        parent::__construct($root, $loader, $logger);

        Model::$_db = $this->db;
        Model::$_app = $this;
        Module::$_app = $this;
        User::$_app = $this;
        UserGroup::$_app = $this;
        ModelTrait::$_app = $this;

        //-- CONFIG.INI
        $user_config = $this->config;

        //load system config 
        $this->config = Yaml::parseFile($this->system_root . "/config.yml");


        //user config
        foreach ($user_config as $n => $v) {
            foreach ($v as $a => $b) {
                $this->config[$n][$a] = $b;
            }
        }

        foreach (Config::Query() as $c) {
            $this->config["user"][$c->name] = $c->value;
        }


        //user
        if (!$_SESSION["app"]["user_id"]) {
            $this->user = new User(2);
        } else {
            $this->user = new User($_SESSION["app"]["user_id"]);
        }
        if (is_string($this->user->style)) {
            $this->user->style = json_decode($this->user->style, true);
        }

        if ($this->user->style["body"] === null) {
            $this->user->style["body"] = ["text-sm"];
        }

        $this->user_id = $this->user->user_id;

        if ($this->user->language) {
            $this->locale = $this->user->language;
        }

        //usergroup
        foreach ($this->user->UserGroup() as $ug) {
            $this->usergroup_id[] = $ug->usergroup_id;
        }


        foreach (SystemValue::Query() as $sv) {
            $this->system_value[$sv->language][$sv->name] = $sv;
        }

        $uri = $this->request->getUri();
        $path = substr($uri->getPath(), strlen($this->base_path));
        $uri = $uri->withPath($path);
        $this->request = $this->request->withUri($uri);
    }

    public function loginWith(User $user)
    {
        $this->user = $user;
        $this->loadACL();
    }

    private function loadACL()
    {

        //-- ACL
        $this->acl = [];
        $ugs = [];
        foreach ($this->user->UserGroup() as $ug) {
            $ugs[] = (string) $ug;
        }

        $acl = Yaml::parseFile(dirname(__DIR__, 2) . "/acl.yml");
        foreach ($acl["path"] as $path => $usergroups) {
            if (array_intersect($ugs, $usergroups)) {
                $this->acl["path"]["allow"][] = $path;
            }
        }

        foreach ($acl["action"] as $action => $actions) {
            foreach ($actions as $module => $usergroups) {
                if (array_intersect($ugs, $usergroups)) {
                    $this->acl["action"]["allow"][$module][] = $action;
                }
            }
        }

        $w = [];
        $u[] = "user_id=" . $this->user->user_id;
        foreach ($this->user->UserGroup() as $ug) {
            $u[] = "usergroup_id=$ug->usergroup_id";
        }
        $w[] = implode(" or ", $u);
        $query = ACL::Query()->where($w);
        foreach ($query as $acl) {
            if ($acl->action) {
                $this->acl["action"][$acl->value][$acl->module][] = $acl->action;
            } else {
                $this->acl["path"][$acl->value][] = $acl->path();
            }
        }

        //all special user
        foreach (ACL::Query()->where(["special_user is not null"]) as $acl) {
            $this->acl["special_user"][$acl->special_user][$acl->value][$acl->module][] = $acl->action;
        }
    }

    public function run()
    {
        //* Create $_POST
        if ($this->request->getMethod() == "POST") {
            if (strpos($this->request->getHeaderLine("Content-Type"), "application/json") !== false) {
                $_POST = $this->request->getParsedBody();
            }
        }

        $this->loadACL();

        $this->user->online();

        //-- setting
        $this->setting = Yaml::parseFile(dirname(__DIR__, 2) . "/setting.yml");

        if (file_exists($this->root . "/setting.yml")) {
            $setting = Yaml::parseFile($this->root . "/setting.yml");
            //user config
            foreach ($setting as $n => $v) {
                foreach ($v as $a => $b) {
                    $this->setting[$n][$a] = $b;
                }
            }
        }

        //-- Translate
        $translate = Yaml::parseFile(dirname(__DIR__, 2) . "/translate.yml");
        $translate = $translate[$this->user->language];
        $this->translate = $translate;
        ///-------------

        $this->alert = new Alert();

        $request = $this->request;

        $this->router->addRoute(function (RequestInterface $request, $loader) {
            return new Route($request, $this);
        });

        ob_start();
        $route = $this->router->getRoute($this->request, $this->loader);
        $request = $request->withAttribute("included_content", ob_get_contents());
        ob_end_clean();
        $this->route = $route;
        $request = $request->withAttribute("route", $route);


        $this->plugins = Yaml::parseFile(dirname(__DIR__, 2) . "/plugins.yml");

        //---- Module --
        $ps = explode("/", $route->path);
        $ps = array_values(array_filter($ps, "strlen"));
        foreach ($this->modules() as $module) {
            if ($module->name == $ps[0]) {
                $this->module = $module;
                break;
            }
        }

        $translates = Translate::Query([
            "language" => $this->user->language
        ])->where("module is null");

        foreach ($translates as $translate) {
            $this->translate[$translate->name] = $translate->value;
        }

        //--- Module Translate 
        foreach (Translate::Query([
            "module" => $this->module->name,
            "language" => $this->user->language
        ]) as $translate) {
            $this->translate[$translate->name] = $translate->value;
        }

        $class = $route->class;
        if ($class) {
            $page = new $class($this);
            $response = new Response(200);
            $request = $request->withRequestTarget($route->method);

            try {
                if ($this->logger) $this->logger->debug("invoke page");
                $response = $page($request, $response);
            } catch (\Exception $e) {
                if ($this->request->getHeader("accept")[0] == "application/json") {
                    $response = new Response(200);
                    $response = $response
                        ->withHeader("content-type", "application/json")
                        ->withBody(new StringStream($e->getMessage()));
                } else {
                    $this->alert->danger($e->getMessage());

                    if ($referer = $this->request->getHeader("Referer")[0]) {
                        if ($url = $_SESSION["app"]["referer"][$referer]) {
                            $response = $response->withHeader("Location", $url);
                        }
                    }
                }
            }

            foreach ($response->getHeaders() as $name => $values) {
                header($name . ": " . implode(", ", $values));
            }


            file_put_contents("php://output", (string) $response->getBody());
        } elseif ($this->logined()) {
            header("location: {$this->base_path}404_not_found#" . $this->request->getUri()->getPath());
        } else {
            $pi = $this->pathInfo();
            header("location: {$this->base_path}#" . $this->request->getUri()->getPath());
        }
    }

    public function flushMessage(): array
    {
        $msg = $_SESSION["app"]["message"];
        $_SESSION["app"]["message"] = [];
        return $msg ?? [];
    }

    public function page(string $path)
    {
        $uri = $this->request->getUri()->withPath($path);
        $request = $this->request->withUri($uri);


        $route = $this->router->getRoute($request, $this->loader);

        $request = $request
            ->withAttribute("action", $route->action)
            ->withAttribute("route", $route);

        $class = $route->class;
        $page = new $class($this);
        return $page;
    }

    public function IP2StepExemptCheck($ip): bool
    {
        $ips = explode(",", $this->config["user"]["2-step verification white list"]);

        foreach ($ips as $i) {
            $cx = explode("/", $i);
            if (sizeof($cx) == 1) {
                $cx[1] = "255.255.255.255";
            }
            $res = ip2long($cx[0]) & ip2long($cx[1]);
            $res2 = ip2long($ip) & ip2long($cx[1]);
            if ($res == $res2) {
                return true;
            }
        }
        return false;
    }

    public function login(string $username, string $password, string $code = null): bool
    {

        //check AuthLock
        if ($this->config["user"]["auth-lockout"]) {
            if (AuthLock::IsLock()) {
                throw new \Exception("IP locked 180 seconds", 403);
            }
        }

        try {
            $user = User::Login($username, $password);
        } catch (Exception $e) {
            AuthLock::Add();
            throw new Exception("Login error");
        }

        if ($user->secret) {
            $need_check = true;
            if ($setting = $user->setting()) {
                if (in_array($_SERVER["REMOTE_ADDR"], $setting["2-step_ip_white_list"])) {
                    $need_check = false;
                }
            }

            if ($need_check && !$this->IP2StepExemptCheck($_SERVER['REMOTE_ADDR'])) {
                if (($code == "" || !$user->checkCode($code))) {
                    throw new Exception("2-step verification", 403);
                }
            }
        }


        $_SESSION["app"]["user_id"] = $user->user_id;
        $_SESSION["app"]["user"] = $user;
        $_SESSION["app"]["login"] = true;
        $user->createUserLog("SUCCESS");
        $user->online();

        AuthLock::Clear();
        $this->user = $user;
        $this->user_id = $user->user_id;

        return true;
    }

    public function loginByToken(string $token): bool
    {
        try {
            $token = (array)JWT::decode($token, $this->config["jwt"]["key"], ["HS256"]);
        } catch (Exception $e) {
            return false;
        }

        if ($token) {
            if ($token["type"] == "access_token") {
                $user = new User($token["id"]);
                $_SESSION["app"]["user_id"] = $user->user_id;
                $_SESSION["app"]["user"] = $user;
                $_SESSION["app"]["login"] = true;
            }
            return true;
        }
        return false;
    }

    public function logined(): bool
    {
        return (bool) $_SESSION["app"]["login"];
    }

    public function file(string $file)
    {
        if (is_readable($file)) {
            $f = $file;
        } elseif (is_readable($this->root . "/" . $file)) {
            $f = $this->root . "/" . $file;
        } elseif (is_readable($this->system_root . "/" . $file)) {
            $f = $this->system_root . "/" . $file;
        }
        return $f;
    }

    public function twig(string $file)
    {
        $pi = $this->pathInfo();

        $twig_file = null;

        if ($file[0] != "/") {
            if (is_readable($file)) {
                $twig_file = $file;
            } elseif (is_readable($pi["document_root"] . "/" . $file)) {
                $twig_file = $pi["document_root"] . "/" . $file;
            } elseif (is_readable($pi["system_root"] . "/" . $file)) {
                $twig_file = $pi["system_root"] . "/" . $file;
            }
        } else {
            if (is_readable($file)) {
                $twig_file = $file;
            } elseif (is_readable($pi["document_root"] . $file)) {
                $twig_file = $pi["document_root"] . $file;
            }
        }

        if ($twig_file) {
            $pi = pathinfo($twig_file);
            $root = $pi["dirname"];
            $template_file = $pi["basename"];

            if (!$config = $this->config["twig"]) {
                $config = [];
            }
            array_walk($config, function (&$o) use ($root) {
                $o = str_replace("{root}", $root, $o);
            });


            $twig["loader"] = new \Twig\Loader\FilesystemLoader($root);
            $twig["environment"] = new \Twig\Environment($twig["loader"], $config);
            $twig["environment"]->addExtension(new \Twig_Extensions_Extension_I18n());
            //$twig["environment"]->addExtension(new TwigI18n());
            $twig["environment"]->addExtension(new \Twig\Dynamic\Extension());

            $_this = $this;
            $twig["environment"]->addFilter(new \Twig\TwigFilter('trans', function ($str) use ($_this) {
                return $_this->translate($str ?? "");
            }));


            return $twig["environment"]->load($template_file);
        }
    }

    public function pathInfo(): array
    {
        $system_root = $this->system_root;

        $server = $this->request->getServerParams();

        $document_root = $this->document_root;
        if ($this->config["system"]["document_root"]) {
            $document_root = $this->config["system"]["document_root"];
        }

        $cms_base = $this->base_path;

        $cms_root = $this->root;

        if (file_exists($document_root . "/composer.json")) {
            $composer_root = $document_root;
        } else if (file_exists($document_root . "/composer/composer.json")) {
            $composer_root = $document_root . "/composer";
        } else if (file_exists($cms_root . "/composer.json")) {
            $composer_root = $cms_root;
        } else if (file_exists($cms_root . "/composer/composer.json")) {
            $composer_root = $cms_root . "/composer";
        } elseif (file_exists($system_root . "/composer.json")) {
            $composer_root = $system_root;
        }

        $composer_base = substr($composer_root, strlen($document_root));
        $composer_base = str_replace(DIRECTORY_SEPARATOR, "/", $composer_base);

        $system_base = substr($system_root, strlen($document_root));
        $system_base = str_replace(DIRECTORY_SEPARATOR, "/", $system_base);

        return compact("composer_base", "composer_root", "document_root", "cms_root", "cms_base", "system_root", "system_base");
    }

    public function module(string $name)
    {
        $ms = $this->modules();

        foreach ($ms as $m) {
            if ($m->name == $name) {
                return $m;
            }
        }
    }

    private $_modules = null;
    public function modules(): array
    {
        if ($this->_modules) {
            return $this->_modules;
        }


        $modules = [];

        $pi = $this->pathInfo();
        $system_root = $pi["system_root"];
        $page = "pages";

        foreach (glob($system_root . DIRECTORY_SEPARATOR . $page . DIRECTORY_SEPARATOR . "*", GLOB_ONLYDIR) as $m) {
            $name = basename($m);
            $config = [];
            if (is_readable($config_file = $m . DIRECTORY_SEPARATOR . "setting.yml")) {
                $config = Yaml::parseFile($config_file);
            }
            $module = new Module($name, $config);
            $modules[$name] = $module;
        }

        $cms_root = $pi["cms_root"];
        foreach (glob($cms_root . DIRECTORY_SEPARATOR . $page . DIRECTORY_SEPARATOR . "*", GLOB_ONLYDIR) as $m) {
            $name = basename($m);
            $config = [];
            if (is_readable($config_file = $m . DIRECTORY_SEPARATOR . "config.yml")) {
                $config = Yaml::parseFile($config_file);
            } elseif (is_readable($config_file = $m . DIRECTORY_SEPARATOR . "config.ini")) {
                $config = parse_ini_file($config_file, true);
            } elseif (is_readable($config_file = $m . DIRECTORY_SEPARATOR . "setting.yml")) {
                $config = Yaml::parseFile($config_file);
            } elseif (is_readable($config_file = $m . DIRECTORY_SEPARATOR . "setting.ini")) {
                $config = parse_ini_file($config_file, true);
            }
            if (!$module = $modules[$name]) {
                $modules[$name] = new Module($name, $config);
            } else {
                $module->loadConfig($config);
            }
        }
        //sorting
        usort($modules, function ($a, $b) {
            return $a->sequence <=> $b->sequence;
        });


        $this->_modules = $modules;
        return $modules;
    }

    public function translate(string $name): string
    {
        return $this->translate[$name] ?? $name;
    }

    public function allowAction(string $action, string $module): bool
    {
        if ($this->user->isAdmin()) {
            return true;
        }


        $acl = $this->acl["action"]["deny"][$module];
        if (in_array("FC", $acl)) {
            return false;
        }
        if (in_array($action, $acl)) {
            return false;
        }
        $acl = $this->acl["action"]["allow"][$module];
        if (in_array("FC", $acl)) {
            return true;
        }
        if (in_array($action, $acl)) {
            return true;
        }
        return false;
    }

    public function acl(string $path): bool
    {
        if ($this->user->isAdmin()) {
            return true;
        }

        if (in_array($path, $this->acl["path"]["deny"])) {
            return false;
        }

        $module = $this->getModuleByPath($path);

        //deny
        $action = $this->acl["action"]["deny"][$module->name];
        if (in_array("FC", $action)) {
            return false;
        }

        $action = $this->acl["action"]["allow"][$module->name];
        if (in_array("FC", $action)) {
            return true;
        }


        $result = false;
        if ($module->user_default_acl === false) {
        } else {
            if ($this->config["system"]["user_default_acl"] && $this->user->isUser()) {
                if ($module) {
                    if (!starts_with($module->class, "App")) { //module is not system module
                        $result = true;
                    }
                }
            }
        }



        if (!$result) {
            $result = (bool) in_array($path, $this->acl["path"]["allow"]);
        }

        return $result;
    }

    public function getModuleByPath(string $path)
    {
        $ps = explode("/", $path);
        $ps = array_values(array_filter($ps, "strlen"));
        return $this->module($ps[0]);
    }

    public function createMail()
    {
        $mail = new Mail(true);
        $smtp = $this->config["user"]["smtp"];
        $mail->setFrom("no-reply@" . $this->config["user"]["domain"]);

        if ($smtp) {
            $mail->isSMTP();
            $mail->Host = (string) $smtp;
            if ($this->config["user"]["smtp-port"]) {
                $mail->Port = (int)$this->config["user"]["smtp-port"];
            }
            $mail->SMTPAuth = true;
            $mail->Username = $this->config["user"]["smtp-username"];
            $mail->Password = $this->config["user"]["smtp-password"];
            $mail->SMTPAutoTLS = $this->config["user"]["smtp-auto-tls"];
        }


        return $mail;
    }

    public function accessDeny(RequestInterface $request): ResponseInterface
    {
        $uri = $request->getUri()->getPath();

        if ($q = $request->getUri()->getQuery()) {
            $uri .= "?" . $q;
        }

        if ($this->logined()) {

            if ($request->getHeader("accept")[0] == "application/json") {
                $response = new Response(200);
                $msg = [];
                $msg["error"]["message"] = "access deny";
                $msg["error"]["code"] = 403;
                $response = $response->withHeader("content-type", "application/json");
                $response = $response->withBody(new JsonStream($msg));
            } else {
                $response = new Response(403);
                $response = $response->withHeader("location", $this->base_path . "access_deny#/" . $uri);
            }
        } else {

            $response = new Response(200);
            $response = $response->withHeader("location", $this->base_path . "#/" . $uri);
        }

        return $response;
    }

    public function version(): string
    {
        $package = $this->composer->package("mathsgod/alt3");
        if (!$package) return "dev";

        if ($package["version"]) {
            $version = $package["version"];
        }

        if ($version == "dev-master") {
            $version .= " " . substr($package["source"]["reference"], 0, 8);
        }
        return $version;
    }

    public function composer(): Composer
    {
        return new Composer($this);
    }

    public function sv(string $name, $locale = null)
    {
        if (!$locale) $locale = $this->locale;
        if ($sv = $this->system_value[$locale][$name]) {
            if ($sv->_value) return $sv->_value;
            return $sv->_value = $sv->values();
        }
    }

    public function savePlace()
    {
        $uri = $this->request->getURI();
        $path = $uri->getPath();

        if ($path[0] == "/") {
            $path = substr($path, 1);
        }

        if ($query = $uri->getQuery()) {
            $_SESSION["app"]["redirect"] = $path . "?" . $query;
        } else {
            $_SESSION["app"]["redirect"] = $path;
        }
    }

    public function loginFido2(string $username, string  $assertion): bool
    {
        $user = User::Query([
            "username" => $username,
            "status" => 0
        ])->first();


        if (!$user) {
            return false;
        }

        $assertion = json_decode($assertion);
        $weba = new \R\WebAuthn($_SERVER["HTTP_HOST"]);
        if (!$weba->authenticate($assertion, $user->credential)) {
            return false;
        }

        $_SESSION["app"]["user_id"] = $user->user_id;
        $_SESSION["app"]["user"] = $user;
        $_SESSION["app"]["login"] = true;
        $user->createUserLog("SUCCESS");
        $user->online();
        $this->user = $user;

        return true;
    }

    public function ui(string $uri): UI
    {

        $ui = UI::Query([
            "user_id" => $this->user_id,
            "uri" => $uri
        ])->first();

        if (!$ui) {
            $ui = new UI();
            $ui->user_id = $this->user_id;
            $ui->uri = $uri;
        }

        return $ui;
    }

    public function languages(): array
    {

        $languages = array_keys($this->config["system"]["language"]);
        $languages = $languages ?? [];

        if ($lang = $this->config["language"]["value"]) {
            $languages = array_merge($languages, $lang);
        }

        $languages = array_unique($languages);

        return $languages;
    }

    public function getUserLoginToken(User $user): String
    {
        //create login token
        $token = JWT::encode([
            "iat" => time(),
            "exp" => time() + 3600,
            "id" => $user->user_id,
            "type" => "access_token"
        ], $this->config["jwt"]["key"]);

        return $token;
    }

    public function executeQuery(string $query, bool $use_system_gql = false)
    {
        $server = new Server($this, $use_system_gql);
        return $server->executeQuery($query);
    }
}
