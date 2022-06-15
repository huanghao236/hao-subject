<?php

namespace Hao;

use Closure;
use Hao\route\Dispatch;

class Route
{

    // 路由配置参数集合
    private $attributes = [];

    // 当前分组信息
    private $group = [];

    //路由信息
    private $rule = [];


    private $allowedAttributes = [
        'middleware', 'namespace', 'prefix'
    ];

    /**
     * 注册路由分组
     * @param string $rule 路由规则
     * @param mixed $route 路由地址
     * @return mixed
     */
    public function group(string $rule, $route)
    {
        $currentGroup = $this->getGroup();
        if ($currentGroup) {
            $name = $currentGroup . ($rule ? '/' . ltrim($rule, '/') : '');
        } else {
            $name = $rule;
        }
        if ($route instanceof Closure) {
            $this->setGroup($name);
            $route();
            $this->setGroup($currentGroup);
        }
    }


    /**
     * 注册GET路由
     * @param string $rule 路由规则
     * @param mixed $route 路由地址
     * @return string
     */
    public function get(string $rule, $route)
    {
        $this->setRule($rule, $route, 'GET', $this->getGroup());
    }

    /**
     * 注册POST路由
     * @param string $rule 路由规则
     * @param mixed $route 路由地址
     */
    public function post(string $rule, $route)
    {
        $this->setRule($rule, $route, 'POST', $this->getGroup());
    }

    /**
     * 注册GET和POST路由
     * @param string $rule 路由规则
     * @param mixed $route 路由地址
     */
    public function any(string $rule, $route)
    {
        $this->setRule($rule, $route, 'ANY', $this->getGroup());
    }


    public function setGroup($name)
    {
        $this->group['name'] = $name;
    }

    public function getGroup()
    {
        if (isset($this->group['name'])) {
            return $this->group['name'];
        }
        return [];
    }

    /**
     * @param string $rule 路由规则
     * @param string|array $route 路由地址
     * @param string $type 路由类型
     * @param string|array $group 所属分组
     */
    public function setRule(string $rule, $route, $type = '*', $group = '')
    {
        $rule  = $group ? $group . '/' . $rule : $rule;
        $route = $this->attributes['namespace'] ? $this->attributes['namespace'] . '\\' . $route : $route;
        if (isset($this->attributes['prefix'])) {
            $this->rule[$this->attributes['prefix']][$type][$rule] = $route;
            $this->rule[$this->attributes['prefix']]['middleware'] = $this->attributes['middleware'];
        } else {
            $this->rule[$type][$rule] = $route;
        }
    }

    public function attributes($method, $parameter)
    {
        $this->attributes[$method] = $parameter;
        return $this;
    }

    /**
     * 当在类中没有找到方法，则进入这里
     * @param $method
     * @param $parameters
     */
    public function __call($method, $parameters)
    {
        if ($method == 'routeQuote') {
            $file = app()->rootPath . '/' . $parameters[0];
            if (file_exists($file)) {
                if (!isset($this->attributes['prefix'])) {
                    if (isset($this->rule['middleware'])) {
                        $this->rule['middleware'] = array_merge($this->rule['middleware'], $this->attributes['middleware']);
                    } else {
                        $this->rule['middleware'] = $this->attributes['middleware'];
                    }
                }
                require $file;
            }
            $this->attributes = [];
        }
        if (in_array($method, $this->allowedAttributes)) {
            if ($method == 'middleware') {
                return $this->attributes($method, is_array($parameters[0]) ? $parameters[0] : $parameters);
            }
            return $this->attributes($method, $parameters[0]);
        }
    }


    /**
     * 校验路由规则
     * @param Request $request
     * @param App $app
     * @param array $routeMiddleware
     * @return bool|Response
     */
    public function dispatch(Request $request, App $app, array $routeMiddleware): Response
    {
        $method   = $request->method();  //请求类型
        $pathInfo = $request->pathInfo();//请求地址
        $prefix   = explode('/', $pathInfo);

        $rule = $this->rule[$prefix[0]] ?? $this->rule;
        if (!isset($rule[$method])) {
            dd('请求方式不存在');
        }

        if (isset($this->rule[$prefix[0]])) {
            unset($prefix[0]);
            $pathInfo = isset($prefix[1]) ? implode('/',$prefix) : '/';
        }

        if (!isset($rule[$method][$pathInfo])) {
            dd('请求地址不存在');
        }

        $dispatch = new Dispatch($request, $app, $rule[$method][$pathInfo], $routeMiddleware, $rule['middleware']);
        return $dispatch->exec();
    }


}