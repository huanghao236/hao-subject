<?php
namespace Hao;

/**
 * 基础类
 * Class App
 * @package Hao
 * @property Route      $route
 * @property Config     $config
 * @property Request    $request
 * @property Http       $http
 * @property Env        $env
 * @property Console    $console
 */
class App extends Container
{

    public $initialized = false;

    /**
     * 框架目录
     * @var string
     */
    protected $haoPath = '';

    /**
     * 应用根目录
     * @var string
     */
    protected $rootPath = '';

    /**
     * 应用目录
     * @var string
     */
    public $appPath = '';


    /**
     * 配置后缀
     * @var string
     */
    protected $configExt = '.php';

    /**
     * 容器绑定标识
     * @var array
     */
    protected $bind = [
        'app'       =>  App::class,//基础类
        'http'      =>  Http::class,//Web应用管理类
        'config'    =>  Config::class,//配置类
        'route'     =>  Route::class,//路由类
        'request'   =>  Request::class,//请求管理类
        'env'       =>  Env::class,//Env管理类
        'kernel'    =>  \App\Http\Kernel::class,//中间件集合类
        'console'   =>  Console::class,//命令执行类
    ];

    public function __construct()
    {
        $this->haoPath = dirname(__DIR__) . DIRECTORY_SEPARATOR;
        $this->rootPath = $this->getDefaultRootPath();
        $this->appPath     = $this->rootPath . 'app' . DIRECTORY_SEPARATOR;
        static::setInstance($this);
        $this->instance('app', $this);
    }
    /**
     * 是否初始化过
     * @return bool
     */
    public function initialized()
    {
        return $this->initialized;
    }


    /**
     * 初始化
     * 加载配置项
     */
    public function initialize(){
        $this->initialized = true;
        $this->load();
    }


    /**
     * 加载所有配置文件
     */
    public function load()
    {
        //加载环境变量定义
        $this->loadEnv();
        //加载config文件夹下的所有配置
        $files = glob($this->rootPath.'config/' . '*' . $this->configExt);//获取指定文件夹下的指定后缀文件
        foreach ($files as $file){
            $this->config->load($file,pathinfo($file,PATHINFO_FILENAME));
        }
        //加载自定义路由,注册路由规则
        $routes = $this->config->get('config.route');
        if (is_array($routes)){
            foreach ($routes as $route) {
                $file = $this->rootPath.'routes/'.$route.$this->configExt;
                if (file_exists($file)){
                    include $file;
                }
            }
        }else{
            $file = $this->rootPath.'routes/'.$routes.$this->configExt;
            if (file_exists($file)){
                include $file;
            }
        }
        /*//加载中间件集合
        $kernels = $this->make('kernels');
        //绑定到类容器当中
        $this->instance('kernels', $kernels);*/
    }


    /**
     * 加载环境变量定义
     */
    public function loadEnv()
    {
        $envFile = $this->rootPath.'.env';
        if(is_file($envFile)){
            $this->env->load($envFile);
        }
    }

    /**
     * 获取应用根目录
     * @access protected
     * @return string
     */
    protected function getDefaultRootPath(): string
    {
        return dirname($this->haoPath, 4) . DIRECTORY_SEPARATOR;
    }
}