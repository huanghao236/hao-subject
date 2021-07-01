<?php

namespace Hao\facade;


use Hao\Facade;

/**
 * @see \Hao\Route
 * @package Hao\facade
 * @mixin \Hao\Route
 * @method static Route any(string $rule, mixed $route) 注册路由
 * @method static Route get(string $rule, mixed $route) 注册GET路由
 * @method static Route post(string $rule, mixed $route) 注册POST路由
 * @method static Route group(string|\Closure $name, mixed $route = null) 注册路由分组
 *
 */
class Route extends Facade
{


    /**
     * 获取当前Facade对应类名（或者已经绑定的容器对象标识）
     * @access protected
     * @return string
     */
    protected static function getFacadeClass()
    {
        return 'route';
    }
}