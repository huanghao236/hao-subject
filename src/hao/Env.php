<?php

declare (strict_types = 1);
namespace Hao;
use ArrayAccess;
use Exception;

/**
 * Env管理类
 * @package Hao
 */
class Env implements ArrayAccess
{
    /**
     * 环境变量数据
     * @var array
     */
    protected $data = [];

    public function __construct()
    {
        $this->data = $_ENV;
    }

    /**
     * 读取配置文件
     * @param string $file
     */
    public function load(string $file)
    {
        $env = parse_ini_file($file, true) ?: [];
        $this->set($env);
    }

    /**
     * 获取配置
     * @param string $key
     * @return mixed|string
     */
    public function get(string $key)
    {
        if (isset($this->data[$key])){
            return $this->data[$key];
        }
        return '';
    }

    /**
     * 设置环境变量值
     * @param string|array  $env 环境变量名
     * @param null $value        值
     */
    public function set($env,$value = null)
    {
        if (is_array($env)){
            foreach ($env as $key => $val){
                if (is_array($val)){
                    foreach ($val as $k => $v) {
                        $this->data[$key . '_' . strtoupper($k)] = $v;
                    }
                }else{
                    $this->data[$key] = $val;
                }
            }
        }else{
            $this->data[$env] = $value;
        }
    }



    //例:$obj = new Env();
    //设置一个偏移位置的值 $obj['data'] = 'data';
    public function offsetSet($name, $value): void
    {
        $this->set($name, $value);
    }

    //检查偏移位置是否存在 isset($obj['data'])
    public function offsetExists($name): bool
    {
        return !is_null($this->get($name));
    }

    //复位一个偏移位置的值 unset($obj['data']);var_dump($test['data']);
    public function offsetUnset($name)
    {
        throw new Exception('not support: unset');
    }

    //获取一个偏移位置的值 var_dump($obj['data'])
    public function offsetGet($name)
    {
        return $this->get($name);
    }
}