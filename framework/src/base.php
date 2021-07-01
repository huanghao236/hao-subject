<?php

define('EXT', '.php');
defined('PUBLIC_PATH') or define('PUBLIC_PATH', dirname($_SERVER['SCRIPT_FILENAME']) . DIRECTORY_SEPARATOR);//项目目录
defined('PROJECT_PATH') or define('PROJECT_PATH',dirname(PUBLIC_PATH,1).DIRECTORY_SEPARATOR);//根目录
defined('APP_PATH') or define('APP_PATH', PROJECT_PATH . 'app'.DIRECTORY_SEPARATOR);//执行目录
defined('CONF_PATH') or define('CONF_PATH', PROJECT_PATH . 'config'.DIRECTORY_SEPARATOR); // 配置文件目录
defined('ROUTE_PATH') or define('ROUTE_PATH', PROJECT_PATH . 'routes'.DIRECTORY_SEPARATOR); // 路由设置文件目录
defined('CONF_EXT') or define('CONF_EXT', EXT); // 配置文件后缀