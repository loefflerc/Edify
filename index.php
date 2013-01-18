<?php
function __autoload($className)
{
    $srcPath = dirname(__FILE__) . '/src';
    $classPath = str_replace('\\', DIRECTORY_SEPARATOR, $className);
    $fullPath = "{$srcPath}/{$classPath}.php";

    if (realpath($fullPath)) {
        require_once $fullPath;
    }
}

Edify\Database\Database::connect();