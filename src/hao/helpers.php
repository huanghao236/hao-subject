<?php
use Hao\Container;

if (! function_exists('app')) {

    /**
     * 获取可用的容器实例
     * @param null $abstract 类名或者标识
     * @param array $parameters 变量
     * @return Closure|Container|mixed|object
     * @throws Exception
     */
    function app($abstract = null, array $parameters = [])
    {
        if (is_null($abstract)) {
            return Container::getInstance();
        }

        return Container::getInstance()->make($abstract, $parameters);
    }
}


if (!function_exists('config')) {
    /**
     * 获取和设置配置参数
     * @param string|array $key  参数名
     * @return Closure|Container|mixed|object
     * @throws Exception
     */
    function config($key = '')
    {
        if (is_null($key)) {
            return app('config');
        }
        return app('config')->get($key);
    }
}


if (! function_exists('env')) {
    /**
     * 获取环境变量的值
     * @param  string  $key 参数名
     * @return mixed|object
     * @throws Exception
     */
    function env(string $key)
    {
        return app('env')->get($key);
    }
}