<?php
namespace Hao;
use Closure;
use Hao\route\Dispatch;

class Route
{

    // 当前分组信息
    private static $group = [];

    //路由信息
    private static $rule = [];

    /**
     * 注册路由分组
     * @param string $rule      路由规则
     * @param mixed  $route     路由地址
     * @return mixed
     */
    public static function group(string $rule, $route){
        $currentGroup = self::getGroup();
        if ($currentGroup) {
            $name = $currentGroup . ($rule ? '/' . ltrim($rule, '/') : '');
        }else{
            $name = $rule;
        }
        if ($route instanceof Closure){
            self::setGroup($name);
            $route();
            self::setGroup($currentGroup);
        }
    }


    /**
     * 注册GET路由
     * @param string $rule  路由规则
     * @param mixed  $route 路由地址
     * @return string
     */
    public static function get(string $rule, $route){
        self::setRule($rule,$route,'GET',self::getGroup());
    }

    /**
     * 注册POST路由
     * @param string $rule  路由规则
     * @param mixed  $route 路由地址
     */
    public static function post(string $rule, $route){
        self::setRule($rule,$route,'POST',self::getGroup());
    }

    /**
     * 注册GET和POST路由
     * @param string $rule  路由规则
     * @param mixed  $route 路由地址
     */
    public static function any(string $rule, $route){
        self::setRule($rule,$route,'ANY',self::getGroup());
    }


    public static function setGroup($name){
        /*if (isset(self::$group['name'])){
            self::$group['name'] = self::$group['name'].'/'.$name;
        }else{
            self::$group['name'] = $name;
        }*/
        self::$group['name']    = $name;
    }

    public static function getGroup(){
        if (isset(self::$group['name'])){
            return self::$group['name'];
        }
        return [];
    }

    /**
     * @param string $rule          路由规则
     * @param string|array $route   路由地址
     * @param string $type          路由类型
     * @param string|array $group   所属分组
     */
    public static function setRule(string $rule, $route, $type = '*',$group = ''){
        if (empty($group)){
            self::$rule[$type][$rule] = $route;
        }else{
            self::$rule[$type][$group.'/'.$rule] = $route;
        }
    }

    /**
     * 获取路由分组信息
     * @return array
     */
    public static function getRule(){
        return self::$rule;
    }

    /**
     * 校验路由规则
     * @param Request $request
     * @param App     $app
     * @param array   $routeMiddleware
     * @return bool|Response
     */
    public function dispatch(Request $request,App $app,array $routeMiddleware): Response
    {
        $method     = $request->method();//请求类型
        $pathInfo   = $request->pathInfo();//请求地址
        if (!isset(self::getRule()[$method])){
            return false;
        }
        if (!isset(self::getRule()[$method][$pathInfo])){
            return false;
        }
        $dispatch = new Dispatch($request,$app,self::getRule()[$method][$pathInfo],$routeMiddleware);
        return $dispatch->exec();
    }

}