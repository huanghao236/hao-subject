<?php
namespace Hao;
use ArrayAccess;

class Request implements ArrayAccess
{
    /**
     * @var string $pathInfonfo
     */
    protected $pathInfo;


    /**
     * php://input内容
     * @var string
     */
    protected $input;

    /**
     * 当前请求类型
     * @var string
     */
    protected $method;

    /**
     * 当前请求所有参数
     * @var array
     */
    protected $server;

    /**
     * 获取GET参数
     * @var array
     */
    protected $get;

    /**
     * 获取POST参数
     * @var array
     */
    protected $post;

    /**
     * 获取POST和GET参数
     * @var array
     */
    protected $all;

    /**
     * 获取Cookie
     * @var array
     */
    protected $cookie;

    /**
     * 获取上传文件
     * @var array
     */
    protected $file;

    /**
     * ENV对象
     * @var Env
     */
    protected $env;

    /**
     * 资源类型定义
     * @var array
     */
    protected $mimeType = [
        'xml'   => 'application/xml,text/xml,application/x-xml',
        'json'  => 'application/json,text/x-json,application/jsonrequest,text/json',
        'js'    => 'text/javascript,application/javascript,application/x-javascript',
        'css'   => 'text/css',
        'rss'   => 'application/rss+xml',
        'yaml'  => 'application/x-yaml,text/yaml',
        'atom'  => 'application/atom+xml',
        'pdf'   => 'application/pdf',
        'text'  => 'text/plain',
        'image' => 'image/png,image/jpg,image/jpeg,image/pjpeg,image/gif,image/webp,image/*',
        'csv'   => 'text/csv',
        'html'  => 'text/html,application/xhtml+xml,*/*',
    ];

    public function __construct()
    {
        // 保存 php://input
        $this->input = file_get_contents('php://input');
    }

    public static function __make(App $app)
    {
        $request = new static();//实例化的是当前调用的类，当前该类被继承时当前调用sun类就实例化sun类
        $request->server  = $_SERVER;
        $request->get     = $_GET;
        $request->post    = $_POST;
        $request->all     = $_REQUEST;
        $request->cookie  = $_COOKIE;
        $request->file    = $_FILES ?? [];
        $request->env     = $app->env;
        return $request;

    }

    /**
     * 获取当前请求URL的pathInfo信息（含URL后缀）
     * @access public
     * @return string
     */
    public function pathInfo()
    {
        $type = 'REDIRECT_URL';
        $server = $this->server();
        if (is_null($this->pathInfo)) {
            if (!empty($server[$type])) {
                $server['PATH_INFO'] = (0 === strpos($server[$type], $server['SCRIPT_NAME'])) ?
                    substr($server[$type], strlen($server['SCRIPT_NAME'])) : $server[$type];
            }
            $this->pathInfo = empty($server['PATH_INFO']) ? '/' : ltrim($server['PATH_INFO'], '/');
        }
        return $this->pathInfo;
    }

    /**
     * 获取指定请求参数
     * @param string $name    参数名称
     * @return string|array
     */
    public function server(string $name = ''){

        if ($name){
            return $this->server[$name] ?? '';
        }
        return $this->server;
    }

    /**
     * 当前的请求类型
     * @access public
     * @return string
     */
    public function method(): string
    {
        return $this->server('REQUEST_METHOD') ?: 'GET';
    }


    /**
     * 获取GET参数
     * @param string $name 指定参数名
     * @return array|string
     */
    public function get($name = '')
    {
        if (empty($name)){
            return $this->get;
        }
        if (isset($this->get[$name])){
            return $this->get[$name];
        }else{
            return [];
        }
    }

    /**
     * 获取POST参数
     * @param string $name         指定参数名
     * @return array|mixed
     */
    public function post(string $name = ''){
        if (empty($name)){
            return $this->post;
        }
        if (isset($this->post[$name])){
            return $this->post[$name];
        }else{
            return [];
        }
    }

    /**
     * 获取POST和GET参数
     * @param string $name         指定参数名
     * @return array|mixed
     */
    public function all(string $name = '')
    {
        if (empty($name)){
            return $this->all;
        }
        if (isset($this->all[$name])){
            return $this->all[$name];
        }else{
            return [];
        }
    }

    /**
     * 获取环境变量
     * @access public
     * @param  string $name 数据名称
     * @return mixed
     */
    public function env(string $name)
    {
        return $this->env->get($name);
    }

    /**
     * 当前请求的资源类型
     * @access public
     * @return string
     */
    public function type(): string
    {
        $accept = $this->server('HTTP_ACCEPT');

        if (empty($accept)) {
            return '';
        }

        foreach ($this->mimeType as $key => $val) {
            $array = explode(',', $val);
            foreach ($array as $k => $v) {
                if (stristr($accept, $v)) {
                    return $key;
                }
            }
        }

        return '';
    }

    /**
     * 当前是否JSON请求
     * @access public
     * @return bool
     */
    public function isJson(): bool
    {
        $acceptType = $this->type();

        return false !== strpos($acceptType, 'json');
    }

    /**
     * 直接从对象中获取提交的参数
     * @param string $name
     * @return mixed
     */
    public function __get(string $name)
    {
        return $this->all($name);
    }

    //例:$obj = new Env();
    //设置一个偏移位置的值 $obj['data'] = 'data';
    public function offsetSet($name, $value): void
    {
        //$this->set($name, $value);
    }

    //检查偏移位置是否存在 isset($obj['data'])
    public function offsetExists($name): bool
    {
        return !is_null($this->get($name));
    }

    //复位一个偏移位置的值 unset($obj['data']);var_dump($test['data']);
    public function offsetUnset($name)
    {
        //throw new \Exception('not support: unset');
    }

    //获取一个偏移位置的值 var_dump($obj['data'])
    public function offsetGet($name)
    {
        return $this->__get($name);
    }
}