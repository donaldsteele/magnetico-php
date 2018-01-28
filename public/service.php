<?php
/**
 * Created by PhpStorm.
 * User: don
 * Date: 1/23/2018
 * Time: 3:56 PM
 */
ob_start();
session_start();

spl_autoload_register(function ($class) {
    $parts = explode('\\', $class);
    $classFile = __DIR__ . DIRECTORY_SEPARATOR . array_shift($parts) . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $parts) . ".php";
    if (FALSE === stream_resolve_include_path($classFile)) {
        return;
    }

    /** @noinspection PhpIncludeInspection */
    require $classFile;
});

$service = new \classes\service_handler(new \classes\db\sqlite('../database-bak.sqlite3'));
$service->route();

ob_flush();
