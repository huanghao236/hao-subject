<?php

namespace Hao\route;

use Hao\App;
use Hao\pipeline\Pipeline;
use Hao\Request;
use Hao\Response;
use ReflectionMethod;

class Dispatch
{

    /**
     * 请求对象
     * @var Request
     */
    protected $request;

    /**
     * 调度信息
     * @var mixed
     */
    protected $dispatch;

    /**
     * 路由中间件集合
     * @var array
     */
    protected $routeMiddleware;

    /**
     * 路由使用的中间件集合
     * @var array
     */
    protected $middleware = [];

    protected $app;

    public function __construct(Request $request, App $app, $dispatch, $routeMiddleware, $middleware)
    {
        $this->request         = $request;
        $this->dispatch        = $dispatch;
        $this->app             = $app;
        $this->routeMiddleware = $routeMiddleware;
        $this->middleware      = $middleware;
    }


    public function exec()
    {
        if (strstr($this->dispatch, '@')) {
            $dispatch = explode('@', $this->dispatch);
            $class    = $dispatch[0];
            if (class_exists($class)) {
                // 实例化控制器
                $instance = $this->app->make($class);
                $middleware = [];
                //检测应用服务中是否存在中间件配置
                if ($this->middleware){
                    foreach ($this->middleware as $v) {
                        if (isset($this->routeMiddleware[$v])){
                            if (is_array($this->routeMiddleware[$v])){
                                foreach ($this->routeMiddleware[$v] as $routeMiddleware){
                                    $middleware[] = $routeMiddleware;
                                }
                            }else{
                                $middleware[] = $this->routeMiddleware[$v];
                            }
                        }
                    }
                }
                //检测控制器中是否存在中间件配置
                if ($instance->middleware) {
                    foreach ($instance->middleware as $key => $val) {
                        if (isset($this->routeMiddleware[$key])) {
                            if (isset($val['except']) && is_array($val['except'])) {
                                $except = array_filter($val['except']);
                                if (!empty($except) && !in_array($this->dispatch, $val['except'])) {
                                    $middleware[] = $this->routeMiddleware[$key];
                                }
                            }
                            if (isset($val['only']) && is_array($val['only']) && in_array($this->dispatch, $val['only'])) {
                                $middleware[] = $this->routeMiddleware[$key];
                            }
                        }
                    }
                }
                //如果有路由中间件，则需先走中间件
                if ($middleware){
                    return (new Pipeline($this->app))->send($this->request)->through($middleware)
                                                     ->then(function () use ($instance, $dispatch) {
                                                         $data = $this->app->invokeReflectMethod($instance, new ReflectionMethod($instance, $dispatch[1]));
                                                         return $this->autoResponse($data);
                                                     });
                }else{
                    $data = $this->app->invokeReflectMethod($instance, new ReflectionMethod($instance, $dispatch[1]));
                    return $this->autoResponse($data);
                }

            }
        } else {
            echo '404';
            exit();
        }
    }

    protected function autoResponse($data): Response
    {
        /*if ($data instanceof Response) {
            $response = $data;
        } elseif (!is_null($data)) {
            // 默认自动识别响应输出类型
            $type     = $this->request->isJson() ? 'json' : 'html';
            $response = new Response($data);
        } else {
            $data = ob_get_clean();
            $content  = false === $data ? '' : $data;
            $status   = '' === $content && $this->request->isJson() ? 204 : 200;
        }*/
        return new Response($data);
    }
}