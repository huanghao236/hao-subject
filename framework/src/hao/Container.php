<?php
//严格模式
declare (strict_types = 1);

namespace Hao;

use ReflectionClass;
use ReflectionException;
use ReflectionFunctionAbstract;
use Exception;
use Closure;
/**
 * 容器管理类
 * Class Container
 * @package Hao
 */
class Container
{

    /**
     * 容器对象实例
     * @var Container|Closure
     */
    protected static $instance;

    /**
     * 容器中的对象实例
     * @var array
     */
    protected $instances = [];

    /**
     * 容器绑定标识
     * 被子类中相同 $bill 变量覆盖了
     * @var array
     */
    protected $bind = [];

    /**
     * 获取容器中的对象实例
     * @param string $abstract 类名或者标识
     * @return mixed
     * @throws Exception|object
     */
    public function get(string $abstract)
    {
        //判断容器中类或标识是否存在
        if ($this->bound($abstract)) {
            $abstract = $this->getAlias($abstract);
            return $this->make($abstract);
        }
        throw new Exception('class not exists: ' . $abstract, $abstract);
    }

    /**
     * 根据别名获取真实类名
     * 若bind容器中存在多维，则递归获取,保证取出的数据是srting类型
     * @param  string $abstract
     * @return string
     */
    public function getAlias(string $abstract): string
    {
        if (isset($this->bind[$abstract])) {
            $bind = $this->bind[$abstract];
            if (is_string($bind)) {
                return $this->getAlias($bind);
            }
        }
        return $abstract;
    }


    /**
     * 判断容器中是否存在类及标识
     * @param $abstract
     * @return bool
     */
    public function bound($abstract)
    {
        return isset($this->bind[$abstract]) || isset($this->instances[$abstract]);
    }

    /**
     * 创建类的实例 已经存在则直接获取
     * @param string $abstract  类名或者标识
     * @param array $vars       变量
     * @param bool $newInstance 是否每次创建新的实例
     * @return mixed|object
     * @throws Exception
     */
    public function make(string $abstract, array $vars = [], bool $newInstance = false)
    {
        $abstract = $this->getAlias($abstract);
        if (isset($this->instances[$abstract]) && !$newInstance) {
            return $this->instances[$abstract];
        }
        $object = $this->invokeClass($abstract,$vars);
        if (!$newInstance) {
            $this->instances[$abstract] = $object;
        }
        return $object;
    }

    /**
     * 调用反射执行类的实例化
     * @param string $class 类名
     * @param array $var    参数
     * @return object
     * @throws Exception
     */
    public function invokeClass(string $class,array $var = []){
        try {
            //通过传入$class类名建立反射类
            //ReflectionClass 可以获取$class类中的：
            //常量、属性、方法、命名空间、$class中是否存在某法方法等
            $reflect = new ReflectionClass($class);
        } catch (ReflectionException $e) {
            throw new Exception('class not exists: ' . $class, $class, $e);
        }

        //检测该类中是否用__make方法
        if ($reflect->hasMethod('__make')) {
            //getMethod获取__make方法的有关信息
            $method = $reflect->getMethod('__make');
            if ($method->isPublic() && $method->isStatic()) {
                $args   = $this->bindParams($method, $var);
                //invokeArgs 将数组$args作为参数传送给__make方法，并执行他,当invokeArgs 第一个参数object为空时，该方法必须为静态方法
                return $method->invokeArgs(null, $args);
            }
        }
        //获取构造函数,若类中没有构造函数返回null
        $constructor = $reflect->getConstructor();
        //若有构造函数则将该构造函数当成参数创建一个新的类实例
        $args = $constructor ?  $this->bindParams($constructor,$var): [];
        //根据新给的参数创建一个新的类实例,相当于实例化$class类
        return $reflect->newInstanceArgs($args);
    }

