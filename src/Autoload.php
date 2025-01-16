<?php

class Autoload
{

    public static function load($className)
    {
        $dir = __DIR__;

        $path = str_replace('\\', '/', $className);
        $filePath = $dir . '/' . $path . '.php';

        if (file_exists($filePath)) {
            include_once $filePath;
            return true;
        }

        throw new ErrorException('Class ' . $filePath . ' couldn\'t be found');
    }

}

spl_autoload_register(['Autoload', 'load']);