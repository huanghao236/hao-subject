<?php


namespace Hao\facade;

use Hao\Facade;
/**
 * @see \Hao\Env
 * @package Hao\facade
 * @mixin \Hao\Env
 * @method static mixed get(string $name = null, mixed $default = null) 获取环境变量值
 * @method static void set(string|array $env, mixed $value = null) 设置环境变量值
 */

class Env extends Facade
{
    /**
     * 获取当前Facade对应类名（或者已经绑定的容器对象标识）
     * @access protected
     * @return string
     */
    protected static function getFacadeClass()
    {
        return 'env';
    }
}