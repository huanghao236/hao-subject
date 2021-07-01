<?php
namespace Hao\pipeline;

use Hao\App;
use Throwable;
use Closure;
use Exception;

class Pipeline
{

    /**
     * 正在通过管道(中间件)的对象
     * @var
     */
    protected $passable;

    /**
     * 管道(中间件)数组
     * @var array
     */
    protected $pipes = [];

    /**
     * 调用每个管道(中间件)的方法
     * @var string
     */
    protected $method = 'handle';

    /**
     * @var App
     */
    protected $app;

    public function __construct(App $app)
    {
        $this->app = $app;
    }

    /**
     * 初始数据
     * @param $passable
     * @return $this
     */
    public function send($passable)
    {
        $this->passable = $passable;
        return $this;
    }


    /**
     * 设置管道(中间件)阵列
     * @param $pipes
     * @return $this
     */
    public function through($pipes)
    {
        //func_get_args()获取一个函数的所有参数
        //例:through(1,2,3) func_get_args()直接返回1,2,3
        $this->pipes = is_array($pipes) ? $pipes : func_get_args();
        return $this;
    }


    public function then(Closure $destination){
        /*array_reduce 将函数callable迭代地作用到$array数组的每一个单元中
        array_reduce ( array $array , callable $callback [, mixed $initial = NULL ] )
        例：
        $arr = ['A', 'B', 'C'];
        $res = array_reduce($arr, function($carry, $item){
            return $carry . $item;
        }, 'INITIAL-');
        第一次迭代 $carry = INITIAL- ,$item = A return 结果为 INITIAL-A
        第二次迭代 $carry = INITIAL-A ,$item = B return 结果为 INITIAL-AB
        第三次迭代 $carry = INITIAL-AB ,$item = C return 结果为 INITIAL-ABC*/
        $pipeline = array_reduce(array_reverse($this->pipes),
            $this->carry(),
            $this->prepareDestination($destination));

        return $pipeline($this->passable);
    }


    protected function carry()
    {
        return function ($stack, $pipe){
            return function ($request)use ($stack, $pipe){
                $pipe = $this->app->make($pipe);
                return $pipe->{$this->method}($request,$stack);
            };
        };
    }

    /**
     * 当中间件中return $next($request) 将执行此方法
     * @param Closure $destination 中间件中的$next
     * @return Closure
     */
    protected function prepareDestination(Closure $destination)
    {
        return function ($passable) use ($destination) {
            try {
                return $destination($passable);
            } catch (Throwable $e) {
                return $this->handleException($passable, $e);
            }
        };
    }

    /**
     * @param $passable
     * @param Throwable $e
     * @throws Throwable
     */
    protected function handleException($passable, Throwable $e)
    {
        throw $e;
    }
}