<?php

namespace Hao;

/**
 * Web应用管理类
 * Class Http
 * @package Hao
 */
class Http
{
    /**
     *
     * @var App
     */
    protected $app;

    /**
     * 路由路径
     * @var string
     */
    protected $routePath;

    public function __construct(App $app){
        $this->app = $app;
        $this->routePath = 'route' . DIRECTORY_SEPARATOR;
    }

    /**
     * 程序开始执行
     */
    public function run(): Response
    {
        //检测是否已初始化
        if (!$this->app->initialized()){
            $this->app->initialize();
        }
        //创建request请求对象
        $request = $this->app->make('request');
        //绑定到类容器当中
        $this->app->instance('request', $request);
        $kernel = $this->app->make('kernel');
        return $kernel->handle($request);

    }
}