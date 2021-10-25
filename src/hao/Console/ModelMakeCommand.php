<?php
namespace Hao\Console;

class ModelMakeCommand
{

    public function getStub()
    {
        return __DIR__.'/stubs/model.stub';
    }

    //获取nanmespace名称
    public function getNamespace(string $file_name): string
    {
        $file_name = explode("\\",$file_name);
        array_pop($file_name);
        if ($file_name){
            return  'App\\'.implode('\\',$file_name);
        }
        return  'App\\Models';
    }

    //文件存储目录
    public function fileDirectory(string $appPath,string $route)
    {
        return $appPath.$route;
    }

    //默认目录
    public function defaultDirectory(string $appPath)
    {
        return $appPath.'Models';
    }
}