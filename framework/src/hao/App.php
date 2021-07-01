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
 */
class App extends Container
{

    public $initialized = false;

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
        'kernel'   =>  \App\Http\Kernel::class,//中间件集合类
    ];

    public function __construct(){

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
        $files = glob(CONF_PATH . '*' . CONF_EXT);//获取指定文件夹下的指定后缀文件
        foreach ($files as $file){
            $this->config->load($file,pathinfo($file,PATHINFO_FILENAME));
        }
        //加载自定义路由,注册路由规则
        $routes = $this->config->get('config.route');
        if (is_array($routes)){
            foreach ($routes as $route) {
                $file = ROUTE_PATH.$route.CONF_EXT;
                if (file_exists($file)){
                    include $file;
                }
            }
        }else{
            $file = ROUTE_PATH.$routes.CONF_EXT;
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
        $envFile = PUBLIC_PATH.'../.env';
        if(is_file($envFile)){
            $this->env->load($envFile);
        }
    }
}