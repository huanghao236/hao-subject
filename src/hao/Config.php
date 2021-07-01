<?php
namespace Hao;

class Config
{
    /**
     * 配置参数
     * @var array
     */
    private $config = [];

    /**
     * 加载配置文件（PHP格式）
     * @param  string $file  配置文件
     * @param string|null $name
     * @return array
     */
    public function load(string $file,string $name = null)
    {
        $config = [];
        if (is_file($file)) {
            $type = pathinfo($file, PATHINFO_EXTENSION);//数组的形式返回关于文件路径的信息
            if ('php' == $type) {
                $config = include $file;
            }
        }
        return $this->set($config,$name);
    }

    /**
     * 获取配置
     * @param null $name        配置参数名
     * @return array|string
     */
    public function get($name = null)
    {
        if (empty($name)){
            return $this->config;
        }

        if (isset($this->config[$name])){
            return $this->config[$name];
        }

        if (strpos($name,'.')){
            $config = $this->config;
            $name = explode('.',$name);
            foreach ($name as $v){
                if (isset($config[$v])){
                    $config = $config[$v];
                }
            }
            return $config;
        }

        return [];
    }

    /**
     * 设置config
     * @param array         $config
     * @param string|null   $name
     * @return array
     */
    public function set(array $config,string $name = null)
    {
        if ($name){
            if (isset($this->config[$name])){
                $result = array_merge($this->config[$name],$config);
            }else{
                $result = $config;
            }
            $this->config[$name] = $result;
        }else{
            $this->config = array_merge($this->config,$config);
        }

        return $this->config;
    }
}