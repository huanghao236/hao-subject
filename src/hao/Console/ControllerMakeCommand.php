<?php
namespace Hao\Console;

class ControllerMakeCommand
{
    
    public function getStub()
    {
        return __DIR__.'/stubs/controller.stub';
    }

    //获取nanmespace名称
    public function getNamespace(string $file_name): string
    {
        $file_name = explode("\\",$file_name);
        array_pop($file_name);
        if ($file_name){
            return  'App\\Http\\Controllers\\'.implode('\\',$file_name);
        }
        return  'App\\Http\\Controllers';
    }

    //文件存储目录
    public function fileDirectory(string $appPath,string $route)
    {
        return $appPath.'Http\\Controllers\\'.$route;
    }

    //默认目录
    public function defaultDirectory(string $appPath)
    {
        return $appPath.'Http\\Controllers';
    }
}