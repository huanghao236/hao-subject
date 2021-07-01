<?php
namespace Hao\http;

use Hao\App;
use Hao\pipeline\Pipeline;
use Hao\Request;
use Hao\Response;
use Hao\Route;
use Hao\route\Dispatch;

class Kernel
{
    /**
     * 全局中间件
     * @var array
     */
    protected $middleware = [

    ];


    /**
     * 路由中间件
     * @var array
     */
    protected $routeMiddleware = [

    ];

    /**
     * @var App
     */
    protected $app;

    public function __construct(App $app)
    {
        $this->app = $app;
    }

    /**
     * 处理传入的HTTP请求
     * @param Request $request
     * @return Response
     */
    public function handle(Request $request):Response
    {
        return (new Pipeline($this->app))->send($request)->through($this->middleware)->then(function ($request){
            return $this->app->route->dispatch($request,$this->app,$this->routeMiddleware);
        });
    }
}