    /**
     * 绑定参数
     * @param ReflectionFunctionAbstract $reflect 反射类
     * @param array $vars                         参数
     * @return array
     * @throws Exception
     */
    protected function bindParams(ReflectionFunctionAbstract $reflect, array $vars = []): array
    {
        //获取参数数量
        if ($reflect->getNumberOfParameters() == 0) {
            return [];
        }
        //获取反射类方法中的所有变量名 例：test(Test $test) 返回
        //array(1) {
        //  [0]=>
        //  object(ReflectionParameter)#5 (1) {
        //    ["name"]=>
        //    string(7) "test"
        //  }
        //}

        $params = $reflect->getParameters();
        $args   = [];
        foreach ($params as $param) {
            $name           = $param->getName();//获取函数名 例：获取上面对象中的name 'test'
            $reflectionType = $param->getType();//返回参数类型 例：获取上方test方法中的'Test'
            //$reflectionType->isBuiltin() 判断方法中定义的参数类型是否是PHP支持的
            //例：方法test(app $app)参数存在,但所定义的参数类型不是PHP
            //$reflectionType->getName() 获取类型名称 'app'
            if ($reflectionType && $reflectionType->isBuiltin() === false) {
                $args[] = $this->getObjectParam($reflectionType->getName(), $vars);
            }else{
                throw new Exception('method param miss:' . $name);
            }

        }
        return $args;
    }


    /**
     * 获取对象类型的参数值
     * @access protected
     * @param string $className 类名
     * @param array  $vars      参数
     * @return mixed|object
     * @throws Exception
     */
    protected function getObjectParam(string $className, array &$vars)
    {
        $array = $vars;
        $value = array_shift($array);//删除数组中的第一个元素，并返回被删除元素的值
        //1.判断$value对象是否是$className类的实例化,2.判断一个对象是否实现了某个接口
        if ($value instanceof $className) {
            $result = $value;
            array_shift($vars);
        } else {
            $result = $this->make($className);
        }

        return $result;
    }

    /**
     * 绑定一个类、闭包、实例、接口实现到容器
     * @access public
     * @param string|array $abstract 类标识、接口
     * @param mixed        $concrete 要绑定的类、闭包或者实例
     * @return $this
     */
    public function bind($abstract, $concrete = null)
    {
        if (is_array($abstract)) {
            foreach ($abstract as $key => $val) {
                $this->bind($key, $val);
            }
        } elseif ($concrete instanceof Closure) {
            //如果绑定的是匿名函数类 function(){return new Bind();}
            $this->bind[$abstract] = $concrete;
        } elseif (is_object($concrete)) {
            $this->instance($abstract, $concrete);
        } else {
            $abstract = $this->getAlias($abstract);
            if ($abstract != $concrete) {
                $this->bind[$abstract] = $concrete;
            }
        }
        return $this;
    }

    /**
     * 绑定一个类实例到容器
     * @access public
     * @param string $abstract 类名或者标识
     * @param object $instance 类的实例
     * @return $this
     */
    public function instance(string $abstract, $instance)
    {
        $abstract = $this->getAlias($abstract);

        $this->instances[$abstract] = $instance;

        return $this;
    }


    /**
     * 设置当前容器的实例
     * @access public
     * @param object|Closure $instance
     * @return void
     */
    public static function setInstance($instance): void
    {
        static::$instance = $instance;
    }

    /**
     * 获取当前容器的实例（单例）
     * @access public
     * @return static
     */
    public static function getInstance()
    {
        return static::$instance;
    }

    /**
     * 执行闭包函数
     * @param Closure $function
     * @return mixed
     */
    public function invokeFunction(Closure $function){

        return $function();
    }

    /**
     * 调用反射执行类的方法 支持参数绑定
     * @access public
     * @param object $instance 对象实例
     * @param mixed  $reflect  反射类
     * @return mixed
     * @throws Exception
     */
    public function invokeReflectMethod($instance, $reflect)
    {
        $args = $this->bindParams($reflect);
        return $reflect->invokeArgs($instance, $args);
    }

    /**
     * 给一个未定义的属性赋值时调用
     * 例：当 $container->test = function () {return new Bind();} 这个在container类中test并没有定义，__set将会把
     * $name = 'test'、$value = 'function () {return new Bind();}'赋值到自己想要赋值的变量中,若已定义则修改该变量的赋值。
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        $this->bind($name, $value);
    }

    /**
     * 当调用一个未定义的属性时访问此方法
     * @param $name
     * @return object
     * @throws Exception
     */
    public function __get($name)
    {
        return $this->get($name);
    }
}