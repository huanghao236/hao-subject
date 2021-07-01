<?php


namespace Hao;

/**
 * Facade(门面)管理类
 * Class Facade
 * @package Hao
 */
class Facade
{

    /**
     * 创建Facade实例
     * @return mixed|object
     * @throws Object
     */
    protected static function createFacade()
    {
        $class = static::getFacadeClass();
        return Container::getInstance()->make($class);
    }


    /**
     * 调用实际类的方法,声明此方法用来处理调用对象中不存在的方法
     * @param string $method 对象中的方法
     * @param array  $params 传递的参数
     * @return mixed
     */
    public static function __callStatic(string $method, array $params)
    {
        //call_user_func_array 获取实例化的对象中指定的方法，传入参数，获取返回数据
        return call_user_func_array([
            static::createFacade(), //实例化的对象
            $method//对象中的方法
        ],
            $params//传递的参数
        );
    }
}