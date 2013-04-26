<?php
include_once "DB.php";
include_once "Model.php";
class NanoFramework
{
    const optionalParam = "/\\((.*?)\\)/";
    const namedParam = "/(\\(\\?)?:\\w+/";
    const spaltParam = "/\\*\\w+/";
    const escapeRegExp = "/[\\-{}\\[\\]+?.,\\\\\\^$|#\\s]/";
    public $postbody = null;
    private $getroutes = array();
    private $postroutes = array();
    private $putroutes = array();
    private $allroutes = array();
    private $deleteroutes = array();
    private $cache = array();
    private $out = false;
    public $errorhandler=null;

    function __construct()
    {
        $this->cache = @unserialize(@file_get_contents("App/tmp/routes.json"));
        if (!isset($this->cache) || !is_array($this->cache)) {
            $this->cache = array();
            $this->out = true;
        }
        $self = $this;
        set_exception_handler(
            function ($obj) use ($self) {
                $self->status(500);
                if($self->errorhandler!=null){
                    call_user_func_array($self->errorhandler, array(array('error' => $obj->getmessage(), 'trace' => $obj->gettrace())));
                    return;
                }
                $self->json(array('error' => $obj->getmessage(), 'trace' => $obj->gettrace()));
            }
        );
        register_shutdown_function(
            function () use ($self) {
                $error = error_get_last();
                if ($error['type'] == 1) {
                if($self->errorhandler!=null){


                    call_user_func_array($self->errorhandler, array(array('error' =>$error["message"])));
                    return;
                }
                $self->json(array('error' => $error["message"]));
                }
            }
        );

        set_error_handler(
            function ($code, $text) use ($self) {
                if (error_reporting())
                    throw new ErrorException($text, $code);
            }
        );
        //Backbone.emulateHTTP and Backbone.emulateJSON
        $emulated = false;
        if (isset($headers['X-HTTP-Method-Override']))
            $_SERVER['REQUEST_METHOD'] = $headers['X-HTTP-Method-Override'];
        if (isset($_POST['_method'])) {
            $_SERVER['REQUEST_METHOD'] = $_POST['_method'];
            $emulated = true;

        }
        if (isset($_POST['model'])) {
            $emulated = true;
        }
        //Read post content
        $this->postbody = $emulated ? (isset($_POST['model']) ? $_POST['model'] : "") : file_get_contents('php://input');
    }

    public function get($urlPattern, $fn)
    {
        $urlRegexp = $this->convertToRegexp($urlPattern);

        $this->getroutes[$urlRegexp] = $fn;
    }

    private function convertToRegexp($urlPattern)
    {
        if (isset($this->cache[$urlPattern])) {
            return $this->cache[$urlPattern];
        }
        $this->out = true;
        $urlRegexp = $urlPattern;
        $urlRegexp = preg_replace(NanoFramework::escapeRegExp, '\\\\$0', $urlRegexp);
        $urlRegexp = preg_replace(NanoFramework::optionalParam, '(?:$1)?', $urlRegexp);
        $urlRegexp = preg_replace_callback(NanoFramework::namedParam, function ($matches) {
            return isset($matches[1]) ? $matches[0] : "(?P<" . substr($matches[0], 1) . ">[^/]+)";
        }, $urlRegexp);
        $urlRegexp = preg_replace(NanoFramework::spaltParam, '(.*?)', $urlRegexp);
        $urlRegexp = "/^" . str_replace("/", "\\/", $urlRegexp) . "[\\/]?$/";
        $this->cache[$urlPattern] = $urlRegexp;
        return $urlRegexp;
    }

    public function post($urlPattern, $fn)
    {
        $urlRegexp = $this->convertToRegexp($urlPattern);
        $this->postroutes[$urlRegexp] = $fn;
    }

    public function delete($urlPattern, $fn)
    {
        $urlRegexp = $this->convertToRegexp($urlPattern);
        $this->deleteroutes[$urlRegexp] = $fn;
    }

    public function put($urlPattern, $fn)
    {
        $urlRegexp = $this->convertToRegexp($urlPattern);
        $this->putroutes[$urlRegexp] = $fn;
    }

    public function all($urlPattern, $fn)
    {
        $urlRegexp = $this->convertToRegexp($urlPattern);
        $this->allroutes[$urlRegexp] = $fn;
    }

    public function run($url = null)
    {
        if ($this->out) {
            @file_put_contents("App/tmp/routes.json", serialize($this->cache));
        }
        if ($url == null) {
            /*
             * if (isset($_GET['url'])) {
                $url = $_GET['url'];
            } else
             */
             if (substr($_SERVER['PHP_SELF'], 0, strlen($_SERVER['SCRIPT_NAME'])) === $_SERVER['SCRIPT_NAME']) {
                $url = substr($_SERVER['PHP_SELF'], strlen($_SERVER['SCRIPT_NAME']));
            }
            if ($url == "") {
                $url = "/";
            }
        }
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {

            $this->route($this->getroutes, $url);
        } else if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $this->route($this->postroutes, $url);
        } else if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {

            $this->route($this->deleteroutes, $url);
        } else if ($_SERVER['REQUEST_METHOD'] === 'PUT') {

            $this->route($this->putroutes, $url);
        }
        $this->route($this->allroutes, $url);

        $this->status(404);
        throw new Exception("No matching route for " . $_SERVER['REQUEST_METHOD'] . " " . $url);

    }

    public function route(&$list, $url)
    {
        foreach ($list as $exp => $fn) {
            $matches = array();
            if (preg_match($exp, $url, $matches)) {
                $data = call_user_func_array($fn, array($this, array_map('urldecode', $matches)));
                if (is_array($data)||is_object($data)) {
                    $this->json($data);
                }
                exit(0);
            }
        }
        return false;
    }

    public function json($data)
    {
        header('Content-type: text/json; charset=utf-8');
        header('Content-type: application/json; charset=utf-8');
        echo json_encode($data);
        exit(0);
    }

    public function status($code)
    {
        if ($code == 404)
            header($_SERVER["SERVER_PROTOCOL"] . " 404 Not Found");
        if ($code == 500)
            header($_SERVER["SERVER_PROTOCOL"] . " 500 Internal Server Error");
    }

    public function error_handler($string)
    {
        $this->errorhandler=$string;
    }
    public function redirect($uri)
    {

        $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != "off") ? "https" : "http";
        $base= $protocol . "://" . $_SERVER['HTTP_HOST'] . str_replace("index.php", "", $_SERVER['SCRIPT_NAME']);
        header('Location: '.(preg_match('/^https?:\/\//',$uri)?
            $uri:($base."index.php".$uri)));
 
        exit(0);
    }
}
