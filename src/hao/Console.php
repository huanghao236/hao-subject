<?php


namespace Hao;


use Hao\Console\ControllerMakeCommand;
use Hao\Console\ModelMakeCommand;

class Console
{
    /**
     * 指令集合
     * @var string[]
     */
    protected $commands = [
        'make:model'      => ModelMakeCommand::class,
        'make:controller' => ControllerMakeCommand::class,
    ];

    public function __construct(App $app)
    {
        $this->app = $app;
        //检测是否已初始化
        if (!$this->app->initialized()){
            $this->app->initialize();
        }
    }

    /**
     * 执行当前的指令
     */
    public function run()
    {
        $argv = $_SERVER['argv'];
        // 去除命令名
        array_shift($argv);
        try {
            $command = explode(':',$argv[0]);
            $this->{$command[0]}($argv);
        }catch (\Exception $e){
            throw new Exception('class not exists: ' . $abstract, $abstract);
        }
    }

    /**
     * 创建文件方法
     * @param array $argv  命令内容
     */
    private function make(array $argv)
    {
        //实例化对应命令的类
        $command = $this->app->invokeClass($this->commands[$argv[0]]);

        //若创建命令为多级目录时
        if (count(explode("\\",$argv[1])) > 1){
            //去除文件名称，得到目录路径
            $name = end(explode("\\",$argv[1]));
            //文件存储目录
            $path = $command->fileDirectory($this->app->appPath,str_replace('\\'.$name,'',$argv[1]));
        }else{
            //若创建命令为一级目录时
            $name = $argv[1];
            $path = $command->defaultDirectory($this->app->appPath);
        }

        if (!is_dir($path)){
            // r读 w写 x执行
            // r=4 w=2 x=1
            // rwx=7 表示所有权限
            //第一个7文件所有人，第二个7文件所属组，第三个7其他人
            mkdir($path,0777,true);
        }
        if (!file_exists($path.'\\'.$name.'.php')){
            $file = str_replace(
                ['{{ namespace }}', '{{ class }}'],
                [$command->getNamespace($argv[1]), $name],
                file_get_contents($command->getStub())//读取文件内容
            );
            file_put_contents($path.'\\'.$name.'.php',$file,LOCK_EX);
            dd('文件创建成功！！');
        }else{
            dd('该文件已存在，请勿重复创建！！');
        }
    }

    /**
     * 执行任务方法
     */
    public function query()
    {

    }

}