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

    public function __construct(Request $request,App $app,$dispatch,$routeMiddleware)
    {
        $this->request          = $request;
        $this->dispatch         = $dispatch;
        $this->app              = $app;
        $this->routeMiddleware  = $routeMiddleware;
    }


    public function exec(){
        $home = $this->request->env('HOME');
        if (strstr($this->dispatch,'@')){
            $dispatch = explode('@',$this->dispatch);
            $class = $home.$dispatch[0];
            if (class_exists($class)){
                // 实例化控制器
                $instance = $this->app->make($class);
                if ($instance->middleware){
                    foreach ($instance->middleware as $key => $val){
                        if (isset($this->routeMiddleware[$key])){
                            if (isset($val['except']) && is_array($val['except'])){
                                $except = array_filter($val['except']);
                                if(!empty($except) && !in_array($this->dispatch,$val['except'])){
                                    $this->middleware[] = $this->routeMiddleware[$key];
                                }
                            }
                            if (isset($val['only']) && is_array($val['only']) && in_array($this->dispatch,$val['only'])){
                                $this->middleware[] = $this->routeMiddleware[$key];
                            }
                        }
                    }
                    return (new Pipeline($this->app))->send($this->request)->through($this->middleware)->then(function ()use ($instance,$dispatch){
                        $data = $this->app->invokeReflectMethod($instance, new ReflectionMethod($instance, $dispatch[1]));
                        return $this->autoResponse($data);
                    });
                }else{
                    $data = $this->app->invokeReflectMethod($instance, new ReflectionMethod($instance, $dispatch[1]));
                    return $this->autoResponse($data);
                }
            }
        }else{
            echo '404';exit();
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