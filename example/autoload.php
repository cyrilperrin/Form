<?php

/**
 * PSR-0 autoload function
 * @param $className string class name
 */
function autoload($className)
{
    $className = ltrim($className, '\\');
    $filePath = '';
    if ($lastNsPos = strrpos($className, '\\')) {
        $namespace = substr($className, 0, $lastNsPos);
        $className = substr($className, $lastNsPos + 1);
        $filePath = str_replace('\\', DIRECTORY_SEPARATOR, $namespace).
        DIRECTORY_SEPARATOR;
    }
    $filePath .= str_replace('_', DIRECTORY_SEPARATOR, $className).'.php';
    if (stream_resolve_include_path($filePath) !== false) {
        include($filePath);
    }
}

// Register autoload function
spl_autoload_register('autoload');